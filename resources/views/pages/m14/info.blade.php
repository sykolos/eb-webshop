@extends('layouts.v6.shop')
@php
    $titles = ['szallitas' => 'Szállítás és fizetés', 'fizetes' => 'Fizetési információk', 'gyik' => 'Gyakori kérdések', 'katalogus' => 'Katalógus és dokumentumok', 'impresszum' => 'Impresszum', 'aszf' => 'Általános Szerződési Feltételek', 'adatkezeles' => 'Adatkezelési tájékoztató', 'elallas' => 'Elállás és ügyintézés'];
@endphp
@section('title', $titles[$slug] ?? 'Információ')
@section('content')
<div class="v6-container v6-container--narrow v6-m14-page"><div class="v6-section__header"><div><span class="v6-eyebrow">TUDNIVALÓK · {{ config('site.content_version') }}</span><h1>{{ $titles[$slug] ?? 'Információ' }}</h1><p>Az Electro Business beszerzési folyamatához kapcsolódó tájékoztató.</p></div></div>
@if($slug === 'gyik')
<div class="v6-faq">@foreach([['Hogyan láthatom a partnerárakat?','A partnerárak jóváhagyott, bejelentkezett partnerfiókhoz kötöttek.'],['Mikor teljesül a rendelés?','A pontos teljesítés a termék elérhetőségétől és az egyeztetett szállítási feltételektől függ.'],['Kérhetek segítséget egy termékhez?','Igen, a Kapcsolat oldalon küldött üzenetre a csapat válaszol.']] as $item)<details><summary>{{ $item[0] }}</summary><p>{{ $item[1] }}</p></details>@endforeach</div>
@elseif($slug === 'katalogus')
<section class="v6-panel"><h2>Offline termékkatalógus</h2>@php($catalogPath = public_path(config('site.catalog.path')))<p>A letölthető dokumentum csak ellenőrzött, helyben elérhető fájlként jelenik meg.</p>@if(is_file($catalogPath))<p>{{ config('site.catalog.name') }} · {{ config('site.catalog.language') }} · {{ number_format(filesize($catalogPath) / 1048576, 1, ',', ' ') }} MB</p><a class="v6-button v6-button--primary" href="{{ route('info.catalog.download') }}">PDF letöltése</a>@else<div class="v6-empty-state"><strong>A katalógus jelenleg nincs feltöltve.</strong><span>Amíg nincs ellenőrzött PDF a rendszerben, letöltési gomb nem jelenik meg.</span></div>@endif</section>
@elseif(in_array($slug, ['szallitas', 'fizetes'], true))
<div class="v6-m14-info-grid"><section class="v6-panel"><h2>Szállítás</h2><p>A szállítási mód és díj a rendelés, a termék elérhetősége és a partneri egyeztetés alapján kerül véglegesítésre.</p></section><section class="v6-panel"><h2>Fizetés</h2><p>A fizetési feltételek a jóváhagyott partneri megállapodás és a rendelés visszaigazolása szerint érvényesek.</p></section></div>
@elseif($slug === 'adatkezeles')
<article class="v6-panel v6-document"><div class="v6-notice">A jogilag véglegesített szöveg jóváhagyás után kerül kiadásra; az alábbi technikai működés már ehhez az élesítés előtti ellenőrzéshez tartozik.</div><h2>Weboldal-mérés</h2><p>A webshop csak a mérési hozzájárulás elfogadása után rögzít első fél aktivitási eseményeket, például oldalmegtekintést, keresést, termékmegtekintést és kosárfolyamatot. Elutasítás vagy visszavonás után ilyen esemény nem készül.</p><p>Az eseményekből email-cím, név, cím, jelszó, token vagy partnerár nem kerül tárolásra. Az események megőrzési ideje hat hónap, utána automatikus takarítás törli őket. A rendelési és számlázási adatok üzleti igazságforrása továbbra is a Laravel rendelési adatbázisa.</p><p>Külső mérőszolgáltató jelenleg nincs bekötve. A választott hozzájárulás bármikor módosítható a „Cookie beállítások” gombbal.</p></article>
@else
<article class="v6-panel v6-document"><div class="v6-notice">Ez az oldal a szerkezetet és a verziózási helyet biztosítja. A jogilag ellenőrzött végleges szöveget a jóváhagyás után kell ide beemelni.</div><p><strong>Dokumentum:</strong> {{ $titles[$slug] }}</p><p><strong>Verzió:</strong> {{ config('site.content_version') }}</p><p><strong>Hatálybalépés:</strong> Jóváhagyás után töltendő.</p><h2>Kapcsolat és ügyintézés</h2><p>Kérdés vagy dokumentum-egyeztetés esetén használd a kapcsolati űrlapot.</p><a class="v6-button v6-button--primary" href="{{ route('contact') }}">Kapcsolatfelvétel</a></article>
@endif
</div>
@endsection
