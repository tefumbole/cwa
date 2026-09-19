{{-- Full-screen circular selfie capture (membership). Front camera + gold ring. --}}
<div id="cwa-selfie-modal" class="fixed inset-0 z-[90] hidden flex-col bg-black" aria-hidden="true">
    <div class="relative z-10 px-4 pt-5 pb-2 text-center shrink-0">
        <p class="text-white font-extrabold text-lg m-0 inline-flex items-center justify-center gap-2">
            <img src="{{ \App\Support\SiteBrand::logoUrl($general_setting ?? null) }}" alt="" class="w-8 h-8 rounded-full object-cover border-2 border-brand-gold">
            {{ __('cwa.membership.snap_title') }}
        </p>
        <p class="text-white/70 text-sm mt-1 mb-0">{{ __('cwa.membership.snap_guide') }}</p>
        <button type="button" id="cwa-selfie-close" class="absolute right-3 top-4 text-white/90 font-semibold text-sm min-h-[2.5rem] px-3">
            {{ __('cwa.membership.snap_close') }}
        </button>
    </div>

    <div class="relative flex-1 min-h-0">
        <video id="cwa-selfie-video" class="absolute inset-0 w-full h-full object-cover" playsinline webkit-playsinline autoplay muted></video>
        <canvas id="cwa-selfie-canvas" class="hidden"></canvas>
        <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
            <div id="cwa-selfie-circle" class="cwa-selfie-ring"></div>
        </div>
    </div>

    <div class="relative z-10 px-4 pt-4 pb-[calc(1.25rem+env(safe-area-inset-bottom,0px))] text-center shrink-0">
        <p id="cwa-selfie-error" class="text-red-400 text-xs hidden mb-3"></p>
        <button type="button" id="cwa-selfie-capture"
                class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-brand-gold text-brand-blue shadow-lg border-4 border-white">
            <span class="sr-only">{{ __('cwa.membership.snap_button') }}</span>
        </button>
        <p class="text-white font-bold mt-3 mb-0">{{ __('cwa.membership.snap_button') }}</p>
        <button type="button" id="cwa-selfie-flip" class="mt-2 text-sm text-brand-gold font-semibold">{{ __('cwa.membership.snap_flip') }}</button>
    </div>
</div>

<style>
    .cwa-selfie-ring {
        width: min(72vw, 22rem);
        height: min(72vw, 22rem);
        border-radius: 9999px;
        border: 5px solid #D4AF37;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.58), 0 0 24px rgba(212, 175, 55, 0.55);
        box-sizing: border-box;
    }
    .cwa-selfie-preview {
        display: block;
        width: 11.5rem;
        height: 11.5rem;
        padding: 0;
        border-radius: 9999px;
        border: 4px solid #D4AF37;
        background: #003D82;
        overflow: hidden;
        margin: 0 auto;
        position: relative;
        cursor: pointer;
        box-shadow: 0 0 0 6px rgba(212, 175, 55, 0.18);
        appearance: none;
        -webkit-appearance: none;
    }
    .cwa-selfie-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cwa-selfie-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #F5E6A8;
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
        padding: 1rem;
    }
</style>

<script>
(function () {
    var modal = document.getElementById('cwa-selfie-modal');
    var video = document.getElementById('cwa-selfie-video');
    var canvas = document.getElementById('cwa-selfie-canvas');
    var circle = document.getElementById('cwa-selfie-circle');
    var errEl = document.getElementById('cwa-selfie-error');
    var stream = null;
    var facingMode = 'user';
    var targetInput = document.getElementById('membership-selfie-input');
    var previewImg = document.getElementById('membership-selfie-preview');
    var placeholder = document.getElementById('membership-selfie-placeholder');
    var statusEl = document.getElementById('membership-selfie-status');

    function showError(msg) {
        if (!errEl) return;
        errEl.textContent = msg || '';
        errEl.classList.toggle('hidden', !msg);
    }

    function stopStream() {
        if (stream) {
            stream.getTracks().forEach(function (t) { t.stop(); });
            stream = null;
        }
        if (video) video.srcObject = null;
    }

    function closeModal() {
        stopStream();
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        showError('');
    }

    function startStream() {
        showError('');
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showError(@json(__('cwa.membership.snap_unsupported')));
            return;
        }
        stopStream();
        var constraints = {
            audio: false,
            video: {
                facingMode: facingMode,
                width: { ideal: 1280 },
                height: { ideal: 1280 }
            }
        };
        navigator.mediaDevices.getUserMedia(constraints).then(function (s) {
            stream = s;
            video.setAttribute('playsinline', 'true');
            video.setAttribute('webkit-playsinline', 'true');
            video.srcObject = s;
            video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'none';
            return video.play();
        }).catch(function () {
            return navigator.mediaDevices.getUserMedia({ audio: false, video: true }).then(function (s) {
                stream = s;
                video.srcObject = s;
                return video.play();
            });
        }).catch(function (err) {
            showError(@json(__('cwa.membership.snap_denied')) + ' (' + (err && err.message ? err.message : 'denied') + ')');
        });
    }

    function openModal() {
        if (!modal) return;
        facingMode = 'user';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        startStream();
    }

    function sourceRect() {
        var vw = video.videoWidth || 720;
        var vh = video.videoHeight || 720;
        var vRect = video.getBoundingClientRect();
        var cRect = circle.getBoundingClientRect();
        var scale = Math.max(vRect.width / vw, vRect.height / vh);
        var dispW = vw * scale;
        var dispH = vh * scale;
        var offX = vRect.left + (vRect.width - dispW) / 2;
        var offY = vRect.top + (vRect.height - dispH) / 2;
        var sx = (cRect.left - offX) / scale;
        var sy = (cRect.top - offY) / scale;
        var sw = cRect.width / scale;
        var sh = cRect.height / scale;
        if (facingMode === 'user') {
            sx = vw - sx - sw;
        }
        sx = Math.max(0, Math.min(sx, vw - 1));
        sy = Math.max(0, Math.min(sy, vh - 1));
        sw = Math.max(32, Math.min(sw, vw - sx));
        sh = Math.max(32, Math.min(sh, vh - sy));
        return { sx: sx, sy: sy, sw: sw, sh: sh };
    }

    function showPreview(file) {
        if (!file) return;
        if (previewImg) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.classList.remove('hidden');
        }
        if (placeholder) placeholder.classList.add('hidden');
        if (statusEl) statusEl.textContent = @json(__('cwa.membership.snap_ready'));
    }

    function setFile(file) {
        if (!targetInput || !file) return false;
        try {
            var dt = new DataTransfer();
            dt.items.add(file);
            targetInput.files = dt.files;
        } catch (e) {
            showError(@json(__('cwa.membership.snap_save_fail')));
            return false;
        }
        showPreview(file);
        return true;
    }

    var closeBtn = document.getElementById('cwa-selfie-close');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) closeModal();
    });
    var flipBtn = document.getElementById('cwa-selfie-flip');
    if (flipBtn) {
        flipBtn.addEventListener('click', function () {
            facingMode = facingMode === 'user' ? 'environment' : 'user';
            startStream();
        });
    }
    var captureBtn = document.getElementById('cwa-selfie-capture');
    if (captureBtn) {
        captureBtn.addEventListener('click', function () {
            if (!stream || !video.videoWidth) {
                showError(@json(__('cwa.membership.snap_fail')));
                return;
            }
            var r = sourceRect();
            var size = Math.round(Math.min(r.sw, r.sh));
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, r.sx, r.sy, r.sw, r.sh, 0, 0, size, size);
            canvas.toBlob(function (blob) {
                if (!blob) {
                    showError(@json(__('cwa.membership.snap_fail')));
                    return;
                }
                var file;
                try {
                    file = new File([blob], 'selfie_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                } catch (e) {
                    file = blob;
                    file.name = 'selfie.jpg';
                }
                if (setFile(file)) closeModal();
            }, 'image/jpeg', 0.88);
        });
    }

    var openBtns = document.querySelectorAll('[data-cwa-selfie-open]');
    for (var i = 0; i < openBtns.length; i++) {
        openBtns[i].addEventListener('click', openModal);
    }

    if (targetInput) {
        targetInput.addEventListener('change', function () {
            if (!targetInput.files || !targetInput.files[0]) return;
            showPreview(targetInput.files[0]);
        });
    }
})();
</script>
