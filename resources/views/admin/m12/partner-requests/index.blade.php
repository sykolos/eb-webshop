@extends('layouts.v6.admin')

@section('title', 'Partnerkérelmek')
@section('heading', 'Partnerkérelmek')
@section('intro', 'Új partnerfiókok ellenőrzése, jóváhagyása és indokolt elutasítása.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header"><div><span class="v6-eyebrow">M12 jóváhagyás</span><h2>Beérkezett partnerkérelmek</h2><p>A jóváhagyás a partner fiókállapotát módosítja, a szerepkört nem változtatja meg.</p></div><a class="v6-button v6-button--outline" href="{{ route('adminpanel.users') }}">Felhasználók</a></div>
    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="GET" class="v6-actions" style="margin-bottom:18px"><label>Állapot<select name="status"><option value="">Minden állapot</option>@foreach(\App\Models\PartnerRegistrationRequest::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></label><button class="v6-button v6-button--outline" type="submit">Szűrés</button></form>
    <div class="v6-table-wrap"><table class="v6-table" style="min-width:980px"><thead><tr><th>Név / cég</th><th>Email</th><th>Telefon</th><th>Állapot</th><th>Beküldve</th><th>Művelet</th></tr></thead><tbody>
    @forelse($requests as $registrationRequest)
        <tr><td><strong>{{ $registrationRequest->company_name }}</strong><br><small>{{ $registrationRequest->name }}@if($registrationRequest->user) · <a href="{{ route('adminpanel.users.user_view', $registrationRequest->user_id) }}">#{{ $registrationRequest->user_id }}</a>@endif</small></td><td>{{ $registrationRequest->email }}</td><td>{{ $registrationRequest->phone ?: '-' }}</td><td><span class="v6-status v6-status--{{ $registrationRequest->status === 'approved' ? 'success' : ($registrationRequest->status === 'pending' ? 'warning' : 'danger') }}">{{ $registrationRequest->status }}</span></td><td>{{ optional($registrationRequest->created_at)->format('Y. m. d. H:i') }}</td><td><form method="POST" action="{{ route('adminpanel.partner-requests.update', $registrationRequest) }}" class="v6-actions">@csrf<select name="status" aria-label="Új kérelemállapot">@foreach(\App\Models\PartnerRegistrationRequest::STATUSES as $status)<option value="{{ $status }}" @selected($registrationRequest->status === $status)>{{ $status }}</option>@endforeach</select><input name="review_note" value="{{ $registrationRequest->review_note }}" maxlength="2000" placeholder="Indoklás / megjegyzés"><button class="v6-button v6-button--small v6-button--primary" type="submit">Mentés</button></form></td></tr>
    @empty
        <tr><td colspan="6"><div class="v6-empty"><strong>Nincs partnerkérelem</strong><span>A kiválasztott állapotban nincs feldolgozandó kérelem.</span></div></td></tr>
    @endforelse
    </tbody></table></div>
    <div class="v6-actions" style="justify-content:center;margin-top:18px">{{ $requests->withQueryString()->links() }}</div>
</div>
@endsection
