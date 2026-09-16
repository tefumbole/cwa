@extends('beyond.layout')

@section('title', __('cwa.gallery.title'))
@section('meta_description', __('cwa.gallery.subtitle'))

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-6">{{ __('cwa.gallery.title') }}</h1>
        @if ($items->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <i data-lucide="image" class="w-16 h-16 mx-auto mb-4 text-gray-300"></i>
                <p class="text-lg">{{ __('cwa.gallery.empty') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($items as $item)
                    @include('beyond.partials.gallery_item', ['item' => $item])
                @endforeach
            </div>
        @endif
    </div>
</div>

<div id="gallery-lightbox" class="fixed inset-0 z-50 hidden bg-black/90 items-center justify-center p-4" onclick="closeGalleryLightbox(event)">
    <button type="button" class="absolute top-4 right-4 text-white text-3xl leading-none" onclick="closeGalleryLightbox(event)">&times;</button>
    <img id="gallery-lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
</div>

@endsection

@push('scripts')
<script async src="https://www.tiktok.com/embed.js"></script>
<script async src="//www.instagram.com/embed.js"></script>
<script>
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
