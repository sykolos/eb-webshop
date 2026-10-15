@extends('layouts.v6.admin')

@section('title', 'Import ellenőrzése')
@section('heading', 'Import ellenőrzése')
@section('intro', 'A fájl még nem módosította az adatbázist. Az alkalmazás csak hibamentes, jóváhagyott előnézetből indulhat.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div><span class="v6-eyebrow">M13.05-M13.10</span><h2>{{ $import->original_name }}</h2><p>Munkalap: {{ $payload['sheet_name'] }} · mód: {{ $payload['mode'] === 'update' ? 'meglévő frissítése' : 'meglévő kihagyása' }}</p></div>
        <a class="v6-button v6-button--outline" href="{{ route('adminpanel.exports') }}">Vissza az exportokhoz</a>
    </div>

    <div class="v6-stat-grid">
        <div class="v6-stat"><strong>{{ $payload['summary']['total'] }}</strong><span>feldolgozott sor</span></div>
        <div class="v6-stat"><strong>{{ $payload['summary']['create'] }}</strong><span>új termék</span></div>
        <div class="v6-stat"><strong>{{ $payload['summary']['update'] }}</strong><span>frissítés</span></div>
        <div class="v6-stat"><strong class="{{ $payload['summary']['errors'] ? 'v6-danger-text' : '' }}">{{ $payload['summary']['errors'] }}</strong><span>hibás sor</span></div>
    </div>

    <form method="POST" action="{{ route('adminpanel.exports.import.remap', $import) }}" class="v6-form">
        @csrf
        <div class="v6-form-grid">
            <label>Munkalap<select name="sheet">@foreach($payload['source_sheets'] as $index => $sourceSheet)<option value="{{ $index }}" @selected((int) $payload['sheet_index'] === $index)>{{ $sourceSheet['name'] }}</option>@endforeach</select></label>
            <label>Meglévő cikkszám esetén<select name="mode"><option value="skip" @selected($payload['mode'] === 'skip')>Kihagyás</option><option value="update" @selected($payload['mode'] === 'update')>Frissítés</option></select></label>
        </div>
        <div class="v6-form-grid">
            @foreach($fields as $field)
                <label>{{ $field['label'] }} @if($field['required'])*@endif<select name="mapping[{{ $field['key'] }}]"><option value="">Nincs hozzárendelve</option>@foreach($payload['headers'] as $index => $header)<option value="{{ $index }}" @selected((string) ($payload['mapping'][$field['key']] ?? '') === (string) $index)>{{ $index + 1 }}. {{ is_array($header) ? 'Képlet/nem támogatott cella' : $header }}</option>@endforeach</select></label>
            @endforeach
        </div>
        <button class="v6-button v6-button--outline" type="submit">Oszloppárosítás újraellenőrzése</button>
    </form>

    @if($payload['summary']['errors'])
        <div class="v6-toast v6-toast--warning" role="alert">Hibás sor miatt az alkalmazás blokkolva van. <a href="{{ route('adminpanel.exports.import.errors', $import) }}">Hibajegyzék letöltése</a></div>
    @else
        <form method="POST" action="{{ route('adminpanel.exports.import.apply', $import) }}" class="v6-form">
            @csrf
            <p>Az alkalmazás tranzakcióban fut, a konkurens változásokat újraellenőrzi, és ismételt beküldésnél nem hajtja végre kétszer.</p>
            <button class="v6-button v6-button--primary" type="submit">Hibamentes import alkalmazása</button>
        </form>
    @endif

    <div class="v6-table-wrap">
        <table class="v6-table">
            <thead><tr><th>Excel-sor</th><th>Cikkszám</th><th>Termék</th><th>Művelet</th><th>Eredmény</th></tr></thead>
            <tbody>
            @foreach(array_slice($payload['rows'], 0, 100) as $row)
                <tr><td>{{ $row['line'] }}</td><td>{{ $row['record']['serial_number'] }}</td><td>{{ $row['record']['title'] }}</td><td>{{ $row['action'] }}</td><td class="{{ $row['errors'] ? 'v6-danger-text' : '' }}">{{ $row['errors'] ? implode(' · ', $row['errors']) : 'Ellenőrizve' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
