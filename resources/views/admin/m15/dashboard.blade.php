@extends('layouts.v6.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('intro', 'Rendelési teljesítmény, munkalisták és első fél aktivitási mérés egy helyen.')

@section('content')
<div class="v6-section">
    <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel') }}">
        <label>Dátumtól<input type="date" name="from" value="{{ $from->toDateString() }}"></label>
        <label>Dátumig<input type="date" name="to" value="{{ $to->toDateString() }}"></label>
        <button class="v6-button v6-button--primary" type="submit">Frissítés</button>
        <a class="v6-button v6-button--outline" href="{{ route('adminpanel') }}">Alapértelmezett időszak</a>
    </form>

    <div class="v6-stat-grid">
        <div class="v6-stat"><span>Nettó rendelési érték</span><strong>{{ number_format($summary['net_total'], 0, ',', ' ') }} Ft</strong><small>{{ $from->format('Y.m.d') }} – {{ $to->format('Y.m.d') }}</small></div>
        <div class="v6-stat"><span>Rendelések</span><strong>{{ number_format($summary['order_count'], 0, ',', ' ') }}</strong><small>törölt nélkül</small></div>
        <div class="v6-stat"><span>Átlagos rendelés</span><strong>{{ number_format($summary['average_order'], 0, ',', ' ') }} Ft</strong><small>azonos időszakból</small></div>
        <div class="v6-stat"><span>Nyitott rendelések</span><strong>{{ number_format($summary['open_orders'], 0, ',', ' ') }}</strong><small>teljes aktív állomány</small></div>
    </div>

    <div class="v6-stat-grid" style="margin-top:14px">
        <a class="v6-stat" href="{{ route('adminpanel.partner-requests') }}"><span>Jóváhagyandó partnerek</span><strong>{{ $summary['pending_partners'] }}</strong><small>munkalista megnyitása</small></a>
        <a class="v6-stat" href="{{ route('adminpanel.products.data-quality') }}"><span>Hiányos terméktörzs</span><strong>{{ $summary['incomplete_products'] }}</strong><small>adatminőségi lista</small></a>
        <a class="v6-stat" href="{{ route('adminpanel.orders', ['status' => 'függőben']) }}"><span>Feldolgozandó rendelések</span><strong>{{ $summary['processing_orders'] }}</strong><small>rendeléslista megnyitása</small></a>
        <a class="v6-stat" href="{{ route('adminpanel.activity') }}"><span>Aktív látogatók</span><strong>{{ $summary['active_visitors'] }}</strong><small>utolsó 15 perc, becslés</small></a>
    </div>

    <div class="v6-dashboard-columns">
        <section class="v6-panel">
            <div class="v6-panel__header"><div><h2>Top termékek</h2><p>Nettó termékforgalom mentett rendelési árakból. A mennyiségi egységek nem kerülnek összeadásra.</p></div></div>
            <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Termék</th><th>Cikkszám</th><th>Nettó forgalom</th><th>Sor-mennyiség</th></tr></thead><tbody>
                @forelse($topProducts as $product)<tr><td>{{ $product->title ?: $product->product_title ?: 'Ismeretlen termék' }}</td><td>{{ $product->product_serial_number ?: $product->serial_number ?: '—' }}</td><td>{{ number_format((float) $product->net_sales, 0, ',', ' ') }} Ft</td><td>{{ $product->quantity }}</td></tr>@empty<tr><td colspan="4"><div class="v6-empty"><strong>Nincs adat erre az időszakra</strong><span>A rangsor csak nem törölt rendelésekből készül.</span></div></td></tr>@endforelse
            </tbody></table></div>
        </section>
        <section class="v6-panel">
            <div class="v6-panel__header"><div><h2>Kézi kiemeltek</h2><p>A számított top rangsortól külön kezelt admin sorrend.</p></div><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.recommended.edit') }}">Szerkesztés</a></div>
            <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>#</th><th>Termék</th></tr></thead><tbody>@forelse($featuredProducts as $featured)<tr><td>{{ $featured->sort_order + 1 }}</td><td>{{ $featured->product?->title ?: 'Törölt termék' }}</td></tr>@empty<tr><td colspan="2"><div class="v6-empty"><strong>Nincs kézi kiemelt termék</strong></div></td></tr>@endforelse</tbody></table></div>
        </section>
    </div>

    <div class="v6-dashboard-columns">
        <section class="v6-panel"><div class="v6-panel__header"><div><h2>Elérhetőségi állapotok</h2><p>Darabszám, nem mennyiségi készlet.</p></div></div><div class="v6-check-grid">@foreach($availability as $status => $count)<span class="v6-status v6-status--neutral">{{ \App\Models\Products::AVAILABILITY_STATUSES[$status] ?? $status }}: {{ $count }}</span>@endforeach</div></section>
        <section class="v6-panel"><div class="v6-panel__header"><div><h2>Figyelő nézetek</h2><p>Adminmegtekintés nem módosítja a kosár aktivitását.</p></div></div><div class="v6-actions"><a class="v6-button v6-button--outline" href="{{ route('adminpanel.carts') }}">Kosarak figyelése</a><a class="v6-button v6-button--outline" href="{{ route('adminpanel.activity') }}">Aktivitás és tracking</a><a class="v6-button v6-button--outline" href="{{ route('adminpanel.users', ['inactive_days' => 90]) }}">90 napja inaktív partnerek</a></div></section>
    </div>
</div>
@endsection
