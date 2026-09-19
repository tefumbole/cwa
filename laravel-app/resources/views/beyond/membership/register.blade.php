@extends('beyond.layout')

@section('title', __('cwa.membership.page_title'))
@section('meta_description', __('cwa.membership.page_title'))

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-6">{{ __('cwa.membership.page_title') }}</h1>

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
          class="bg-white rounded-2xl shadow-xl border border-gray-100 p-5 sm:p-8" id="membership-registration-form"
          x-data="membershipWizard()">
        @csrf
        <input type="hidden" name="id_stored_path" x-model="idPath">
        <input type="hidden" name="selfie_stored" x-model="selfiePath">
        <input type="hidden" name="signature" id="membership_signature_input" x-model="signature">
        <input type="hidden" name="id_type" :value="idType">
        <input type="file" name="id_front" id="id-file-input" accept="image/*" class="sr-only">
        <input type="file" name="selfie" id="membership-selfie-input" accept="image/*" class="sr-only">

        {{-- Phone --}}
        <div x-show="step === 'phone'" x-cloak>
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.phone') }} <span class="text-red-500">*</span></label>
            <div class="mt-1 flex rounded-md border border-gray-200 overflow-hidden">
                <select name="country_code" x-model="countryCode" @change="scheduleLookup()"
                        class="bg-slate-50 text-slate-700 text-sm font-bold border-r border-gray-200 px-2 max-w-[11rem]">
                    @foreach ($countryCodes as $code => $label)
                        <option value="{{ $code }}" @if($code === '+237') selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
                <input required name="phone" x-model="phone" @input="normalizePhone(); scheduleLookup()" type="tel"
                       inputmode="numeric" autocomplete="tel-national"
                       class="w-full px-3 py-2 outline-none" placeholder="6XX XXX XXX">
            </div>
            <p class="text-xs text-slate-500 mt-1">{{ __('cwa.membership.phone_hint') }}</p>
            <p x-show="looking" x-cloak class="text-sm text-slate-500 mt-2">{{ __('cwa.membership.checking') }}</p>
            <div x-show="!looking && (operator || donorName)" x-cloak class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                <span x-show="operator === 'mtn'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ffcc00]">{{ __('cwa.donate.mtn') }}</span>
                <span x-show="operator === 'orange'" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ff6600] text-white">{{ __('cwa.donate.orange') }}</span>
                <span class="font-semibold text-brand-blue" x-text="donorName"></span>
            </div>

            <label class="text-sm font-semibold text-gray-700 mt-5 block">{{ __('cwa.membership.name') }} <span class="text-red-500">*</span></label>
            <input required name="name" x-model="fullName" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
            <p class="text-xs text-slate-500 mt-1">{{ __('cwa.membership.name_hint') }}</p>

            <label class="text-sm font-semibold text-gray-700 mt-5 block">{{ __('cwa.membership.whatsapp_opt') }}</label>
            <label class="flex items-center gap-2 text-sm text-slate-600 mt-1">
                <input type="checkbox" x-model="waSame"> {{ __('cwa.membership.whatsapp_same') }}
            </label>
            <div class="mt-2 flex rounded-md border border-gray-200 overflow-hidden" x-show="!waSame" x-cloak>
                <select name="whatsapp_country" x-model="waCountry" class="bg-slate-50 text-sm font-bold px-2 border-r max-w-[11rem]">
                    @foreach ($countryCodes as $code => $label)
                        <option value="{{ $code }}" @if($code === '+237') selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="whatsapp_phone" x-model="waPhone" type="tel" inputmode="numeric" class="w-full px-3 py-2 outline-none">
            </div>

            <div class="grid sm:grid-cols-2 gap-3 mt-5">
                <div>
                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.diocese') }} <span class="text-red-500">*</span></label>
                    <input required name="diocese" x-model="diocese" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.parish') }} <span class="text-red-500">*</span></label>
                    <input required name="parish" x-model="parish" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                </div>
            </div>
            <button type="button" @click="goIdType()" class="mt-6 w-full bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.next') }}</button>
        </div>

        {{-- ID type --}}
        <div x-show="step === 'idtype'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-1">{{ __('cwa.membership.id_step_title') }}</h2>
            <p class="text-sm text-slate-500 mb-5">{{ __('cwa.membership.id_step_hint') }}</p>
            <p class="text-sm font-semibold text-gray-800 mb-3">{{ __('cwa.membership.id_which') }}</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="idType = 'national_id'"
                        :class="idType === 'national_id' ? 'bg-brand-gold text-brand-blue border-brand-gold' : 'bg-white'"
                        class="px-5 py-2.5 rounded-full border font-semibold">{{ __('cwa.membership.id_national') }}</button>
                <button type="button" @click="idType = 'passport'"
                        :class="idType === 'passport' ? 'bg-brand-gold text-brand-blue border-brand-gold' : 'bg-white'"
                        class="px-5 py-2.5 rounded-full border font-semibold">{{ __('cwa.membership.id_passport') }}</button>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" @click="step = 'phone'" class="flex-1 border rounded-full py-3 font-semibold">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="idType && (step = 'idhow')" class="flex-1 bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Upload or scan --}}
        <div x-show="step === 'idhow'" x-cloak>
            <p class="text-sm font-semibold text-gray-800 mb-3">{{ __('cwa.membership.id_how') }}</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="chooseUpload()" class="px-5 py-2.5 rounded-full border font-semibold">{{ __('cwa.membership.id_upload') }}</button>
                <button type="button" @click="chooseScan()" class="px-5 py-2.5 rounded-full border font-semibold bg-brand-gold/15">{{ __('cwa.membership.id_scan') }}</button>
            </div>
            <div x-show="qrSrc || handoffUrl" x-cloak class="mt-6 text-center">
                <p class="text-sm text-slate-600 mb-3">{{ __('cwa.membership.id_qr_hint') }}</p>
                <img x-show="qrSrc" :src="qrSrc" alt="" class="mx-auto w-48 h-48 bg-white p-2 rounded-xl border">
                <p class="text-xs text-slate-400 mt-2 break-all" x-text="handoffUrl"></p>
                <p class="text-sm text-brand-blue mt-3" x-text="waitingId ? '{{ __('cwa.membership.id_waiting') }}' : ''"></p>
            </div>
            <p x-show="reading" class="text-sm text-slate-500 mt-4">{{ __('cwa.membership.id_reading') }}</p>
            <button type="button" @click="step = 'idtype'" class="mt-6 w-full border rounded-full py-3 font-semibold">{{ __('cwa.membership.prev') }}</button>
        </div>

        {{-- ID fields --}}
        <div x-show="step === 'idfields'" x-cloak>
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.id_name') }}</label>
            <input name="ocr_name" x-model="idName" @input="if (idName) fullName = idName" class="w-full mt-1 mb-3 rounded-md border px-3 py-2">
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.id_issue_date') }}</label>
            <input name="id_issue_date" x-model="issueDate" class="w-full mt-1 mb-3 rounded-md border px-3 py-2">
            <label class="text-sm font-semibold text-gray-700">{{ __('cwa.membership.id_issue_place') }}</label>
            <input name="id_issue_place" x-model="issuePlace" class="w-full mt-1 rounded-md border px-3 py-2">
            <div class="flex gap-3 mt-6">
                <button type="button" @click="step = 'idhow'" class="flex-1 border rounded-full py-3 font-semibold">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="step = 'selfie'" class="flex-1 bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Selfie --}}
        <div x-show="step === 'selfie'" x-cloak class="text-center">
            <button type="button" data-cwa-selfie-open class="inline-flex items-center justify-center gap-2 min-h-[3rem] px-8 py-3 rounded-full bg-brand-gold text-brand-blue font-extrabold">
                {{ __('cwa.membership.selfie_btn') }}
            </button>
            <p class="text-sm text-emerald-700 mt-3" id="membership-selfie-status">{{ __('cwa.membership.no_file') }}</p>
            <div class="cwa-selfie-preview mx-auto mt-4" id="wizard-selfie-wrap" x-show="hasSelfie" x-cloak>
                <img id="membership-selfie-preview" alt="" class="hidden">
                <span id="membership-selfie-placeholder" class="cwa-selfie-placeholder hidden"></span>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" @click="step = 'idfields'" class="flex-1 border rounded-full py-3 font-semibold">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="goSign()" class="flex-1 bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Sign --}}
        <div x-show="step === 'sign'" x-cloak>
            <p class="font-semibold text-gray-800" x-text="fullName"></p>
            <button type="button" @click="openSign()" class="mt-4 w-full inline-flex items-center justify-center gap-2 min-h-[3rem] px-8 py-3 rounded-full bg-brand-gold text-brand-blue font-extrabold">
                {{ __('cwa.membership.sign_btn') }}
            </button>
            <div x-show="signPanel" x-cloak class="mt-4 border-2 border-dashed border-brand-blue rounded-xl p-3">
                <canvas id="membership-signature-pad" width="500" height="160" class="w-full bg-white rounded-lg touch-none"></canvas>
                <button type="button" @click="clearPad()" class="mt-2 text-sm underline">Clear</button>
            </div>
            <div x-show="signQr" x-cloak class="mt-6 text-center">
                <p class="text-sm text-slate-600 mb-3">{{ __('cwa.membership.sign_qr_hint') }}</p>
                <img :src="signQr" alt="" class="mx-auto w-48 h-48 bg-white p-2 rounded-xl border">
            </div>
            <p x-show="signature" class="text-sm text-emerald-700 mt-3">{{ __('cwa.membership.sign_ready') }}</p>
            <img x-show="signature" :src="signature" alt="" class="mt-2 max-h-20 border rounded">
            <div class="flex gap-3 mt-6">
                <button type="button" @click="step = 'selfie'" class="flex-1 border rounded-full py-3 font-semibold">{{ __('cwa.membership.prev') }}</button>
                <button type="submit" class="flex-1 bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.submit') }}</button>
            </div>
        </div>
    </form>
