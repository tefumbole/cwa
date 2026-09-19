@extends('beyond.layout')

@section('title', __('cwa.membership.register_title'))
@section('meta_description', __('cwa.membership.register_meta'))

@section('content')
@include('beyond.apply.partials.apply_styles')
<div class="max-w-2xl mx-auto px-4 py-8">
    <p class="text-brand-gold text-xs font-bold uppercase tracking-widest mb-1">{{ __('cwa.join.kicker') }}</p>
    <h1 class="text-2xl font-extrabold text-brand-blue mb-2">{{ __('cwa.membership.register_title') }}</h1>
    <p class="text-slate-600 text-sm mb-6">{{ __('cwa.membership.register_meta') }}</p>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
            <ul class="list-disc pl-5 space-y-1 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('beyond.membership.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-xl border border-gray-100 p-5 sm:p-8 space-y-6" id="membership-registration-form"
          x-data="membershipForm()">
        @csrf

        <div>
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.phone') }} <span class="text-red-500">*</span></label>
            <div class="mt-1 flex rounded-md border border-gray-200 overflow-hidden focus-within:border-brand-blue">
                <span class="inline-flex items-center px-3 bg-slate-50 text-slate-600 text-sm font-bold border-r border-gray-200">+237</span>
                <input required name="phone" x-model="phone" value="{{ old('phone') }}" @input="normalizePhone(); scheduleLookup()" type="tel"
                       inputmode="numeric" maxlength="13" autocomplete="tel-national"
                       class="w-full px-3 py-2 outline-none" placeholder="6XX XXX XXX">
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ __('cwa.membership.phone_hint') }}</p>
            <div class="mt-2 min-h-[1.5rem] text-sm">
                <p x-show="looking" x-cloak class="text-slate-500">{{ __('cwa.membership.checking') }}</p>
                <div x-show="!looking && (operator || donorName)" x-cloak class="flex flex-wrap items-center gap-2">
                    <span x-show="operator === 'mtn'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ffcc00] text-[#1a1a1a]">{{ __('cwa.donate.mtn') }}</span>
                    <span x-show="operator === 'orange'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ff6600] text-white">{{ __('cwa.donate.orange') }}</span>
                    <span x-show="donorName" class="font-semibold text-brand-blue" x-text="donorName"></span>
                </div>
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.name') }} <span class="text-red-500">*</span></label>
            <input required name="name" x-model="fullName" value="{{ old('name') }}"
                   class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2 focus:border-brand-blue outline-none">
            <p class="text-xs text-slate-500 mt-1">{{ __('cwa.membership.name_hint') }}</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.diocese') }} <span class="text-red-500">*</span></label>
                <input required name="diocese" value="{{ old('diocese') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.parish') }} <span class="text-red-500">*</span></label>
                <input required name="parish" value="{{ old('parish') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
            </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.email') }}</label>
                <input name="email" value="{{ old('email') }}" type="email" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.age') }}</label>
                <select name="age_range" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    <option value="">{{ __('cwa.join.age_skip') }}</option>
                    @foreach ($ageRanges as $key => $range)
                        <option value="{{ is_string($key) ? $key : $range }}" {{ (string) old('age_range') === (string) (is_string($key) ? $key : $range) ? 'selected' : '' }}>{{ $range }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="rounded-xl border-2 border-brand-gold/50 bg-[#003D82]/5 p-5 text-center">
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.selfie') }} <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mt-1 mb-4">{{ __('cwa.membership.selfie_hint') }}</p>
            <input type="file" name="selfie" id="membership-selfie-input" accept="image/*" class="sr-only" tabindex="-1" required>
            <button type="button" data-cwa-selfie-open class="cwa-selfie-preview" aria-label="{{ __('cwa.membership.snap_open') }}">
                <img id="membership-selfie-preview" alt="" class="hidden">
                <span id="membership-selfie-placeholder" class="cwa-selfie-placeholder">
                    <i data-lucide="camera" class="w-8 h-8 mb-2"></i>
                    {{ __('cwa.membership.snap_open') }}
                </span>
            </button>
            <button type="button" data-cwa-selfie-open class="mt-4 w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-brand-gold text-brand-blue font-extrabold rounded-full px-6 py-3 min-h-[3rem]">
                <i data-lucide="aperture" class="w-5 h-5"></i> {{ __('cwa.membership.snap_button') }}
            </button>
            <div class="mt-3">
                <label for="membership-selfie-input" class="text-sm text-brand-blue font-semibold underline cursor-pointer">
                    {{ __('cwa.membership.attach_if_have') }}
                </label>
            </div>
            <p class="text-xs text-emerald-700 mt-2 min-h-[1rem] mb-0" id="membership-selfie-status">{{ __('cwa.membership.no_file') }}</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
            <p class="text-sm font-bold text-gray-800 m-0">{{ __('cwa.membership.id_optional') }}</p>
            @foreach ([
                ['id_front', __('cwa.membership.id_front')],
                ['id_back', __('cwa.membership.id_back')],
            ] as $pair)
                <div class="apply-doc-card" data-apply-doc data-facing="environment" data-title="{{ $pair[1] }}">
                    <label class="text-sm font-semibold text-gray-700">{{ $pair[1] }}</label>
                    <input type="file" name="{{ $pair[0] }}" data-doc-target accept="image/*" class="sr-only" tabindex="-1">
                    <input type="file" data-doc-attach accept="image/*" class="hidden" id="attach-{{ $pair[0] }}">
                    <div class="apply-doc-actions">
                        <button type="button" data-doc-snap class="apply-doc-btn primary">
                            <i data-lucide="camera" class="w-4 h-4"></i> {{ __('cwa.membership.snap') }}
                        </button>
                        <label for="attach-{{ $pair[0] }}" class="apply-doc-btn">
                            <i data-lucide="paperclip" class="w-4 h-4"></i> {{ __('cwa.membership.attach') }}
                        </label>
                    </div>
                    <p class="text-xs text-emerald-700 mt-2 min-h-[1rem] mb-0" data-doc-status>{{ __('cwa.membership.no_file') }}</p>
                    <img data-doc-preview alt="" class="hidden mt-2 max-h-36 w-full rounded-lg border object-cover">
                </div>
            @endforeach
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 block mb-2">{{ __('cwa.membership.signature') }} <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-2">{{ __('cwa.membership.signature_hint') }}</p>
            <div class="border-2 border-dashed border-brand-blue rounded-xl bg-blue-50/50 p-3 max-w-lg">
                <canvas id="membership-signature-pad" width="500" height="140" class="w-full max-w-[500px] bg-white rounded-lg touch-none"></canvas>
            </div>
            <input type="hidden" name="signature" id="membership_signature_input" value="{{ old('signature') }}">
            <button type="button" id="clear-membership-signature" class="mt-2 text-sm text-gray-600 hover:text-brand-blue underline">Clear</button>
        </div>

        <button type="submit" class="w-full bg-brand-gold hover:bg-[#b5952f] text-brand-blue font-bold py-3.5 rounded-lg shadow-lg">
            {{ __('cwa.membership.submit') }}
        </button>
    </form>
</div>
@include('beyond.apply.partials.camera_capture')
@include('beyond.membership.partials.circle_selfie')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('membershipForm', function () {
        return {
            phone: @json(old('phone', '')),
            operator: '',
            donorName: '',
            fullName: @json(old('name', '')),
            looking: false,
            timer: null,
            last: '',
            holderUrl: @json(route('beyond.membership.holder')),
            normalizePhone: function () {
                this.phone = String(this.phone || '').replace(/\D/g, '').replace(/^237/, '').replace(/^0/, '').slice(0, 9);
            },
            resetLookup: function () {
                this.operator = '';
                this.donorName = '';
                this.looking = false;
                this.last = '';
            },
            scheduleLookup: function () {
                var self = this;
                clearTimeout(this.timer);
                this.timer = setTimeout(function () { self.lookupNow(); }, 450);
            },
            lookupNow: function () {
                var digits = String(this.phone || '').replace(/\D/g, '');
                if (digits.length < 9) {
                    this.resetLookup();
                    return;
                }
                if (digits === this.last) return;
                this.last = digits;
                this.looking = true;
                var self = this;
                fetch(this.holderUrl + '?phone=' + encodeURIComponent(digits), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (res) {
                    self.looking = false;
                    if (!res) return;
                    self.operator = res.operator || '';
                    self.donorName = res.name || '';
                    if (res.name && !self.fullName) {
                        self.fullName = res.name;
                    }
                })
                .catch(function () { self.looking = false; });
            }
        };
    });
});

(function () {
    var canvas = document.getElementById('membership-signature-pad');
    if (!canvas || typeof SignaturePad === 'undefined') return;
    var pad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 61, 130)'
    });
    var clearBtn = document.getElementById('clear-membership-signature');
    if (clearBtn) clearBtn.addEventListener('click', function () { pad.clear(); });
    var form = document.getElementById('membership-registration-form');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (pad.isEmpty()) {
                e.preventDefault();
                alert(@json(__('cwa.membership.sign_required')));
                return false;
            }
            document.getElementById('membership_signature_input').value = pad.toDataURL('image/png');
        });
    }
})();
</script>
@endpush
