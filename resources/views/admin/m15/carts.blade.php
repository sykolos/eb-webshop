@extends('layouts.v6.admin')
@section('title', 'Kosárfigyelés')
@section('heading', 'Kosárfigyelés')
@section('intro', 'Tulajdonos, utolsó aktivitás, aktuális tételek és állapot. Az ár az admin aktuális partneri nézete.')
@section('content')
<div class="v6-section">
    <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel.carts') }}"><label>Állapot<select name="state"><option value="">Minden állapot</option><option value="active" @selected(request('state') === 'active')>Aktív</option><option value="abandoned" @selected(request('state') === 'abandoned')>Elhagyott</option><option value="converted" @selected(request('state') === 'converted')>Rendeléssé vált</option></select></label><button class="v6-button v6-button--primary" type="submit">Szűrés</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel') }}">Vissza a dashboardra</a></form>
    <div class="v6-notice">A kosár állapota a mentett aktivitásból számolt munkajelzés. A rendelés üzleti igazságforrása továbbra is az `orders` tábla.</div>
    <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Partner</th><th>Utolsó aktivitás</th><th>Tételek</th><th>Aktuális nettó</th><th>Állapot</th></tr></thead><tbody>@forelse($rows as $row)<tr><td><strong>{{ $row['cart']->user?->name ?: 'Ismeretlen partner' }}</strong><small>{{ $row['cart']->user?->email }}</small></td><td>{{ $row['cart']->last_activity_at?->format('Y-m-d H:i') ?: '—' }}</td><td>{{ $row['cart']->items_count }}</td><td>{{ number_format($row['total'], 0, ',', ' ') }} Ft</td><td><span class="v6-status {{ $row['state'] === 'active' ? 'v6-status--success' : ($row['state'] === 'converted' ? 'v6-status--neutral' : 'v6-status--warning') }}">{{ ['active' => 'Aktív', 'abandoned' => 'Elhagyott', 'converted' => 'Rendeléssé vált'][$row['state']] }}</span></td></tr>@empty<tr><td colspan="5"><div class="v6-empty"><strong>Nincs megjeleníthető kosár</strong><span>Csak legalább egy tételt tartalmazó kosarak jelennek meg.</span></div></td></tr>@endforelse</tbody></table></div>
    <div class="v6-pagination">{{ $carts->links() }}</div>
</div>
@endsection
