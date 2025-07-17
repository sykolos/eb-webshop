{{-- 💻 Asztali nézet – táblázat --}}
<div class="card d-none d-lg-block">
    <div class="card-header bg-dark text-white">
        <h5>Rendelések</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Megrendelő</th>
                    <th>Tételek</th>
                    <th>Összeg</th>                         
                    <th>Dátum</th>                         
                    <th>Státusz</th>                             
                    <th>Művelet</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>
                        @if($order->user && $order->user->user_invoice)
                            {{ $order->user->user_invoice->company_name }}
                        @else
                            <em>N/A</em>
                        @endif
                    </td>
                    <td>{{ $order->items->count() }}</td>
                    <td>{{ number_format($order->total, 0, '', ' ') }} Ft</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}</td>
                    <td>
                        <span class="badge bg-@if($order->status == 'függőben')warning
                            @elseif($order->status == 'feldolgozás')info
                            @elseif($order->status == 'kiszállítva')success
                            @elseif($order->status == 'törölve')danger @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('adminpanel.orders.view', $order->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-eye"></i> Megtekint
                        </a> 
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- 📱 Mobil nézet – kártyák --}}
<div class="d-block d-lg-none">
    <div class="row row-cols-1 g-3">
        @foreach ($orders as $order)
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-2">#{{ $order->id }} – 
                        <small class="text-muted">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}
                        </small>
                    </h5>

                    <p class="mb-1"><strong>Megrendelő:</strong>
                        @if($order->user && $order->user->user_invoice)
                            {{ $order->user->user_invoice->company_name }}
                        @else
                            <em>N/A</em>
                        @endif
                    </p>

                    <p class="mb-1"><strong>Tételek száma:</strong> {{ $order->items->count() }}</p>
                    <p class="mb-1"><strong>Végösszeg:</strong> {{ number_format($order->total, 0, '', ' ') }} Ft</p>
                    <p class="mb-1"><strong>Státusz:</strong>
                        <span class="badge bg-@if($order->status == 'függőben')warning
                            @elseif($order->status == 'feldolgozás')info
                            @elseif($order->status == 'kiszállítva')success
                            @elseif($order->status == 'törölve')danger @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>

                    <div class="mt-3 d-grid">
                        <a href="{{ route('adminpanel.orders.view', $order->id) }}" class="btn btn-primary">
                            <i class="bi bi-eye"></i> Megtekintés
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>
