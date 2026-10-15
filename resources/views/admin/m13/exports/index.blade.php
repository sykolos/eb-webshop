@extends('layouts.v6.admin')

@section('title', 'Exportkatalógus')
@section('heading', 'Exportkatalógus')
@section('intro', 'Teljes, szerveroldali szűrésből készülő XLSX/CSV exportok és ellenőrzött termékimport egy admin nézetben.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div>
            <span class="v6-eyebrow">M13</span>
            <h2>Exportálás és import</h2>
            <p>{{ $datasets->count() }} adatkör nyilvántartva. Az export a teljes szűrt szerveroldali eredményt használja, nem csak az aktuális lapot.</p>
        </div>
        <a class="v6-button v6-button--outline" href="{{ route('adminpanel.exports.template') }}">Import sablon letöltése</a>
    </div>

    @if(session('success'))<div class="v6-toast" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="v6-form-error" role="alert">{{ $errors->first() }}</div>@endif

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M13.02-M13.04</span><h3>Új export indítása</h3><p>A nagyobb export háttérfeladatként készül. A fájl két órán át privát, lejáró linken érhető el.</p></div></div>
        <form method="POST" action="{{ route('adminpanel.exports.store') }}" class="v6-form">
            @csrf
            <div class="v6-form-grid">
                <label>Adatkör<select name="dataset" required>@foreach($datasets as $dataset)<option value="{{ $dataset['key'] }}">{{ $dataset['label'] }}</option>@endforeach</select></label>
                <label>Formátum<select name="format" required><option value="xlsx">XLSX</option><option value="csv">CSV, pontosvesszős UTF-8 BOM</option></select></label>
                <label>Keresés<input name="search" value="{{ request('search') }}" placeholder="Név, cikkszám, email vagy rendelés"></label>
                <label>Státusz<input name="status" value="{{ request('status') }}" placeholder="pl. függőben vagy active"></label>
                <label>Kategória<select name="category_id"><option value="">Minden kategória</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ str_repeat('— ', $category->tree_level) }}{{ $category->name }}</option>@endforeach</select></label>
                <label>Publikálás<select name="published"><option value="">Mindegy</option><option value="1">Publikus</option><option value="0">Rejtett</option></select></label>
                <label>Dátumtól<input type="date" name="from_date"></label>
                <label>Dátumig<input type="date" name="to_date"></label>
            </div>
            <div class="v6-actions"><label class="v6-check"><input type="checkbox" name="include_archived" value="1"> Archiváltak is</label><label class="v6-check"><input type="checkbox" name="include_deleted" value="1"> Törölt rendelések is</label><button class="v6-button v6-button--primary" type="submit">Export indítása</button></div>
        </form>
    </div>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">M13.05-M13.11</span><h3>Termékimport</h3><p>Legfeljebb 5 MB-os XLSX, maximum 5000 adatsor és 50 oszlop. Az előnézet nem módosít adatot.</p></div></div>
        <form method="POST" action="{{ route('adminpanel.exports.import.preview.store') }}" enctype="multipart/form-data" class="v6-form">
            @csrf
            <label>Termékimport XLSX<input type="file" name="file" accept=".xlsx" required></label>
            <label>Meglévő cikkszám esetén<select name="mode"><option value="skip">Kihagyás, csak új termékek</option><option value="update">Meglévő termék frissítése</option></select></label>
            <div class="v6-actions"><button class="v6-button" type="submit">Fájl előnézete és ellenőrzése</button><span class="v6-form-help">A régi .xls formátumot előbb .xlsx formátumba kell menteni.</span></div>
        </form>
    </div>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">Futások</span><h3>Legutóbbi exportok</h3></div></div>
        <div class="v6-table-wrap">
            <table class="v6-table">
                <thead><tr><th>Adatkör</th><th>Formátum</th><th>Állapot</th><th>Sorok</th><th>Indítva</th><th>Művelet</th></tr></thead>
        <tbody>@forelse($exports as $export)<tr><td>{{ data_get(collect($datasets)->firstWhere('key', $export->dataset), 'label', $export->dataset) }}</td><td>{{ strtoupper($export->format) }}</td><td><span class="v6-status {{ $export->status === 'completed' ? 'v6-status--success' : ($export->status === 'failed' ? 'v6-status--danger' : 'v6-status--warning') }}">{{ ['queued' => 'Sorban', 'processing' => 'Feldolgozás', 'completed' => 'Kész', 'failed' => 'Hiba', 'expired' => 'Lejárt'][$export->status] ?? $export->status }}</span>@if($export->error)<small>{{ $export->error }}</small>@endif</td><td>{{ $export->rows_count }}</td><td>{{ $export->created_at?->format('Y-m-d H:i') }}</td><td>@if($export->isDownloadable())<a class="v6-button v6-button--small v6-button--outline" href="{{ route('adminpanel.exports.download', $export) }}">Letöltés</a>@else<span>Várakozik</span>@endif</td></tr>@empty<tr><td colspan="6"><div class="v6-empty"><strong>Még nincs export</strong><span>Az első export indítása után itt jelenik meg az állapota.</span></div></td></tr>@endforelse</tbody>
            </table>
        </div>
    </div>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">Nyilvántartás</span><h3>Exportálható üzleti táblák</h3></div></div>
    <div class="v6-table-wrap">
        <table class="v6-table">
            <thead>
                <tr>
                    <th scope="col">Adatkör</th>
                    <th scope="col">Forrás</th>
                    <th scope="col">Export hatóköre</th>
                    <th scope="col">Állapot</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datasets as $dataset)
                    <tr>
                        <td>
                            <strong>{{ $dataset['label'] }}</strong>
                            <small>{{ $dataset['description'] }}</small>
                        </td>
                        <td><code>{{ $dataset['source'] }}</code></td>
                        <td>{{ $dataset['scope'] }}</td>
                        <td><span class="v6-status v6-status--success">Elérhető</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection
