@extends('beyond.layout')

@section('title', $purpose === 'sign' ? __('cwa.membership.handoff_sign_title') : __('cwa.membership.handoff_scan_title'))

@section('content')
<div class="max-w-lg mx-auto px-4 py-8" x-data="handoffPage()">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-2">
        {{ $purpose === 'sign' ? __('cwa.membership.handoff_sign_title') : __('cwa.membership.handoff_scan_title') }}
    </h1>
    <p class="text-sm text-slate-500 mb-6">{{ __('cwa.membership.id_step_hint') }}</p>

    <div x-show="done" x-cloak class="bg-white rounded-2xl border p-6 text-center">
        <p class="font-semibold text-emerald-700">{{ __('cwa.membership.handoff_done') }}</p>
    </div>

    @if ($purpose === 'scan')
        <div x-show="!done" class="bg-white rounded-2xl shadow border p-5">
            <input type="file" id="handoff-doc" accept="image/*" capture="environment" class="sr-only">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="upload()" class="px-5 py-2.5 rounded-full border font-semibold">{{ __('cwa.membership.id_upload') }}</button>
                <button type="button" @click="scan()" class="px-5 py-2.5 rounded-full border font-semibold bg-brand-gold/20">{{ __('cwa.membership.id_scan') }}</button>
            </div>
            <p x-show="busy" class="text-sm text-slate-500 mt-4">{{ __('cwa.membership.id_reading') }}</p>
            <p x-show="error" class="text-sm text-red-600 mt-3" x-text="error"></p>
        </div>
    @else
        <div x-show="!done" class="bg-white rounded-2xl shadow border p-5">
            <canvas id="handoff-signature-pad" width="500" height="180" class="w-full bg-white rounded-lg border touch-none"></canvas>
            <div class="flex gap-2 mt-3">
                <button type="button" @click="clearPad()" class="flex-1 border rounded-full py-3 font-semibold">Clear</button>
                <button type="button" @click="sendSign()" class="flex-1 bg-brand-gold text-brand-blue font-extrabold rounded-full py-3">{{ __('cwa.membership.sign_btn') }}</button>
            </div>
            <p x-show="error" class="text-sm text-red-600 mt-3" x-text="error"></p>
        </div>
    @endif
</div>
@if ($purpose === 'scan')
    @include('beyond.apply.partials.camera_capture')
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
function handoffPage() {
    return {
        done: false,
        busy: false,
        error: '',
        pad: null,
        token: @json($token),
        idType: @json($idType),
        csrf: @json(csrf_token()),
        init: function () {
            var canvas = document.getElementById('handoff-signature-pad');
            if (canvas && typeof SignaturePad !== 'undefined') {
                this.pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)', penColor: 'rgb(0,61,130)' });
            }
        },
        upload: function () {
            var self = this;
            var input = document.getElementById('handoff-doc');
            input.onchange = function () {
                if (input.files && input.files[0]) self.sendFile(input.files[0]);
            };
            input.click();
        },
        scan: function () {
            var self = this;
            var input = document.getElementById('handoff-doc');
            if (window.BeyondApplyCamera) {
                window.BeyondApplyCamera.open({
                    targetInput: input,
                    facingMode: 'environment',
                    title: @json(__('cwa.membership.id_scan'))
                });
                input.addEventListener('change', function once() {
                    if (input.files && input.files[0]) self.sendFile(input.files[0]);
                    input.removeEventListener('change', once);
                });
                return;
            }
            this.upload();
        },
        sendFile: function (file) {
            var self = this;
            this.busy = true;
            this.error = '';
            var fd = new FormData();
            fd.append('_token', this.csrf);
            fd.append('document', file);
            fd.append('id_type', this.idType);
            fetch(@json(url('/membership/continue')).replace(/\/$/, '') + '/' + this.token + '/file', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); })
            .then(function (res) {
                self.busy = false;
                if (!res || !res.ok) {
                    self.error = @json(__('cwa.membership.handoff_missing'));
                    return;
                }
                self.done = true;
            }).catch(function () {
                self.busy = false;
                self.error = @json(__('cwa.membership.handoff_missing'));
            });
        },
        clearPad: function () {
            if (this.pad) this.pad.clear();
        },
        sendSign: function () {
            if (!this.pad || this.pad.isEmpty()) {
                this.error = @json(__('cwa.membership.sign_required'));
                return;
            }
            var self = this;
            this.error = '';
            var fd = new FormData();
            fd.append('_token', this.csrf);
            fd.append('signature', this.pad.toDataURL('image/png'));
            fetch(@json(url('/membership/continue')).replace(/\/$/, '') + '/' + this.token + '/sign', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.ok) {
                    self.error = @json(__('cwa.membership.handoff_missing'));
                    return;
                }
                self.done = true;
            }).catch(function () {
                self.error = @json(__('cwa.membership.handoff_missing'));
            });
        }
    };
}
</script>
@endpush
