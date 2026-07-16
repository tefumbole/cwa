@extends('beyond.layout')

@section('title', 'Our Services')
@section('meta_description', "CWA apostolate: spiritual growth, evangelization, charity, and community service.")

@section('content')

@include('beyond.partials.hero', [
    'title' => \App\Support\SiteContent::html('services.hero_title', 'Our <span class="text-brand-gold">Apostolate</span>'),
    'subtitle' => \App\Support\SiteContent::text('services.hero_subtitle', 'Spiritual growth, evangelization, charity, and community service'),
])

<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold text-brand-blue">{{ \App\Support\SiteContent::text('services.heading', 'How We Serve') }}</h2>
            <p class="text-xl text-gray-600 mt-4">{{ \App\Support\SiteContent::text('services.subheading', 'Translating Christian values into action through prayer, mercy, and social development.') }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @foreach ($services as $service)
                <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col">
                    <div class="text-6xl mb-4 flex-shrink-0">{{ $service['emoji'] }}</div>
                    <h3 class="text-2xl font-bold text-brand-blue mb-3">{{ $service['title'] }}</h3>
                    <p class="text-gray-700 text-sm flex-grow mb-4">{{ $service['description'] }}</p>
                    <a href="https://wa.me/237683155315?text={{ urlencode("Hello Catholic Women's Association, I would like to learn more about " . $service['title']) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 bg-brand-blue hover:bg-brand-dark text-white px-6 py-3 rounded-md text-sm font-semibold self-start">
                        <i data-lucide="message-circle" class="w-4 h-4"></i> Contact Us
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-gradient-to-r from-brand-blue to-brand-light">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-bold text-white mb-6">Ready to Get Started?</h2>
        <p class="text-xl text-gray-200 mb-8">Contact us to walk with CWA in faith, charity, and community service.</p>
        <a href="https://wa.me/237683155315" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 bg-brand-gold text-brand-blue px-8 py-4 text-lg font-bold rounded-lg shadow-xl hover:shadow-2xl">
            <i data-lucide="message-circle" class="w-5 h-5"></i> Chat on WhatsApp
        </a>
    </div>
</section>

@endsection
