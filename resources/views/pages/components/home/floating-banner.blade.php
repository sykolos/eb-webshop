@php($banner = $banners->active('floating'))
@if($banner)
<aside class="m14-floating-banner" aria-label="{{ $banner->alt_text }}"><a href="{{ $banner->link_url ?: ($banner->offer ? route('info.offers').'#ajanlat-'.$banner->offer->slug : '#') }}"><picture><source media="(max-width: 520px)" srcset="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->mobile_path) }}"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->desktop_path) }}" alt="{{ $banner->alt_text }}"></picture>@if($banner->title)<strong>{{ $banner->title }}</strong>@endif</a></aside>
@endif
