@extends('layouts.v6.admin')

@section('title', 'Exportkatalógus')
@section('heading', 'Exportkatalógus')
@section('intro', 'A későbbi XLSX és CSV exportok központi, jogosultságkezelt adatkör-nyilvántartása.')

@section('content')
<div class="v6-section">
    <div class="v6-section__header">
        <div>
            <span class="v6-eyebrow">M13.01</span>
            <h2>Exportálható üzleti táblák</h2>
            <p>{{ $datasets->count() }} adatkör nyilvántartva. A letöltési műveletek a következő M13 pontokban készülnek el.</p>
        </div>
        <span class="v6-status">Nyilvántartás</span>
    </div>

    <div class="v6-notice" role="status">
        Ez a nézet még nem indít fájlletöltést. Az adatkörök és források rögzítése után külön készül el a teljes szűrt eredményhalmaz XLSX/CSV exportja.
    </div>

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
                        <td><span class="v6-status">Előkészítve</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
