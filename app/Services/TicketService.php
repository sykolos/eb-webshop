<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketRead;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class TicketService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly SecureFileStorage $files,
        private readonly AutomationRuleService $automations,
    ) {
    }

    public function create(User $owner, array $data, ?UploadedFile $attachment = null): Ticket
    {
        return DB::transaction(function () use ($owner, $data, $attachment): Ticket {
            $order = $this->ownedOrder($owner, $data['order_id'] ?? null);
            $priority = $data['priority'] ?? 'normal';
            $ticket = Ticket::create([
                'ticket_number' => $this->nextNumber(),
                'user_id' => $owner->id,
                'order_id' => $order?->id,
                'type' => $data['type'],
                'priority' => $priority,
                'status' => 'new',
                'subject' => $data['subject'],
                'last_reply_at' => now(),
                'last_customer_reply_at' => now(),
                'response_due_at' => now()->addHours((int) config('tickets.response_hours.'.$priority, 24)),
                'resolution_due_at' => now()->addHours((int) config('tickets.resolution_hours.'.$priority, 72)),
            ]);
            $message = $this->createMessage($ticket, $owner, $data['body'], false, $attachment);
            $this->audit->record('ticket.created', $ticket, ['ticket_number' => $ticket->ticket_number, 'message_id' => $message->id, 'order_id' => $ticket->order_id]);
            return $ticket->fresh(['user', 'order', 'messages.author', 'messages.attachments']);
        });
    }

    public function adminQuery(array $filters = []): Builder
    {
        return Ticket::query()->with(['user', 'order', 'assignee'])
            ->when(filled($filters['search'] ?? null), function (Builder $query) use ($filters): void {
                $term = trim($filters['search']);
                $query->where(function (Builder $nested) use ($term): void {
                    $nested->where('ticket_number', 'like', '%'.$term.'%')
                        ->orWhere('subject', 'like', '%'.$term.'%')
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(filled($filters['priority'] ?? null), fn (Builder $query) => $query->where('priority', $filters['priority']))
            ->when(filled($filters['type'] ?? null), fn (Builder $query) => $query->where('type', $filters['type']))
            ->when(filled($filters['assigned_to'] ?? null), fn (Builder $query) => $query->where('assigned_to', $filters['assigned_to']))
            ->latest('last_reply_at');
    }

    public function customerQuery(User $owner): Builder
    {
        return Ticket::query()->where('user_id', $owner->id)->with(['order', 'assignee'])->latest('last_reply_at');
    }

    public function forActor(Ticket $ticket, User $actor, bool $includeInternal = false): Ticket
    {
        if (! $this->isAdmin($actor) && $ticket->user_id !== $actor->id) {
            throw (new ModelNotFoundException())->setModel(Ticket::class, [$ticket->id]);
        }

        $ticket->load(['user', 'order', 'assignee', 'statusHistories.changedBy', 'messages' => function ($query) use ($includeInternal, $actor): void {
            $query->with(['author', 'attachments'])->when(! $includeInternal || ! $this->isAdmin($actor), fn ($messages) => $messages->where('is_internal', false))->oldest('created_at');
        }]);
        $this->markRead($ticket, $actor);
        return $ticket;
    }

    public function addMessage(Ticket $ticket, User $actor, string $body, bool $internal = false, ?UploadedFile $attachment = null): TicketMessage
    {
        if (! $this->isAdmin($actor) && ($internal || $ticket->user_id !== $actor->id)) {
            throw (new ModelNotFoundException())->setModel(Ticket::class, [$ticket->id]);
        }
        if ($internal && ! $this->isAdmin($actor)) {
            throw ValidationException::withMessages(['body' => 'Belső megjegyzést csak adminisztrátor írhat.']);
        }

        return DB::transaction(function () use ($ticket, $actor, $body, $internal, $attachment): TicketMessage {
            $message = $this->createMessage($ticket, $actor, $body, $internal, $attachment);
            $updates = ['last_reply_at' => now()];
            if ($internal) {
                $updates['last_admin_reply_at'] = now();
            } elseif ($this->isAdmin($actor)) {
                $updates['last_admin_reply_at'] = now();
                if ($ticket->status !== 'closed') {
                    $updates['status'] = 'waiting_customer';
                }
                $this->automations->dispatchEvent('ticket_update', $ticket->user, ['ticket_id' => $ticket->id, 'message_id' => $message->id]);
            } else {
                $updates['last_customer_reply_at'] = now();
                if (in_array($ticket->status, ['waiting_customer', 'resolved'], true)) {
                    $updates['status'] = 'in_progress';
                }
            }
            $ticket->forceFill($updates)->save();
            $this->audit->record($internal ? 'ticket.internal_message_added' : 'ticket.message_added', $ticket, ['message_id' => $message->id, 'internal' => $internal]);
            return $message->fresh(['author', 'attachments']);
        });
    }

    public function changeStatus(Ticket $ticket, User $actor, string $status, ?string $note = null): Ticket
    {
        abort_unless($this->isAdmin($actor), 403);
        if (! in_array($status, config('tickets.statuses', []), true)) {
            throw ValidationException::withMessages(['status' => 'Ismeretlen ticketállapot.']);
        }
        if ($ticket->status !== $status && ! $this->canTransition((string) $ticket->status, $status)) {
            throw ValidationException::withMessages(['status' => 'Ez az állapotváltás nem engedélyezett.']);
        }
        if ($ticket->status === $status && blank($note)) {
            return $ticket;
        }

        return DB::transaction(function () use ($ticket, $actor, $status, $note): Ticket {
            $from = $ticket->status;
            $ticket->forceFill([
                'status' => $status,
                'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? ($ticket->resolved_at ?: now()) : null,
                'closed_at' => $status === 'closed' ? now() : null,
            ])->save();
            TicketStatusHistory::create(['ticket_id' => $ticket->id, 'changed_by_user_id' => $actor->id, 'from_status' => $from, 'to_status' => $status, 'note' => $note]);
            $this->audit->record('ticket.status_changed', $ticket, ['from' => $from, 'to' => $status, 'note' => $note]);
            return $ticket->fresh(['user', 'order', 'assignee']);
        });
    }

    public function assign(Ticket $ticket, User $actor, ?User $assignee): Ticket
    {
        abort_unless($this->isAdmin($actor), 403);
        abort_unless(! $assignee || $this->isAdmin($assignee), 422, 'Csak admin jogosultságú felhasználó jelölhető felelősnek.');
        $ticket->forceFill(['assigned_to' => $assignee?->id])->save();
        $this->audit->record('ticket.assigned', $ticket, ['assigned_to' => $assignee?->id]);
        return $ticket->fresh('assignee');
    }

    public function markRead(Ticket $ticket, User $actor): void
    {
        if (! $this->isAdmin($actor) && $ticket->user_id !== $actor->id) {
            return;
        }
        TicketRead::updateOrCreate(['ticket_id' => $ticket->id, 'user_id' => $actor->id], ['last_read_at' => now()]);
    }

    public function unread(Ticket $ticket, User $actor): bool
    {
        $readAt = TicketRead::query()->where('ticket_id', $ticket->id)->where('user_id', $actor->id)->value('last_read_at');
        return $ticket->messages()->where('created_at', '>', $readAt ?: '1970-01-01')->when(! $this->isAdmin($actor), fn ($query) => $query->where('is_internal', false))->exists();
    }

    public function purgeExpired(): int
    {
        $deleted = 0;
        $cutoff = now()->subMonths((int) config('tickets.retention_months', 24));

        Ticket::query()
            ->whereNotNull('closed_at')
            ->where('closed_at', '<', $cutoff)
            ->with('messages.attachments')
            ->chunkById(100, function ($tickets) use (&$deleted): void {
                foreach ($tickets as $ticket) {
                    DB::transaction(function () use ($ticket, &$deleted): void {
                        foreach ($ticket->messages as $message) {
                            foreach ($message->attachments as $attachment) {
                                $this->files->deletePrivate($attachment->path);
                            }
                        }

                        $ticket->delete();
                        $deleted++;
                    });
                }
            });

        return $deleted;
    }

    public function attachmentForActor(TicketAttachment $attachment, User $actor): TicketAttachment
    {
        $attachment->loadMissing('message.ticket');
        $ticket = $attachment->message?->ticket;
        abort_unless($ticket && ($this->isAdmin($actor) || (! $attachment->message->is_internal && $ticket->user_id === $actor->id)), 404);
        return $attachment;
    }

    public function isAdmin(User $user): bool
    {
        return $user->hasPermission('admin.access');
    }

    private function createMessage(Ticket $ticket, User $actor, string $body, bool $internal, ?UploadedFile $attachment): TicketMessage
    {
        $message = TicketMessage::create(['ticket_id' => $ticket->id, 'author_user_id' => $actor->id, 'body' => trim($body), 'is_internal' => $internal]);
        if ($attachment) {
            $path = $this->files->storePrivate($attachment, 'tickets/'.$ticket->id, config('tickets.attachment_extensions'), (int) config('tickets.attachment_max_kilobytes'));
            $message->attachments()->create([
                'uploaded_by_user_id' => $actor->id,
                'disk' => 'private',
                'path' => $path,
                'original_name' => Str::limit($attachment->getClientOriginalName(), 191, ''),
                'mime_type' => $attachment->getMimeType() ?: 'application/octet-stream',
                'size_bytes' => (int) $attachment->getSize(),
                'sha256' => hash_file('sha256', $attachment->getRealPath()),
            ]);
        }
        return $message;
    }

    private function ownedOrder(User $owner, mixed $orderId): ?Order
    {
        if (! $orderId) {
            return null;
        }
        $order = Order::query()->whereKey((int) $orderId)->where('user_id', $owner->id)->first();
        if (! $order) {
            throw ValidationException::withMessages(['order_id' => 'Csak a saját rendelésedhez kapcsolhatsz ticketet.']);
        }
        return $order;
    }

    private function nextNumber(): string
    {
        do {
            $number = 'EBT-'.now()->format('ymd').'-'.strtoupper(Str::random(6));
        } while (Ticket::query()->where('ticket_number', $number)->exists());
        return $number;
    }

    private function canTransition(string $from, string $to): bool
    {
        return in_array($to, match ($from) {
            'new' => ['in_progress', 'closed'],
            'in_progress' => ['waiting_customer', 'resolved', 'closed'],
            'waiting_customer' => ['in_progress', 'resolved', 'closed'],
            'resolved' => ['closed', 'in_progress'],
            'closed' => ['in_progress'],
            default => [],
        }, true);
    }
}
