@extends('layouts.v6.admin')
@section('title', 'V6 komponensek')
@section('heading', 'V6 frontend alapok')
@section('intro', 'Védett staging komponensgaléria az új webshop, partnerfiók és admin layoutokhoz.')
@section('content')
<section class="v6-panel">
    <div class="v6-panel__header">
        <div>
            <h2>Három izolált layout</h2>
            <p>A régi képernyők változatlanul működnek; az új váz képernyőnként vezethető be.</p>
        </div>
        <span class="v6-status v6-status--neutral">M05 preview</span>
    </div>
    <div class="v6-showcase-grid">
        <a class="v6-showcase-link" href="{{ route('adminpanel.design-system.shop') }}">
            <strong>Webshop layout</strong>
            <span>Ipari hero, kategóriák, termékkártyák és kereshető felületi alapok.</span>
            <span>Megnyitás →</span>
        </a>
        <a class="v6-showcase-link" href="{{ route('adminpanel.design-system.account') }}">
            <strong>Partnerfiók layout</strong>
            <span>Oldalsó fióknavigáció, státuszok, rendelési lista és adatállapotok.</span>
            <span>Megnyitás →</span>
        </a>
        <a class="v6-showcase-link" href="{{ route('adminpanel.design-system.admin') }}">
            <strong>Admin layout</strong>
            <span>Grafit oldalsáv, világos munkaterület, mutatók és adattábla.</span>
            <span>Megnyitás →</span>
        </a>
    </div>
</section>

<section class="v6-section v6-section--white" style="margin-top: 18px; padding: 24px; border: 1px solid var(--eb-line); border-radius: var(--eb-radius);">
    <div class="v6-section-heading">
        <div><h2>Alapkomponensek</h2><p>Egységes állapotok, fókusz és reszponzív töréspontok.</p></div>
    </div>
    <div class="v6-preview-strip"><span class="v6-status v6-status--success">Kész</span><span class="v6-status v6-status--warning">Beszerzés alatt</span><span class="v6-status v6-status--danger">Hiba</span><span class="v6-status v6-status--neutral">Nincs adat</span></div>
    <div class="v6-form-grid">
        <div class="v6-field"><label for="preview-search">Keresőmező</label><input id="preview-search" type="search" placeholder="Cikkszám vagy terméknév"></div>
        <div class="v6-field"><label for="preview-state">Állapot</label><select id="preview-state"><option>Összes állapot</option><option>Készleten</option><option>Beszerzés alatt</option></select></div>
        <div class="v6-field v6-field--full"><label for="preview-note">Megjegyzés</label><textarea id="preview-note" placeholder="Hosszabb tartalom és hibás állapot is törés nélkül jelenik meg."></textarea></div>
    </div>
</section>
@endsection
