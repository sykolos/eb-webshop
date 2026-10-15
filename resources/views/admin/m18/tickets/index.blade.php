@extends('layouts.v6.admin')

@section('title', 'Ticketek')
@section('heading', 'Ticketek')
@section('intro')<p>Partnerenként elkülönített ügyfélszolgálati, rendelési és fejlesztői kommunikáció.</p>@endsection

@section('content')
<div class="v6-section">
    <div class="v6-notice">A belső megjegyzések és mellékletek csak adminisztrátoroknak láthatók. A partneroldali kommunikáció külön jogosultság- és ticket-azonosítás után érhető el.</div>
    @if(session('success'))<div class="v6-toast">{{ session('success') }}</div>@endif
    <form class="v6-filter-bar" method="GET" action="{{ route('adminpanel.tickets') }}">
        <label>Keresés<input type="search" name="search" value="{{ request('search') }}" placeholder="Ticket, tárgy, partner"></label>
        <label>Állapot<select name="status"><option value="">Minden állapot</option>@foreach($statuses as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label>Prioritás<select name="priority"><option value="">Minden prioritás</option>@foreach($priorities as $key => $label)<option value="{{ $key }}" @selected(request('priority') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label>Típus<select name="type"><option value="">Minden típus</option>@foreach($types as $key => $label)<option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>@endforeach</select></label>
        <label>Felelős<select name="assigned_to"><option value="">Minden felelős</option>@foreach($assignees as $assignee)<option value="{{ $assignee->id }}" @selected((string) request('assigned_to') === (string) $assignee->id)>{{ $assignee->name }}</option>@endforeach</select></label>
        <button class="v6-button v6-button--primary" type="submit">Szűrés</button><a class="v6-button v6-button--outline" href="{{ route('adminpanel.tickets') }}">Alaphelyzet</a>
    </form>
    <div class="v6-section__header"><div><span class="v6-eyebrow">M18.01 · M18.02 · M18.05</span><h2>Ticket munkalista</h2><p>{{ $tickets->total() }} ticket, a szűrés szerveroldalon történik.</p></div><a class="v6-button v6-button--outline" href="{{ route('adminpanel.exports') }}">Ticket exportok</a></div>
    <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Ticket</th><th>Partner</th><th>Tárgy</th><th>Típus</th><th>Prioritás</th><th>Állapot</th><th>Felelős</th><th>Utolsó válasz</th><th></th></tr></thead><tbody>@forelse($tickets as $ticket)<tr><td><strong>{{ $ticket->ticket_number }}</strong>@if($ticket->has_unread)<small class="v6-status v6-status--warning">Új üzenet</small>@endif @if($ticket->order)<small>Rendelés: {{ $ticket->order->displayNumber() }}</small>@endif</td><td>{{ $ticket->user?->name ?: '—' }}<small>{{ $ticket->user?->email }}</small></td><td>{{ $ticket->subject }}</td><td>{{ $types[$ticket->type] ?? $ticket->type }}</td><td><span class="v6-status v6-status--{{ in_array($ticket->priority, ['high','critical'], true) ? 'danger' : ($ticket->priority === 'low' ? 'neutral' : 'warning') }}">{{ $priorities[$ticket->priority] ?? $ticket->priority }}</span></td><td><span class="v6-status v6-status--{{ in_array($ticket->status, ['resolved','closed'], true) ? 'success' : ($ticket->status === 'waiting_customer' ? 'warning' : 'neutral') }}">{{ $statuses[$ticket->status] ?? $ticket->status }}</span></td><td>{{ $ticket->assignee?->name ?: 'Nincs kijelölve' }}</td><td>{{ $ticket->last_reply_at?->format('Y-m-d H:i') }}</td><td><a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.tickets.show', $ticket) }}">Megnyitás</a></td></tr>@empty<tr><td colspan="9"><div class="v6-empty"><strong>Nincs ticket</strong><span>A szűrésnek megfelelő ticket még nincs.</span></div></td></tr>@endforelse</tbody></table></div>
    <div class="v6-pagination">{{ $tickets->links() }}</div>
</div>
@endsection
