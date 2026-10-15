@extends('layouts.v6.admin')
@section('title', 'Aktivitás és tracking')
@section('heading', 'Aktivitás és tracking')
@section('intro', 'Hozzájárulás után, saját adatbázisban tárolt események. Külső szolgáltató nincs aktiválva.')
@section('content')
<div class="v6-section">
    <div class="v6-notice">A mérés csak az `accepted` hozzájárulási cookie mellett ír eseményt. A payload személyes és partnerár mezőit maszkoljuk, az admin és belépési oldalak nincsenek automatikusan mérve. Megőrzés: 6 hónap; szolgáltatói késés: nem alkalmazható, mert nincs külső provider bekötve.</div>
    <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel.activity') }}"><label>Esemény<select name="event_name"><option value="">Minden esemény</option>@foreach($eventNames as $name)<option value="{{ $name }}" @selected(request('event_name') === $name)>{{ $name }}</option>@endforeach</select></label><label>Dátumtól<input type="date" name="from" value="{{ request('from') }}"></label><label>Dátumig<input type="date" name="to" value="{{ request('to') }}"></label><button class="v6-button v6-button--primary" type="submit">Szűrés</button></form>
    <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Esemény</th><th>Útvonal</th><th>Eszköz</th><th>Forrás</th><th>Időpont</th><th>Adat</th></tr></thead><tbody>@forelse($events as $event)<tr><td><span class="v6-status v6-status--neutral">{{ $event->event_name }}</span></td><td>{{ $event->page_path ?: '/' }}</td><td>{{ $event->device_type ?: '—' }}</td><td>{{ $event->campaign_source ?: 'direkt' }}</td><td>{{ $event->occurred_at?->format('Y-m-d H:i:s') }}</td><td><code>{{ json_encode($tracking->mask($event)['payload'], JSON_UNESCAPED_UNICODE) }}</code></td></tr>@empty<tr><td colspan="6"><div class="v6-empty"><strong>Még nincs mért esemény</strong><span>Elutasított hozzájárulás mellett ez a lista szándékosan üres marad.</span></div></td></tr>@endforelse</tbody></table></div>
    <div class="v6-pagination">{{ $events->links() }}</div>
</div>
@endsection
