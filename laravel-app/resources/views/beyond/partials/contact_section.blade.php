@php
    $waPhone = preg_replace('/\D+/', '', \App\Support\SiteContent::text('contact.phone', ''));
    $website = \App\Support\SiteContent::text('contact.website', 'www.cwacam.org');
    $websiteUrl = preg_match('#^https?://#', $website) ? $website : 'https://'.ltrim($website, '/');
    $email = \App\Support\SiteContent::text('contact.email', 'info@cwacam.org');
@endphp

<section id="contact" class="py-10 scroll-mt-24">
    <div class="max-w-3xl mx-auto px-4">

        <h2 class="text-2xl font-extrabold text-brand-blue mb-6">{{ \App\Support\SiteContent::text('contact.heading', __('cwa.contact.heading')) }}</h2>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-xl border-t-4 border-t-brand-blue shadow-md hover:shadow-lg transition-all p-6">
                    <h3 class="text-xl font-bold text-brand-blue mb-4 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5"></i> {{ __('cwa.contact.office') }}
                    </h3>
                    <div class="space-y-3 text-gray-600">
                        <p class="font-semibold text-gray-800">{{ \App\Support\SiteContent::text('contact.office_name', __('cwa.contact.office_name')) }}</p>
                        <p>{{ \App\Support\SiteContent::text('contact.office_line1', __('cwa.contact.office_line1')) }}</p>
                        <p>{{ \App\Support\SiteContent::text('contact.office_line2', __('cwa.contact.office_line2')) }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-xl border-t-4 border-t-brand-gold shadow-md hover:shadow-lg transition-all p-6">
                    <h3 class="text-xl font-bold text-brand-blue mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-gold"></i> {{ __('cwa.contact.contact') }}
                    </h3>
                    <div class="space-y-4 text-gray-600">
                        <div>
                            <p class="font-bold text-gray-800">{{ \App\Support\SiteContent::text('contact.person_name', __('cwa.contact.person_name')) }}</p>
                            <p class="text-sm text-gray-500">{{ \App\Support\SiteContent::text('contact.person_role', __('cwa.contact.person_role')) }}</p>
                        </div>
                        @if($waPhone)
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-blue-100 p-2 rounded-full text-brand-blue"><i data-lucide="phone" class="w-4 h-4"></i></div>
                            <div>
                                <p class="font-medium">{{ \App\Support\SiteContent::text('contact.phone', '') }}</p>
                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener" class="text-brand-gold hover:text-brand-blue text-xs font-semibold inline-flex items-center gap-1">
                                    <i data-lucide="message-circle" class="w-3 h-3"></i> Chat on WhatsApp
                                </a>
                            </div>
                        </div>
                        @endif
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-yellow-100 p-2 rounded-full text-brand-gold"><i data-lucide="mail" class="w-4 h-4"></i></div>
                            <a href="mailto:{{ $email }}" class="font-medium hover:text-brand-blue">{{ $email }}</a>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <div class="bg-gray-200 p-2 rounded-full text-gray-700"><i data-lucide="globe" class="w-4 h-4"></i></div>
                            <a href="{{ $websiteUrl }}" class="font-medium hover:text-brand-blue">{{ $website }}</a>
                        </div>
                    </div>
                </div>

                <div class="bg-brand-blue text-white shadow-md rounded-xl overflow-hidden relative p-6">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/10 rounded-full blur-xl"></div>
                    <div class="relative z-10 flex items-start gap-4">
                        <div class="bg-white/20 p-3 rounded-full shrink-0"><i data-lucide="clock" class="w-6 h-6"></i></div>
                        <div>
                            <h3 class="font-bold text-lg mb-2 text-brand-gold">{{ __('cwa.contact.hours') }}</h3>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between"><span class="text-blue-100">{{ __('cwa.contact.weekdays') }}</span><span class="font-medium">{{ \App\Support\SiteContent::text('contact.hours_weekday', '9:00 AM - 6:00 PM') }}</span></div>
                                <div class="flex justify-between"><span class="text-blue-100">{{ __('cwa.contact.weekend') }}</span><span class="font-medium opacity-80">{{ \App\Support\SiteContent::text('contact.hours_weekend', __('cwa.contact.hours_weekend_closed')) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="shadow-xl border-0 h-full rounded-xl overflow-hidden bg-white">
                    <div class="bg-gradient-to-r from-brand-dark to-brand-blue p-6 text-white">
                        <h3 class="text-2xl font-bold flex items-center gap-2">
                            <i data-lucide="send" class="w-6 h-6 text-brand-gold"></i> {{ __('cwa.contact.send') }}
                        </h3>
                        <p class="text-blue-100 mt-1">{{ __('cwa.contact.send_hint') }}</p>
                    </div>
                    <div class="p-8 md:p-10">
                        <form id="contact-form" class="space-y-6" onsubmit="return submitContact(event)">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.contact.full_name') }} <span class="text-red-500">*</span></label>
                                    <input required name="name" type="text" placeholder="{{ __('cwa.contact.name_ph') }}"
                                           class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.contact.email') }} <span class="text-red-500">*</span></label>
                                    <input required name="email" type="email" placeholder="{{ __('cwa.contact.email_ph') }}"
                                           class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.contact.subject') }} <span class="text-red-500">*</span></label>
                                <input required name="subject" type="text" placeholder="{{ __('cwa.contact.subject_ph') }}"
                                       class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.contact.message') }} <span class="text-red-500">*</span></label>
                                <textarea required name="message" rows="6" placeholder="{{ __('cwa.contact.message_ph') }}"
                                          class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-2 resize-none focus:bg-white focus:border-brand-blue focus:ring-2 focus:ring-brand-blue/20 outline-none"></textarea>
                            </div>
                            <div id="contact-success" class="hidden rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm"></div>
                            <div class="pt-4 flex flex-col sm:flex-row gap-4">
                                <button type="submit"
                                        class="w-full sm:w-auto px-8 bg-brand-blue hover:bg-[#002a5a] text-white font-bold h-12 text-lg rounded-md shadow-md inline-flex items-center justify-center gap-2">
                                    <i data-lucide="send" class="w-5 h-5"></i> {{ __('cwa.contact.send_btn') }}
                                </button>
                                @if($waPhone)
                                <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode(__('cwa.contact.wa_hello')) }}"
                                   target="_blank" rel="noopener"
                                   class="w-full sm:w-auto px-8 border-2 border-[#25D366] text-[#25D366] hover:bg-[#25D366] hover:text-white font-bold h-12 text-lg rounded-md inline-flex items-center justify-center gap-2 transition-colors">
                                    <i data-lucide="message-circle" class="w-5 h-5"></i> {{ __('cwa.contact.open_wa') }}
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
function submitContact(e) {
    e.preventDefault();
    const f = e.target;
    const name = f.name.value.trim();
    const email = f.email.value.trim();
    const subject = f.subject.value.trim();
    const message = f.message.value.trim();
    const text = `*{{ __('cwa.contact.wa_title') }}*\n\n*{{ __('cwa.contact.full_name') }}:* ${name}\n*{{ __('cwa.contact.email') }}:* ${email}\n*{{ __('cwa.contact.subject') }}:* ${subject}\n\n*{{ __('cwa.contact.message') }}:*\n${message}`;
    @if($waPhone)
    window.open('https://wa.me/{{ $waPhone }}?text=' + encodeURIComponent(text), '_blank');
    @else
    window.location.href = 'mailto:{{ $email }}?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(text.replace(/\*/g, ''));
    @endif
    const el = document.getElementById('contact-success');
    el.textContent = @json(__('cwa.contact.success'));
    el.classList.remove('hidden');
    f.reset();
    return false;
}
</script>
@endpush
