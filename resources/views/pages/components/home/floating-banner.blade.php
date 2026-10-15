@php($banner = $banners->active('floating'))
@if($banner)
<aside class="m14-floating-banner" aria-label="{{ $banner->alt_text }}"><a href="{{ $banner->link_url ?: '#' }}"><picture><source media="(max-width: 520px)" srcset="{{ asset('storage/'.$banner->mobile_path) }}"><img src="{{ asset('storage/'.$banner->desktop_path) }}" alt="{{ $banner->alt_text }}"></picture>@if($banner->title)<strong>{{ $banner->title }}</strong>@endif</a></aside>
@endif
