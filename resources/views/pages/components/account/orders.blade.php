@extends('pages.account')
@section('title','Rendelések')
@section('content')
<div class="accounts-page account-v6-content"><div class="v6-container"><div class="row">
    <div class="col-lg-3 col-md-4 col-sm-12">@include('pages.components.account.header')</div>
    <div class="col-lg-9 col-md-8 col-sm-12"><section class="orders-box account-v6-panel">
        <div class="account-v6-toolbar"><h1 class="orders-box-title section-header mb-0">Rendelések</h1>
            <form method="get" class="account-v6-filter" aria-label="Rendelések szűrése"><select name="status" aria-label="Státusz"><option value="">Minden státusz</option>@foreach($statuses as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select><input type="date" name="from" value="{{ request('from') }}" aria-label="Kezdő dátum"><input type="date" name="to" value="{{ request('to') }}" aria-label="Záró dátum"><button class="v6-button v6-button--outline" type="submit">Szűrés</button></form>
        </div>
        @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
        <div class="table-responsive"><table class="account-v6-table"><thead><tr><th>Dátum</th><th>Net. végösszeg</th><th>Státusz</th><th>Rendelésazonosító</th><th>Műveletek</th></tr></thead><tbody>
        @forelse($orders as $order)<tr><td>{{ optional($order->created_at)->format('Y. m. d.') }}</td><td>{{ $order->is_direct_shipping ? 'Árazatlan' : number_format($order->subtotal_net ?: $order->total, 0, ',', ' ').' Ft' }}</td><td><span class="v6-status v6-status--neutral">{{ $order->status }}</span></td><td>{{ $order->displayNumber() }}</td><td class="account-v6-actions"><a href="{{ route('account.details', $order->id) }}" class="v6-button v6-button--outline">Részletek</a><form action="{{ route('account.orders.reorder', $order) }}" method="post">@csrf<button type="submit" class="v6-button v6-button--primary">Újrarendelés</button></form></td></tr>
        @empty<tr><td colspan="5" class="text-center">Nincs megjeleníthető rendelés.</td></tr>@endforelse
        </tbody></table></div>{{ $orders->links() }}
    </section></div>
</div></div></div>
@endsection
