@extends('layouts.v6.admin')

@section('title', 'Hero és bannerek')
@section('heading', 'Hero és bannerek')
@section('intro', 'Eszközönkénti képek, publikálási időszak és visszavehető publikus állapot egy helyen.')

@section('content')
<div class="v6-section">
    @if($errors->any())<div class="v6-toast v6-toast--warning" role="alert">{{ $errors->first() }}</div>@endif
    <div class="v6-notice">A képek mentéskor rögzített méretre lesznek vágva: desktop 1600×500, tablet 1200×675, telefon 900×1125. A publikálás külön művelet, és mindhárom változatot kéri.</div>

    <form method="POST" action="{{ route('adminpanel.banners.store') }}" enctype="multipart/form-data" class="v6-form">
        @csrf
        <div class="v6-section__header"><div><span class="v6-eyebrow">M14.01–M14.03</span><h2>Új banner</h2><p>Aktuális akcióhoz vagy termékkategóriához kapcsolható. Ellenőrizetlen kedvezményt ne írj a képre.</p></div></div>
        <div class="v6-form-grid">
            <label>Hely<select name="slot" required><option value="hero">Hero</option><option value="floating">Úszó banner</option></select></label>
            <label>Sorrend<input type="number" name="sort_order" min="0" max="100000" value="0"></label>
            <label>Cím<input name="title" maxlength="191" value="{{ old('title') }}"></label>
            <label>Alt szöveg<input name="alt_text" required maxlength="191" value="{{ old('alt_text') }}"></label>
            <label>CTA szöveg<input name="link_label" maxlength="120" value="{{ old('link_label') }}" placeholder="Kategória megnyitása"></label>
            <label>CTA / kategória URL<input name="link_url" maxlength="2048" value="{{ old('link_url') }}" placeholder="/shop?category=..."></label>
            <label>Aktív ettől<input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"></label>
            <label>Aktív eddig<input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"></label>
            <label>Desktop kép<input type="file" name="desktop" accept="image/jpeg,image/png,image/webp" required></label>
            <label>Tablet kép<input type="file" name="tablet" accept="image/jpeg,image/png,image/webp" required></label>
            <label>Telefon kép<input type="file" name="mobile" accept="image/jpeg,image/png,image/webp" required></label>
        </div>
        <div class="v6-actions">
            <button class="v6-button v6-button--outline" type="submit" name="publish" value="0">Piszkozat mentése</button>
            <button class="v6-button v6-button--primary" type="submit" name="publish" value="1">Mentés és publikálás</button>
        </div>
    </form>

    <div class="v6-subsection">
        <div class="v6-section__header"><div><span class="v6-eyebrow">Publikációs lista</span><h2>Mentett bannerek</h2></div></div>
        <div class="v6-table-wrap"><table class="v6-table"><thead><tr><th>Előnézet</th><th>Hely és tartalom</th><th>Időszak</th><th>Állapot</th><th>Műveletek</th></tr></thead><tbody>
        @forelse($banners as $banner)
            <tr>
                <td>@if($banner->mobile_path)<img class="v6-media-thumb" src="{{ asset('storage/'.$banner->mobile_path) }}" alt="">@endif</td>
                <td><strong>{{ $banner->title ?: 'Névtelen banner' }}</strong><small>{{ strtoupper($banner->slot) }} · {{ $banner->alt_text }}</small></td>
                <td><small>{{ $banner->starts_at?->format('Y-m-d H:i') ?: 'Azonnal' }} – {{ $banner->ends_at?->format('Y-m-d H:i') ?: 'Visszavonásig' }}</small></td>
                <td><span class="v6-status {{ $banner->is_published ? 'v6-status--success' : 'v6-status--neutral' }}">{{ $banner->is_published ? 'Publikus' : 'Piszkozat' }}</span></td>
                <td><div class="v6-actions">
                    @if(!$banner->is_published)<form method="POST" action="{{ route('adminpanel.banners.publish', $banner) }}">@csrf<button class="v6-button v6-button--small v6-button--primary" type="submit">Publikálás</button></form>@else<form method="POST" action="{{ route('adminpanel.banners.archive', $banner) }}">@csrf<button class="v6-button v6-button--small v6-button--outline" type="submit">Visszavonás</button></form>@endif
                    <form method="POST" action="{{ route('adminpanel.banners.destroy', $banner) }}">@csrf @method('DELETE')<button class="v6-button v6-button--small v6-button--danger" type="submit">Törlés</button></form>
                </div></td>
            </tr>
        @empty
            <tr><td colspan="5"><div class="v6-empty"><strong>Még nincs banner</strong><span>Az első mentés után itt kezelheted az időzített hero- és úszóképeket.</span></div></td></tr>
        @endforelse
        </tbody></table></div>
        {{ $banners->links() }}
    </div>
</div>
@endsection
