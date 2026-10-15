<div class="v6-table-wrap">
    <table class="v6-table" style="min-width:980px">
        <thead><tr><th><input type="checkbox" data-select-all aria-label="Látható rendelések kijelölése"></th><th>Rendelés / partner</th><th>Tételek</th><th>Nettó érték</th><th>Dátum</th><th>Állapot</th><th>Művelet</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            @php($company = $order->user?->user_invoice?->company_name ?: $order->name ?: $order->email)
            <tr>
                <td><input type="checkbox" data-order-checkbox form="bulk-order-form" name="order_ids[]" value="{{ $order->id }}" aria-label="{{ $order->displayNumber() }} kijelölése"></td>
                <td><a href="{{ route('adminpanel.orders.view', $order->id) }}"><strong>{{ $order->displayNumber() }}</strong></a><br><small>{{ $company }} · {{ $order->email }}</small>@if($order->trashed())<br><span class="v6-status v6-status--danger">Törölt</span>@endif</td>
                <td>{{ $order->items->count() }}</td>
                <td><strong>{{ number_format((float) ($order->subtotal_net ?: $order->total), 0, ',', ' ') }} Ft</strong></td>
                <td>{{ optional($order->created_at)->format('Y. m. d. H:i') }}</td>
                <td>
                    @if(!$order->trashed())
                        <form method="POST" action="{{ route('adminpanel.orders.status.update', $order->id) }}" class="v6-actions">
                            @csrf
                            <select name="status" aria-label="{{ $order->displayNumber() }} állapota">@foreach($order->statusOptions() as $state)<option value="{{ $state }}" @selected($order->status === $state)>{{ ucfirst($state) }}</option>@endforeach</select>
                            <button class="v6-button v6-button--small v6-button--outline" type="submit">Mentés</button>
                        </form>
                    @else
                        <span class="v6-status v6-status--danger">Archivált</span>
                    @endif
                </td>
                <td class="v6-actions"><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.orders.view', $order->id) }}">Adatlap</a></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="v6-empty"><strong>Nincs ilyen rendelés</strong><span>Módosítsd a keresést vagy a szűrőket.</span></div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="v6-actions" style="justify-content:center;margin-top:18px">{{ $orders->links() }}</div>
