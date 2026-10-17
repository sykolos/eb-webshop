@php($banner = $banners->active('hero'))
@if($banner)
<section class="m14-hero" aria-label="{{ $banner->alt_text }}">
    <picture>
        <source media="(max-width: 520px)" srcset="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->mobile_path) }}">
        <source media="(max-width: 1024px)" srcset="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->tablet_path) }}">
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->desktop_path) }}" alt="{{ $banner->alt_text }}">
    </picture>
    <div class="m14-hero__overlay"><div><span class="m14-eyebrow">Electro Business</span><h1>{{ $banner->title ?: 'Biztos kapcsolat. Erős alapok.' }}</h1>@if(($banner->link_url || $banner->offer) && $banner->link_label)<a class="m14-button" href="{{ $banner->link_url ?: route('info.offers').'#ajanlat-'.$banner->offer->slug }}">{{ $banner->link_label }}</a>@endif</div></div>
</section>
@endif