</div>
@include('beyond.membership.partials.circle_selfie')
@include('beyond.apply.partials.camera_capture')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
function membershipWizard() {
    return {
        step: 'phone',
        countryCode: '+237',
        phone: @json(old('phone', '')),
        fullName: @json(old('name', '')),
        donorName: '',
        operator: '',
        looking: false,
        timer: null,
        last: '',
        waSame: true,
        waCountry: '+237',
        waPhone: '',
        diocese: @json(old('diocese', '')),
        parish: @json(old('parish', '')),
        idType: '',
        idPath: '',
        idName: '',
        issueDate: '',
        issuePlace: '',
        selfiePath: '',
        hasSelfie: false,
        signature: '',
        qrSrc: '',
        handoffUrl: '',
        waitingId: false,
        reading: false,
        signPanel: false,
        signQr: '',
        pad: null,
        holderUrl: @json(route('beyond.membership.holder')),
        init: function () {
            var self = this;
            var input = document.getElementById('membership-selfie-input');
            if (input) {
                input.addEventListener('change', function () {
                    self.hasSelfie = !!(input.files && input.files[0]);
                });
            }
        },
        csrf: function () {
            var el = document.querySelector('#membership-registration-form input[name=_token]');
            return el ? el.value : '';
        },
        isMobile: function () {
            return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
        },
        normalizePhone: function () {
            this.phone = String(this.phone || '').replace(/\D/g, '');
            if (this.countryCode === '+237') {
                this.phone = this.phone.replace(/^237/, '').replace(/^0/, '').slice(0, 9);
            }
        },
        scheduleLookup: function () {
            var self = this;
            clearTimeout(this.timer);
            this.timer = setTimeout(function () { self.lookupNow(); }, 400);
        },
        lookupNow: function () {
            this.normalizePhone();
            if (this.countryCode !== '+237' || this.phone.length < 9) {
                this.operator = '';
                this.donorName = '';
                return;
            }
            if (this.phone === this.last) return;
            this.last = this.phone;
            this.looking = true;
            var self = this;
            fetch(this.holderUrl + '?phone=' + encodeURIComponent(this.phone) + '&country_code=' + encodeURIComponent(this.countryCode), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.ok ? r.json() : null; })
            .then(function (res) {
                self.looking = false;
                if (!res) return;
                self.operator = res.operator || '';
                self.donorName = res.name || '';
                if (res.name && !self.fullName) self.fullName = res.name;
            }).catch(function () { self.looking = false; });
        },
        goIdType: function () {
            if (!this.phone || !this.fullName || !this.diocese || !this.parish) return;
            this.step = 'idtype';
        },
        chooseUpload: function () {
            var self = this;
            var input = document.getElementById('id-file-input');
            input.onchange = function () {
                if (!input.files || !input.files[0]) return;
                self.uploadId(input.files[0]);
            };
            input.click();
        },
        uploadId: function (file) {
            var self = this;
            this.reading = true;
            var fd = new FormData();
            fd.append('_token', this.csrf());
            fd.append('document', file);
            fd.append('id_type', this.idType);
            fetch(@json(route('beyond.membership.ocr')), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    self.reading = false;
                    if (!res || !res.ok) return;
                    self.applyOcr(res);
                }).catch(function () { self.reading = false; });
        },
        applyOcr: function (res) {
            this.idPath = res.path || '';
            var o = res.ocr || {};
            this.idName = o.name || this.fullName;
            if (o.name) this.fullName = o.name;
            this.issueDate = o.issue_date || '';
            this.issuePlace = o.issue_place || '';
            this.step = 'idfields';
            this.waitingId = false;
        },
        chooseScan: function () {
            if (this.isMobile()) {
                this.startMobileScan();
                return;
            }
            this.startQr('scan');
        },
        startMobileScan: function () {
            var self = this;
            if (window.BeyondApplyCamera) {
                var input = document.getElementById('id-file-input');
                window.BeyondApplyCamera.open({
                    targetInput: input,
                    facingMode: 'environment',
                    title: @json(__('cwa.membership.id_scan'))
                });
                input.addEventListener('change', function once() {
                    if (input.files && input.files[0]) self.uploadId(input.files[0]);
                    input.removeEventListener('change', once);
                });
                return;
            }
            this.chooseUpload();
        },
        startQr: function (purpose) {
            var self = this;
            var fd = new FormData();
            fd.append('_token', this.csrf());
            fd.append('purpose', purpose);
            fd.append('id_type', this.idType);
            fetch(@json(route('beyond.membership.handoff.create')), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (!res || !res.ok) return;
                    self.handoffUrl = res.url || '';
                    if (purpose === 'scan') {
                        self.qrSrc = res.qr || '';
                        self.waitingId = true;
                    } else {
                        self.signQr = res.qr || '';
                    }
                    self.pollToken(res.token, purpose);
                });
        },
        pollToken: function (token, purpose) {
            var self = this;
            var n = 0;
            var t = setInterval(function () {
                n++;
                if (n > 90) { clearInterval(t); return; }
                fetch(@json(url('/membership/continue')).replace(/\/$/, '') + '/' + token + '/status', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (!res || !res.done) return;
                        clearInterval(t);
                        if (purpose === 'scan') self.applyOcr(res);
                        if (purpose === 'sign' && res.signature) {
                            self.signature = res.signature;
                            self.signQr = '';
                        }
                    });
            }, 2000);
        },
        goSign: function () {
            var input = document.getElementById('membership-selfie-input');
            this.hasSelfie = !!(input && input.files && input.files[0]);
            if (!this.hasSelfie) {
                alert(@json(__('cwa.membership.selfie')));
                return;
            }
            this.step = 'sign';
        },
        openSign: function () {
            if (this.isMobile()) {
                this.signPanel = true;
                this.$nextTick(function () { this.initPad(); }.bind(this));
                return;
            }
            this.startQr('sign');
        },
        initPad: function () {
            var canvas = document.getElementById('membership-signature-pad');
            if (!canvas || typeof SignaturePad === 'undefined') return;
            this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: 'rgb(0,61,130)' });
        },
        clearPad: function () {
            if (this.pad) this.pad.clear();
        }
    };
}

document.addEventListener('submit', function (e) {
    var form = document.getElementById('membership-registration-form');
    if (e.target !== form) return;
    var root = (window.Alpine && Alpine.$data) ? Alpine.$data(form) : (form.__x ? form.__x.$data : null);
    if (root && root.pad && !root.pad.isEmpty()) {
        root.signature = root.pad.toDataURL('image/png');
    }
    var hidden = document.getElementById('membership_signature_input');
    if (root) hidden.value = root.signature || '';
    if (!hidden.value) {
        e.preventDefault();
        alert(@json(__('cwa.membership.sign_required')));
    }
});
</script>
@endpush
