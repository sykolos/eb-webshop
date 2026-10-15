<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class OrderManagementService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'company_name' => ['nullable', 'string', 'max:120'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'string', 'in:'.implode(',', Order::allowedStatuses())],
            'min_total' => ['nullable', 'numeric', 'min:0'],
            'max_total' => ['nullable', 'numeric', 'min:0', 'gte:min_total'],
            'include_deleted' => ['nullable', 'boolean'],
        ]);

        $query = Order::query()
            ->with(['user.user_invoice', 'items'])
            ->when($filters['include_deleted'] ?? false, fn ($builder) => $builder->withTrashed())
            ->when(filled($filters['search'] ?? null), function ($builder) use ($filters): void {
                $search = trim((string) $filters['search']);
                $builder->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('order_number', 'like', '%'.$search.'%')
                        ->orWhere('id', is_numeric($search) ? (int) $search : -1)
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('user.user_invoice', fn ($invoiceQuery) => $invoiceQuery->where('company_name', 'like', '%'.$search.'%'));
                });
            })
            ->when(filled($filters['company_name'] ?? null), fn ($builder) => $builder->whereHas(
                'user.user_invoice',
                fn ($invoiceQuery) => $invoiceQuery->where('company_name', 'like', '%'.$filters['company_name'].'%')
            ))
            ->when(filled($filters['from_date'] ?? null), fn ($builder) => $builder->whereDate('created_at', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($builder) => $builder->whereDate('created_at', '<=', $filters['to_date']))
            ->when(filled($filters['status'] ?? null), fn ($builder) => $builder->where('status', $filters['status']))
            ->when(filled($filters['min_total'] ?? null), fn ($builder) => $builder->where('total', '>=', $filters['min_total']))
            ->when(filled($filters['max_total'] ?? null), fn ($builder) => $builder->where('total', '<=', $filters['max_total']))
            ->latest('created_at');

        return $query->paginate(15)->appends($request->query());
    }
}
