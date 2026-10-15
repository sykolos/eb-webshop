<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OrderStatusService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function change(Order $order, string $status, ?User $responsible = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $status, $responsible, $note): Order {
            $locked = Order::query()->lockForUpdate()->find($order->id);
            if (!$locked) {
                throw (new ModelNotFoundException())->setModel(Order::class, [$order->id]);
            }

            if (!in_array($status, Order::allowedStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Ismeretlen rendelési státusz.']);
            }

            if ($locked->status !== $status && !Order::canTransition((string) $locked->status, $status)) {
                throw ValidationException::withMessages([
                    'status' => sprintf('A rendelés státusza ebből az állapotból nem módosítható: %s -> %s.', $locked->status, $status),
                ]);
            }

            $from = (string) $locked->status;
            $locked->forceFill([
                'status' => $status,
                'status_responsible_id' => $responsible?->id,
                'status_note' => $note,
            ])->save();

            if ($from !== $status) {
                $history = OrderStatusHistory::create([
                    'order_id' => $locked->id,
                    'from_status' => $from,
                    'to_status' => $status,
                    'responsible_id' => $responsible?->id,
                    'note' => $note,
                ]);
                $this->audit->record('order.status_updated', $locked, [
                    'from' => $from,
                    'to' => $status,
                    'history_id' => $history->id,
                    'note' => $note,
                ]);
            }

            return $locked->fresh(['statusHistories.responsible', 'statusResponsible']);
        });
    }

    public function bulkChange(iterable $orders, string $status, User $responsible, ?string $note = null): array
    {
        $changed = [];
        $errors = [];

        foreach ($orders as $order) {
            try {
                $changed[] = $this->change($order, $status, $responsible, $note);
            } catch (ValidationException $exception) {
                $errors[] = [
                    'order' => $order->displayNumber(),
                    'message' => collect($exception->errors())->flatten()->first() ?? 'A státuszváltás nem sikerült.',
                ];
            } catch (ModelNotFoundException) {
                $errors[] = [
                    'order' => $order->displayNumber(),
                    'message' => 'A rendelés időközben eltűnt vagy törölve lett.',
                ];
            }
        }

        return compact('changed', 'errors');
    }

    public function recordInitial(Order $order, User $responsible, ?string $note = null): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => null,
            'to_status' => $order->status,
            'responsible_id' => $responsible->id,
            'note' => $note,
        ]);
    }
}
