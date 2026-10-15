<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ManualOrderService;
use App\Services\OrderManagementService;
use App\Services\OrderNotificationService;
use App\Services\OrderStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request, OrderManagementService $orders)
    {
        $paginated = $orders->paginate($request);

        if ($request->ajax()) {
            return view('admin.m11.orders.partials.list', ['orders' => $paginated, 'states' => Order::statuses()]);
        }

        return view('admin.m11.orders.index', ['orders' => $paginated, 'states' => Order::statuses()]);
    }

    public function create()
    {
        return view('admin.m11.orders.create', [
            'partners' => User::query()->with(['user_invoice', 'user_shipping'])
                ->where('is_admin', false)->where('account_status', 'active')->orderBy('name')->get(),
            'products' => Products::query()->where('is_archived', false)->with('product_unit')->orderBy('title')->get(),
            'states' => array_diff(Order::statuses(), ['törölve']),
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(StoreAdminOrderRequest $request, ManualOrderService $manualOrders, OrderNotificationService $notifications)
    {
        $order = $manualOrders->create($request->user(), $request->validated());
        if ($order->wasRecentlyCreated) {
            $notifications->dispatch($order);
        }

        return redirect()->route('adminpanel.orders.view', $order->id)
            ->with('success', $order->wasRecentlyCreated ? 'A kézi rendelés rögzítve.' : 'A kézi rendelés már korábban rögzítve lett.');
    }

    public function storeFilterUrl(Request $request)
    {
        session(['orders_filter_url' => route('adminpanel.orders', $request->query())]);
        return response()->noContent();
    }

    public function view(int $id)
    {
        $order = Order::withTrashed()->with([
            'user', 'user_invoice', 'user_shipping', 'items.product.product_unit',
            'statusHistories.responsible', 'statusResponsible', 'createdByAdmin',
        ])->findOrFail($id);

        return view('admin.m11.orders.view', ['order' => $order, 'states' => $order->statusOptions()]);
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        int $id,
        OrderStatusService $statuses,
        OrderNotificationService $notifications,
    ) {
        $order = Order::findOrFail($id);
        $from = $order->status;
        $updated = $statuses->change($order, $request->validated('status'), $request->user(), $request->validated('note'));
        if ($from !== $updated->status && ($history = $updated->statusHistories->first())) {
            $notifications->dispatchStatusChanged($updated, $history);
        }

        return back()->with('success', 'A rendelés státusza frissítve.');
    }

    public function bulkStatus(Request $request, OrderStatusService $statuses, OrderNotificationService $notifications)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:100'],
            'order_ids.*' => ['integer', 'distinct', 'exists:orders,id'],
            'status' => ['required', 'string', 'in:'.implode(',', Order::allowedStatuses())],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])->get()->keyBy('id');
        $result = $statuses->bulkChange($orders, $validated['status'], $request->user(), $validated['note'] ?? null);
        foreach ($result['changed'] as $changed) {
            if ($history = $changed->statusHistories->first()) {
                $notifications->dispatchStatusChanged($changed, $history);
            }
        }

        $message = count($result['changed']).' rendelés frissítve.';
        if ($result['errors'] !== []) {
            return back()->with('warning', $message)->with('bulk_errors', $result['errors']);
        }

        return back()->with('success', $message);
    }

    public function destroy(int $id, AuditLogger $audit)
    {
        $order = Order::findOrFail($id);
        $number = $order->displayNumber();
        $order->delete();
        $audit->record('order.deleted', $order, ['order_number' => $number]);

        return redirect()->route('adminpanel.orders')->with('success', 'A rendelés elrejtve az aktív listából. Az adatok megmaradtak.');
    }

    public function getpdf(int $id)
    {
        $order = Order::withTrashed()->with('user', 'items', 'items.product', 'user_shipping')->findOrFail($id);
        $pdf = Pdf::loadView('myPDF', ['order' => $order, 'states' => Order::statuses(), 'id' => $id]);
        return $pdf->download($order->displayNumber().'.pdf');
    }
}
