@extends('layouts.admin')
@section('title', 'Partnerkérelmek')
@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title mb-0">Partnerkérelmek</h1>
        <form method="get" class="d-flex gap-2">
            <select name="status" class="form-select" aria-label="Kérelem státusza">
                <option value="">Minden állapot</option>
                @foreach(\App\Models\PartnerRegistrationRequest::STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-dark" type="submit">Szűrés</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif

    <div class="table-responsive bg-white border rounded">
        <table class="table align-middle mb-0">
            <thead><tr><th>Név</th><th>Cég</th><th>Email</th><th>Állapot</th><th>Beküldve</th><th>Művelet</th></tr></thead>
            <tbody>
            @forelse($requests as $registrationRequest)
                <tr>
                    <td>{{ $registrationRequest->name }}</td>
                    <td>{{ $registrationRequest->company_name }}</td>
                    <td>{{ $registrationRequest->email }}</td>
                    <td><span class="badge text-bg-secondary">{{ $registrationRequest->status }}</span></td>
                    <td>{{ optional($registrationRequest->created_at)->format('Y. m. d. H:i') }}</td>
                    <td>
                        <form method="post" action="{{ route('adminpanel.partner-requests.update', $registrationRequest) }}" class="d-flex gap-2">
                            @csrf
                            <select name="status" class="form-select form-select-sm" aria-label="Új állapot">
                                @foreach(\App\Models\PartnerRegistrationRequest::STATUSES as $status)
                                    <option value="{{ $status }}" @selected($registrationRequest->status === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="review_note" class="form-control form-control-sm" placeholder="Megjegyzés" value="{{ $registrationRequest->review_note }}">
                            <button class="btn btn-sm btn-primary" type="submit">Mentés</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4">Nincs partnerkérelem.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
</div>
@endsection
