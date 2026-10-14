<div class="v6-table-wrap">
    <table class="v6-table">
        <thead><tr><th>Termék</th><th>Kategória</th><th>Egység</th><th>Elérhetőség</th><th>Állapot</th><th>Művelet</th></tr></thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td><strong>{{ $product->title }}</strong><br><small>{{ $product->serial_number }}{{ $product->manufacturer ? ' · '.$product->manufacturer : '' }}</small></td>
                <td>{{ $product->category?->name ?? 'Nincs kategória' }}</td>
                <td>{{ $product->product_unit?->unit ?? 'Nincs egység' }}<br><small>{{ $product->product_unit?->quantity }} {{ $product->product_unit?->measure }}</small></td>
                <td><span class="v6-status {{ $product->availability_status === 'in_stock' ? 'v6-status--success' : 'v6-status--warning' }}">{{ \App\Models\Products::AVAILABILITY_STATUSES[$product->availability_status] ?? $product->availability_status }}</span></td>
                <td>
                    @if($product->is_archived)<span class="v6-status v6-status--danger">Archivált</span>
                    @elseif($product->is_published)<span class="v6-status v6-status--success">Publikus</span>
                    @else<span class="v6-status">Rejtett</span>@endif
                </td>
                <td class="v6-actions">
                    <a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.products.edit', $product->id) }}">Szerkesztés</a>
                    @if($product->is_archived)
                        <form method="POST" action="{{ route('adminpanel.products.restore', $product->id) }}">@csrf<button class="v6-button v6-button--small" type="submit">Visszaállítás</button></form>
                    @else
                        <form method="POST" action="{{ route('adminpanel.products.destroy', $product->id) }}" onsubmit="return confirm('A termék archiválva lesz, a rendelési előzmények megmaradnak. Folytatod?')">@csrf @method('DELETE')<button class="v6-button v6-button--small v6-button--danger" type="submit">Archiválás</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><div class="v6-empty"><strong>Nincs találat</strong><span>Próbálj más szűrőt vagy keresést.</span></div></td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $products->links() }}
