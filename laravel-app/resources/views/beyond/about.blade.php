@extends('beyond.layout')

@section('title', "About the Catholic Women's Association | Mission & Vision")
@section('meta_description', "Catholic Women's Association of Cameroon — founded 1964. Motto: To Serve and Not to Be Served (Matthew 20:28). Our mission, vision, and purpose.")

@section('content')

<section class="relative py-20 bg-brand-blue text-white overflow-hidden">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?q=80&w=2073&auto=format&fit=crop');"></div>
    </div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">{{ \App\Support\SiteContent::text('about.hero_title', "Catholic Women\\'s Association of Cameroon") }}</h1>
        <p class="text-xl md:text-2xl text-gray-200 max-w-3xl mx-auto font-light">
            {{ \App\Support\SiteContent::text('about.hero_subtitle', 'A lay association of Catholic women founded in Cameroon in 1964. Motto: “To Serve and Not to Be Served” (Matthew 20:28).') }}
        </p>
    </div>
</section>

{{-- Who we are --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-brand-blue mb-6">Who We Are</h2>
        <p class="text-lg text-gray-700 leading-relaxed mb-4">
            The Catholic Women Association (CWA) of Cameroon is a lay association of Catholic women founded in Cameroon in 1964. Its motto is:
        </p>
        <p class="text-xl md:text-2xl font-semibold text-brand-blue text-center my-8 px-4">
            “To Serve and Not to Be Served” <span class="text-brand-gold">(Matthew 20:28)</span>
        </p>
        <p class="text-lg text-gray-700 leading-relaxed">
            These statements reflect the commonly stated objectives and purpose of the CWA across Cameroon and in Cameroonian Catholic communities abroad.
        </p>
    </div>
</section>

{{-- Mission --}}
<section class="py-16 bg-brand-sky/15">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-brand-blue mb-4">{{ \App\Support\SiteContent::text('about.mission_heading', 'Our Mission') }}</h2>
        <p class="text-lg text-gray-600 mb-8">The mission of the Catholic Women Association is:</p>
        <ul class="space-y-4 text-lg text-gray-700">
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To promote the spiritual growth and renewal of Catholic women.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To deepen members' knowledge of God's Word and strengthen their Christian faith.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To translate Christian values into action through evangelization, charity, service, and social development.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To support families, the Church, and society through works of mercy and community service.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To uplift the spiritual, social, and economic well-being of women and families.</span></li>
        </ul>
    </div>
</section>

{{-- Vision --}}
<section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-brand-blue mb-4">Our Vision</h2>
        <p class="text-lg text-gray-600 mb-8">The vision of the Catholic Women Association is:</p>
        <ul class="space-y-4 text-lg text-gray-700">
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To build a community of holy, committed Catholic women who model their lives after the Blessed Virgin Mary.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To foster a society transformed by Christian values, love, service, and evangelization.</span></li>
            <li class="flex gap-3"><span class="text-brand-gold font-bold shrink-0">•</span><span>To develop women who contribute positively to the Church, family, and nation through faith-filled leadership and service.</span></li>
        </ul>
    </div>
</section>

{{-- Short version --}}
<section class="py-16 bg-brand-blue text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold mb-2 text-center">At a Glance</h2>
        <p class="text-center text-brand-gold mb-10 text-sm uppercase tracking-wide">For brochures, flyers, or constitution</p>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="bg-white/10 rounded-xl p-8 border border-white/10">
                <h3 class="text-xl font-bold text-brand-gold mb-4">Vision</h3>
                <p class="text-gray-100 leading-relaxed">
                    To be a vibrant community of Catholic women living holy lives, serving God, the Church, and society in the spirit of the Blessed Virgin Mary.
                </p>
            </div>
            <div class="bg-white/10 rounded-xl p-8 border border-white/10">
                <h3 class="text-xl font-bold text-brand-gold mb-4">Mission</h3>
                <p class="text-gray-100 leading-relaxed">
                    To promote the spiritual, social, and economic well-being of women and families through prayer, evangelization, service, charity, and Christian witness.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Founded --}}
<section class="py-12 bg-brand-sky/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-4xl font-bold text-brand-blue mb-2">1964</div>
                <div class="text-gray-600 font-medium">Founded in Cameroon</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-brand-blue mb-2">CWA</div>
                <div class="text-gray-600 font-medium">Catholic Women's Association</div>
            </div>
            <div>
                <div class="text-2xl md:text-3xl font-bold text-brand-blue mb-2">Serve</div>
                <div class="text-gray-600 font-medium">Not to Be Served</div>
            </div>
        </div>
    </div>
</section>

{{-- Core values --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-brand-blue mb-12">{{ \App\Support\SiteContent::text('about.values_heading', 'Our Core Values') }}</h2>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach ([
                ['heart', 'Faith', 'Deepening knowledge of God’s Word and strengthening Christian faith.'],
                ['hand-heart', 'Service', 'Evangelization, charity, works of mercy, and community service.'],
                ['sparkles', 'Holiness', 'Modelling our lives after the Blessed Virgin Mary in love and commitment.'],
            ] as [$icon, $title, $desc])
                <div class="p-6 bg-gray-50 rounded-xl hover:shadow-lg transition-shadow">
                    <i data-lucide="{{ $icon }}" class="w-12 h-12 text-brand-gold mx-auto mb-4"></i>
                    <h3 class="text-xl font-bold text-brand-blue mb-2">{{ $title }}</h3>
                    <p class="text-gray-600">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-gradient-to-r from-brand-blue to-brand-dark text-white text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-6">{{ \App\Support\SiteContent::text('about.cta_heading', 'Walk with us in faith and service') }}</h2>
        <p class="text-xl mb-8 opacity-90">{{ \App\Support\SiteContent::text('about.cta_text', "Contact the Catholic Women\\'s Association — we are here to serve.") }}</p>
        <a href="https://wa.me/237683155315" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 bg-brand-gold text-brand-blue font-bold text-lg px-8 py-4 rounded-full hover:bg-white hover:scale-105 transition-all">
            <i data-lucide="message-circle" class="w-5 h-5"></i> Chat on WhatsApp
        </a>
    </div>
</section>

@include('beyond.partials.contact_section')

@endsection
