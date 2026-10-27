<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Add your {{ strtolower($label ?? 'signature') }} — {{ $general_setting->site_title ?? 'CWA Cameroon' }}</title>
    <meta property="og:title" content="Add your {{ strtolower($label ?? 'signature') }}">
    <meta property="og:description" content="{{ $general_setting->site_title ?? 'Catholic Women\'s Association Cameroon' }}">
    <meta property="og:url" content="{{ url('/user-sign/'.$token) }}">
    <style>
        :root { --primary:#0b3f90; --gold:#d4af37; --text:#1f2a44; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Nunito, system-ui, sans-serif; background: linear-gradient(180deg,#f4f8ff 0%,#e8efff 100%); color:var(--text); min-height:100vh; }
        .wrap { max-width: 640px; margin: 0 auto; padding: 28px 16px 40px; }
        .card { background:#fff; border:1px solid #d7e4fb; border-radius:16px; padding:22px; box-shadow:0 8px 24px rgba(11,63,144,.08); }
        h1 { margin:0 0 8px; font-size:22px; color:var(--primary); }
        h2 { margin:18px 0 8px; font-size:16px; color:var(--primary); }
        p { color:#64748b; margin:0 0 16px; line-height:1.5; }
        canvas.pad { display:block; width:100%; height:200px; border:2px dashed #0b3f90; border-radius:12px; touch-action:none; background:#f8fbff; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
        .btn { border:0; border-radius:10px; padding:12px 18px; font-weight:700; cursor:pointer; font-size:15px; }
        .btn-primary { background:var(--primary); color:#fff; }
        .btn-secondary { background:#e2e8f0; color:#334155; }
        .btn-gold { background:var(--gold); color:#3b2a08; }
        .alert { padding:12px 14px; border-radius:10px; margin-bottom:14px; background:#ffe5e5; color:#842029; }
        .name { font-weight:700; color:var(--primary); }
        .photo-box { margin-top:8px; }
        .photo-box input { display:block; width:100%; margin-top:8px; }
        .preview { display:none; margin-top:12px; max-width:220px; border-radius:12px; }
        .selfie-frame { width:180px; height:180px; border-radius:50%; border:4px solid var(--gold); overflow:hidden; background:#f8fbff; display:flex; align-items:center; justify-content:center; color:#94a3b8; margin:8px 0 12px; }
        .selfie-frame img { width:100%; height:100%; object-fit:cover; display:block; }
        .or { margin:16px 0 0; font-size:13px; letter-spacing:.08em; text-transform:uppercase; font-weight:800; color:#8a6d1d; }
    </style>
</head>
<body>
@php
    $isSelfie = ($type ?? 'sign') === 'selfie';
    $pads = ($type ?? 'sign') === 'all'
        ? ['signature_image' => 'Signature', 'comment_image' => 'Comment', 'approver_image' => 'Approver']
        : ['signature_image' => $label ?? 'Signature'];
    $company = $general_setting->site_title ?? 'CWA Cameroon';
@endphp
<div class="wrap">
    <div class="card">
        <h1>{{ $isSelfie ? 'Add your selfie' : (($type ?? 'sign') === 'all' ? 'Add your signatures' : 'Add your '.($label ?? 'signature')) }}</h1>
        @if($isSelfie)
            <p>Hi <span class="name">{{ $user->name }}</span> — {{ $company }} asked for a selfie. Take one now, or choose a photo, then save.</p>
        @else
            <p>Hi <span class="name">{{ $user->name }}</span> — {{ $company }} asked you to sign. Draw with your finger in the box. If the line does not appear, take a photo of your signature instead, then save.</p>
        @endif

        @if(session('not_permitted'))
            <div class="alert">{{ session('not_permitted') }}</div>
        @endif
        @if($errors->any())
            <div class="alert">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        <form method="POST" action="{{ route('user.public.sign.store', $token) }}" id="public-sign-form" enctype="multipart/form-data">
            @csrf
            @if($isSelfie)
                <div class="selfie-frame" id="selfie-frame"><span>Your photo</span></div>
                <label class="btn btn-gold" for="selfie_file">Take or choose a selfie</label>
                <input id="selfie_file" type="file" name="selfie_file" accept="image/*" capture="user" style="position:absolute;left:-9999px;">
            @else
                @foreach($pads as $input => $padLabel)
                    <h2>{{ $padLabel }}</h2>
                    <canvas class="pad" data-input="{{ $input }}"></canvas>
                    <input type="hidden" name="{{ $input }}" id="{{ $input }}">
                    <div class="actions">
                        <button type="button" class="btn btn-secondary clear-pad" data-input="{{ $input }}">Clear</button>
                    </div>
                @endforeach
                <p class="or">Or add a photo of the signature</p>
                <div class="photo-box">
                    <input type="file" name="signature_file" id="signature_file" accept="image/*" capture="environment">
                    <img id="signature-preview" class="preview" alt="">
                </div>
            @endif
            <div class="actions">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    function previewFile(input, img) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function () {
                img.src = reader.result;
                img.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }

    var selfie = document.getElementById('selfie_file');
    var frame = document.getElementById('selfie-frame');
    if (selfie && frame) {
        selfie.addEventListener('change', function () {
            var file = selfie.files && selfie.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function () {
                frame.innerHTML = '<img alt="Selfie" src="' + reader.result + '">';
            };
            reader.readAsDataURL(file);
        });
    }

    var sigFile = document.getElementById('signature_file');
    var sigPreview = document.getElementById('signature-preview');
    if (sigFile && sigPreview) previewFile(sigFile, sigPreview);

    function makePad(canvas) {
        var ctx = canvas.getContext('2d');
        var strokes = [];
        var current = null;
        function point(e) {
            var r = canvas.getBoundingClientRect();
            var t = (e.touches && e.touches[0]) || (e.changedTouches && e.changedTouches[0]) || e;
            return { x: t.clientX - r.left, y: t.clientY - r.top };
        }
        function drawStroke(pts) {
            if (!pts || !pts.length) return;
            ctx.beginPath();
            ctx.moveTo(pts[0].x, pts[0].y);
            if (pts.length === 1) {
                ctx.lineTo(pts[0].x + 0.4, pts[0].y + 0.4);
            } else {
                for (var i = 1; i < pts.length; i++) ctx.lineTo(pts[i].x, pts[i].y);
            }
            ctx.stroke();
        }
        function paint() {
            var w = canvas.clientWidth || canvas.parentNode.clientWidth || 300;
            var h = 200;
            var ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.max(1, Math.round(w * ratio));
            canvas.height = Math.round(h * ratio);
            ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0b3f90';
            strokes.forEach(drawStroke);
            if (current) drawStroke(current);
        }
        function down(e) {
            if (e.cancelable) e.preventDefault();
            current = [point(e)];
            paint();
        }
        function move(e) {
            if (!current) return;
            if (e.cancelable) e.preventDefault();
            var p = point(e);
            var last = current[current.length - 1];
            if (Math.abs(p.x - last.x) < 0.4 && Math.abs(p.y - last.y) < 0.4) return;
            current.push(p);
            paint();
        }
        function up(e) {
            if (!current) return;
            if (e && e.cancelable) e.preventDefault();
            strokes.push(current);
            current = null;
            paint();
        }
        var touch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
        if (touch) {
            canvas.addEventListener('touchstart', down, { passive: false });
            canvas.addEventListener('touchmove', move, { passive: false });
            canvas.addEventListener('touchend', up, { passive: false });
            canvas.addEventListener('touchcancel', up, { passive: false });
        } else {
            canvas.addEventListener('mousedown', down);
            window.addEventListener('mousemove', move);
            window.addEventListener('mouseup', up);
        }
        paint();
        window.addEventListener('resize', paint);
        return {
            isEmpty: function () { return strokes.length === 0 && !current; },
            clear: function () { strokes = []; current = null; paint(); },
            toDataURL: function () { return canvas.toDataURL('image/png'); }
        };
    }

    var pads = {};
    Array.prototype.forEach.call(document.querySelectorAll('canvas.pad'), function (canvas) {
        pads[canvas.getAttribute('data-input')] = makePad(canvas);
    });
    Array.prototype.forEach.call(document.querySelectorAll('.clear-pad'), function (btn) {
        btn.addEventListener('click', function () {
            var pad = pads[btn.getAttribute('data-input')];
            if (pad) pad.clear();
        });
    });

    var form = document.getElementById('public-sign-form');
    form.addEventListener('submit', function (e) {
        if (selfie) {
            if (!selfie.files || !selfie.files.length) {
                e.preventDefault();
                alert('Please take or choose a selfie first.');
            }
            return;
        }
        var drawn = 0;
        Object.keys(pads).forEach(function (key) {
            var pad = pads[key];
            var input = document.getElementById(key);
            if (!pad || pad.isEmpty()) {
                if (input) input.value = '';
                return;
            }
            drawn++;
            if (input) input.value = pad.toDataURL();
        });
        var hasFile = sigFile && sigFile.files && sigFile.files.length;
        if (!drawn && !hasFile) {
            e.preventDefault();
            alert('Draw your signature, or add a photo of it.');
        }
    });
})();
</script>
</body>
</html>
