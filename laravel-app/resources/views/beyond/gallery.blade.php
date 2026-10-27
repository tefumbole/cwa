@extends('beyond.layout')

@section('title', __('cwa.gallery.title'))
@section('meta_description', __('cwa.gallery.subtitle'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-[1.7rem] leading-tight sm:text-2xl font-extrabold text-brand-blue mb-6">{{ __('cwa.gallery.title') }}</h1>
        @php
            $groups = [];
            foreach ($items as $item) {
                $key = $item->eventKey();
                if (! isset($groups[$key])) {
                    $groups[$key] = ['label' => $item->eventHeading(), 'items' => []];
                }
                $groups[$key]['items'][] = $item;
            }
        @endphp
        @if (count($groups) > 1)
            <div class="flex flex-wrap gap-2 mb-6" id="gallery-filters">
                <button type="button" class="gallery-filter is-on" data-gallery-filter="all">All</button>
                @foreach ($groups as $key => $group)
                    <button type="button" class="gallery-filter" data-gallery-filter="{{ $key }}">{{ $group['label'] }}</button>
                @endforeach
            </div>
        @endif
        <style>
            .gallery-filter { border: 1.5px solid #e7e1d4; background: #fff; color: #003D82; border-radius: 999px; padding: .4rem .9rem; font-weight: 800; cursor: pointer; }
            .gallery-filter.is-on { background: #003D82; color: #fff; border-color: #003D82; }
            .gallery-event-title { font-family: Fraunces, Georgia, serif; color: #003D82; font-size: 1.6rem; line-height: 1.25; margin: 0 0 1rem; padding-bottom: .55rem; border-bottom: 3px solid #D4AF37; }
        </style>
        @if ($items->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <i data-lucide="image" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p class="text-lg">{{ __('cwa.gallery.empty') }}</p>
            </div>
        @else
            @foreach ($groups as $key => $group)
                <section class="gallery-section mb-12" data-event="{{ $key }}">
                    <h2 class="gallery-event-title">{{ $group['label'] }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-8">
                        @foreach ($group['items'] as $item)
                            @include('beyond.partials.gallery_item', ['item' => $item])
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif
    </div>
</div>

<div id="gallery-lightbox" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" onclick="closeGalleryLightbox(event)">
    <button type="button" class="absolute top-4 right-4 w-11 h-11 text-white text-3xl leading-none" onclick="closeGalleryLightbox(event)" aria-label="Close">&times;</button>
    <img id="gallery-lightbox-img" src="" alt="" class="max-w-full max-h-[80dvh] rounded-lg shadow-2xl">
</div>

@endsection

@push('scripts')
<script async src="https://www.tiktok.com/embed.js"></script>
<script async src="//www.instagram.com/embed.js"></script>
<script>
document.querySelectorAll('.gallery-filter').forEach(function (button) {
    button.addEventListener('click', function () {
        var key = button.getAttribute('data-gallery-filter');
        document.querySelectorAll('.gallery-filter').forEach(function (other) {
            other.classList.toggle('is-on', other === button);
        });
        document.querySelectorAll('.gallery-section').forEach(function (section) {
            section.style.display = key === 'all' || section.getAttribute('data-event') === key ? '' : 'none';
        });
    });
});
document.querySelectorAll('.gallery-lightbox-trigger').forEach(function (img) {
    img.addEventListener('click', function () {
        var lb = document.getElementById('gallery-lightbox');
        var full = document.getElementById('gallery-lightbox-img');
        full.src = img.getAttribute('data-full');
        full.alt = img.alt;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
    });
});
function closeGalleryLightbox(e) {
    if (e.target.id === 'gallery-lightbox' || e.target.tagName === 'BUTTON') {
        var lb = document.getElementById('gallery-lightbox');
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        document.getElementById('gallery-lightbox-img').src = '';
    }
}
</script>
@endpush
