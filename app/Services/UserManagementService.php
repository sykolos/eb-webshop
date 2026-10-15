<?php

namespace App\Services;

use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final class UserManagementService
{
    public const STATUSES = ['active', 'pending', 'rejected', 'blocked'];

    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['user_invoice', 'user_shipping'])
            ->withCount('orders')
            ->withMax('orders', 'created_at');

        if (($filters['include_archived'] ?? false) || ($filters['status'] ?? null) === 'archived') {
            $query->withTrashed();
        }

        if (($filters['status'] ?? null) === 'archived') {
            $query->whereNotNull('users.deleted_at');
        } elseif (filled($filters['status'] ?? null)) {
            $query->where('account_status', $filters['status']);
        }

        if (filled($filters['role'] ?? null)) {
            $role = $filters['role'];
            $query->where(function ($roleQuery) use ($role) {
                if ($role === 'administrator') {
                    $roleQuery->where('is_admin', true)->orWhere('role', 'administrator')->orWhere('role', 'admin');
                    return;
                }

                $roleQuery->where(function ($partnerQuery) {
                    $partnerQuery->where('is_admin', false)->orWhereNull('is_admin');
                })->where(function ($partnerQuery) {
                    $partnerQuery->whereNull('role')->orWhere('role', 'partner');
                });
            });
        }

        if (filled($filters['search'] ?? null)) {
            $search = trim($filters['search']);
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('users.id', $search)
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhereHas('user_invoice', function ($invoiceQuery) use ($search) {
                        $invoiceQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('vatnumber', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user_shipping', fn ($shippingQuery) => $shippingQuery
                        ->where('receiver', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $this->applyDateFilter($query, 'created_at', $filters['created_from'] ?? null, $filters['created_to'] ?? null);
        $this->applyDateFilter($query, 'last_login_at', $filters['login_from'] ?? null, $filters['login_to'] ?? null);

        if (filter_var($filters['without_orders'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $query->whereDoesntHave('orders');
        }

        if (filled($filters['order_from'] ?? null)) {
            $query->whereHas('orders', fn ($orderQuery) => $orderQuery->whereDate('created_at', '>=', $filters['order_from']));
        }
        if (filled($filters['order_to'] ?? null)) {
            $query->whereHas('orders', fn ($orderQuery) => $orderQuery->whereDate('created_at', '<=', $filters['order_to']));
        }
        if (filled($filters['inactive_days'] ?? null)) {
            $cutoff = now()->subDays((int) $filters['inactive_days'])->startOfDay();
            $query->where(function ($inactiveQuery) use ($cutoff) {
                $inactiveQuery->whereDoesntHave('orders')
                    ->orWhereHas('orders', fn ($orderQuery) => $orderQuery->whereDate('created_at', '<=', $cutoff));
            });
        }

        return $query->latest('users.created_at')
            ->paginate(20)
            ->withQueryString();
    }

    public function find(int $id): User
    {
        return User::withTrashed()
            ->with([
                'user_invoice',
                'user_shipping',
                'registrationRequests.reviewer',
                'orders' => fn ($query) => $query->withTrashed()->latest()->limit(10),
            ])
            ->withCount('special_prices')
            ->findOrFail($id);
    }

    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $role = $data['role'] ?? $user->technicalRole();
            $status = $data['account_status'] ?? ($user->account_status ?: 'active');
            $this->guardAdminChange($user, $actor, $role, $status);

            $emailChanged = $user->email !== $data['email'];
            $user->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'account_status' => $status,
                'role' => $role,
                'is_admin' => $role === 'administrator',
                'admin_note' => $data['admin_note'] ?? null,
                'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
            ])->save();

            User_invoce::updateOrCreate(
                ['user_id' => $user->id],
                $data['invoice']
            );

            $defaultAddressId = collect($data['shipping'] ?? [])
                ->filter(fn (array $addressData) => (bool) ($addressData['is_default'] ?? false))
                ->keys()
                ->first();
            if ($defaultAddressId) {
                User_shipping::where('user_id', $user->id)->update(['is_default' => false]);
            }

            foreach ($data['shipping'] ?? [] as $addressId => $addressData) {
                $address = $user->user_shipping()->whereKey($addressId)->first();
                if (!$address) {
                    continue;
                }
                $addressData['is_default'] = $defaultAddressId && (string) $defaultAddressId === (string) $addressId;
                $address->forceFill($addressData)->save();
            }

            $newShipping = $data['new_shipping'] ?? [];
            if (filled($newShipping['address'] ?? null) || filled($newShipping['receiver'] ?? null)) {
                $user->user_shipping()->create($newShipping);
            }

            app(AccountService::class)->ensureDefaultAddress($user);

            $this->audit->record('admin.user.updated', $user, [
                'changed_fields' => array_keys($data),
                'email_changed' => $emailChanged,
                'role' => $role,
                'account_status' => $status,
            ]);

            return $user->fresh(['user_invoice', 'user_shipping']);
        });
    }

    public function archive(User $user, User $actor): void
    {
        $this->guardAdminChange($user, $actor, 'archived', 'archived');
        DB::transaction(function () use ($user) {
            $user->delete();
            $this->audit->record('admin.user.archived', $user, ['user_id' => $user->id]);
        });
    }

    public function restore(User $user, User $actor): User
    {
        abort_unless($actor->hasPermission('admin.access'), 403);
        if (!$user->trashed()) {
            return $user;
        }

        $user->restore();
        $this->audit->record('admin.user.restored', $user, ['user_id' => $user->id]);
        return $user->fresh();
    }

    public function sendPasswordReset(User $user, User $actor): string
    {
        abort_unless($actor->hasPermission('admin.access'), 403);
        if ($user->trashed()) {
            throw ValidationException::withMessages(['user' => 'Archivált felhasználónak nem küldhető visszaállító link.']);
        }

        $status = Password::sendResetLink(['email' => $user->email]);
        $this->audit->record('admin.user.password_reset_requested', $user, [
            'status' => $status,
        ]);

        return $status;
    }

    private function guardAdminChange(User $user, User $actor, string $role, string $status): void
    {
        abort_unless($actor->hasPermission('admin.access'), 403);

        if ($role === 'archived') {
            if ($user->is($actor) || $user->is_admin) {
                throw ValidationException::withMessages(['user' => 'Saját vagy admin fiók nem archiválható.']);
            }
            return;
        }

        if ($role === 'administrator' && $status !== 'active') {
            throw ValidationException::withMessages(['account_status' => 'Az adminisztrátori fiók állapota csak aktív lehet.']);
        }

        if ($user->is($actor) && ($role !== 'administrator' || $status !== 'active')) {
            throw ValidationException::withMessages(['role' => 'A saját utolsó admin jogosultságod nem vehető el.']);
        }

        if ($user->is_admin && $role !== 'administrator' && User::where('is_admin', true)->where('id', '<>', $user->id)->count() === 0) {
            throw ValidationException::withMessages(['role' => 'Az utolsó adminisztrátor jogosultsága nem vehető el.']);
        }
    }

    private function applyDateFilter($query, string $column, ?string $from, ?string $to): void
    {
        $query->when($from, fn ($builder) => $builder->whereDate($column, '>=', $from))
            ->when($to, fn ($builder) => $builder->whereDate($column, '<=', $to));
    }
}
