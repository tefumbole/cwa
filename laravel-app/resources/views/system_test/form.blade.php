@extends('beyond.layout')

@section('title', 'System test')

@section('content')
<style>
    .test-layout {
        max-width: 1120px; margin: 0 auto; padding: 2rem 1rem 5rem;
        display: grid; grid-template-columns: minmax(0, 1fr) 240px; gap: 1.25rem; align-items: start;
    }
    .progress-rail {
        position: sticky; top: 5.5rem;
        background: #fff; border: 1px solid #e7e1d4; border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 61, 130, .08); padding: 1rem 1rem 1.1rem;
    }
    .progress-rail h2 { margin: 0 0 .7rem; color: #003D82; font-family: Fraunces, Georgia, serif; font-size: 1.2rem; }
    .bar-track { height: 12px; border-radius: 999px; background: #f3efe6; overflow: hidden; border: 1px solid #e7e1d4; }
    .bar-fill { height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, #003D82, #D4AF37); transition: width .25s ease; }
    .pct { margin: .7rem 0 .15rem; font-size: 2.1rem; font-weight: 800; color: #003D82; line-height: 1; }
    .rail-detail { margin: 0; color: #3d4654; font-size: .92rem; }
    @media (max-width: 860px) {
        .test-layout { grid-template-columns: 1fr; padding-top: 1rem; }
        .progress-rail { position: sticky; top: .5rem; z-index: 20; }
    }
    .test-kicker { letter-spacing: .16em; text-transform: uppercase; font-size: .72rem; font-weight: 800; color: #8a6d1d; }
    .test-card {
        background: #fff;
        border: 1px solid #e7e1d4;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0, 61, 130, .06);
        padding: 1.25rem 1.25rem .4rem;
        margin-bottom: 1.1rem;
    }
    .test-card h2 { margin: 0 0 .35rem; color: #003D82; font-family: Fraunces, Georgia, serif; font-size: 1.45rem; }
    .steps { margin: 0; padding: 0; list-style: none; }
    .steps li { display: flex; gap: .75rem; padding: .7rem 0; border-top: 1px solid #f0ebe1; line-height: 1.45; }
    .steps li:first-child { border-top: 0; }
    .step-no {
        flex: 0 0 1.8rem; height: 1.8rem; border-radius: 999px;
        background: #003D82; color: #fff; font-weight: 800; font-size: .85rem;
        display: flex; align-items: center; justify-content: center;
    }
    .check {
        border: 1px solid #e7e1d4;
        border-left: 4px solid #D4AF37;
        border-radius: 14px;
        padding: .9rem 1rem;
        margin: 0 0 .85rem;
        background: #fffdf8;
    }
    .check.is-done { border-left-color: #0f6b4c; }
    .task-head { display: flex; gap: .7rem; align-items: flex-start; }
    .task-no {
        flex: 0 0 2rem; height: 2rem; border-radius: 999px;
        background: #003D82; color: #fff; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .check.is-done .task-no { background: #0f6b4c; }
    .check p { margin: 0 0 .7rem; line-height: 1.45; }
    .task-head .instruction { margin: .15rem 0 0; }
    .instruction { font-weight: 700; color: #1a1f2e; }
    .how { margin: 0 0 .8rem; padding-left: 1.2rem; color: #3d4654; }
    .how li { margin: 0 0 .35rem; }
    .result-label { margin: .2rem 0 .45rem; font-size: .75rem; letter-spacing: .12em; text-transform: uppercase; font-weight: 800; color: #8a6d1d; }
    .choices { display: flex; flex-wrap: wrap; gap: .5rem; }
    .choice {
        position: relative;
        border: 1.5px solid #e7e1d4;
        border-radius: 999px;
        padding: .4rem .85rem;
        cursor: pointer;
        font-size: .92rem;
        font-weight: 700;
        background: #fff;
    }
    .choice input { position: absolute; opacity: 0; }
    .choice.ok:has(input:checked) { border-color: #0f6b4c; background: #e8f7f0; color: #0f6b4c; }
    .choice.bad:has(input:checked) { border-color: #8a1f1f; background: #fff1f1; color: #8a1f1f; }
    .choice.skip:has(input:checked) { border-color: #003D82; background: #eef4ff; color: #003D82; }
    .note, .field input, .field textarea {
        width: 100%; margin-top: .55rem; border: 1px solid #e7e1d4; border-radius: 12px;
        padding: .7rem .8rem; font: inherit; background: #fff;
    }
    .field label { display: block; font-weight: 800; margin: .8rem 0 .25rem; color: #003D82; }
    .go, .go-ghost {
        border-radius: 999px; padding: .85rem 1.3rem; font-weight: 800; cursor: pointer; font: inherit;
    }
    .go { background: #003D82; color: #fff; border: 0; }
    .go:hover { background: #002855; }
    .go:disabled { opacity: .45; cursor: not-allowed; }
    .go-ghost { background: #fff; color: #003D82; border: 1.5px solid #003D82; }
    .saved { background: #e8f7f0; border: 1px solid #b7e4cf; color: #0f6b4c; border-radius: 12px; padding: .8rem 1rem; margin-bottom: 1rem; }
    .page-jump {
        display: flex; gap: .45rem; align-items: center; width: 100%; text-align: left;
        border: 0; background: transparent; border-radius: 10px; padding: .35rem .2rem; cursor: pointer; font: inherit; color: #3d4654;
    }
    .page-jump.is-current { background: #eef4ff; color: #003D82; font-weight: 800; }
    .page-jump.is-complete { color: #0f6b4c; }
    .page-jump .jump-no { flex: 0 0 1.4rem; font-weight: 800; }
    .page-jump .jump-title { flex: 1; }
    .actions { display: flex; flex-wrap: wrap; gap: .6rem; align-items: center; }
    .progress {
        display: flex; justify-content: space-between; gap: 1rem; align-items: center; flex-wrap: wrap;
        border: 1px solid #e7e1d4; border-radius: 16px; padding: .9rem 1rem; background: #fff;
    }
    .links { display: flex; flex-wrap: wrap; gap: .5rem; margin-top: .8rem; }
    .links a {
        border: 1.5px solid #D4AF37; color: #003D82; border-radius: 999px;
        padding: .35rem .8rem; font-weight: 800; text-decoration: none; background: #fff;
    }
    .err { background: #fff1f1; border: 1px solid #f0c2c2; color: #8a1f1f; border-radius: 12px; padding: .8rem 1rem; margin-bottom: 1rem; }
    .hp { position: absolute; left: -9999px; }
</style>

<div class="test-layout">
<div>
    <p class="test-kicker">CWACAM</p>
    <h1 class="text-3xl md:text-4xl text-brand-blue mb-2" style="font-family: Fraunces, Georgia, serif;">Test the website</h1>
    <p class="text-stone-600 mb-4">Work through one page, then save it. You can leave and come back later with your WhatsApp number. The result can be sent only after every question has an answer.</p>

    @if(session('test_saved'))
        <div class="saved">Saved. You can close this page and come back later with the same WhatsApp number.</div>
    @endif
    @if(session('test_expired'))
        <div class="err">This page was open for a long time, so the first send expired. Your answers are still saved. Press the button again.</div>
    @endif
    @if($errors->any())
        <div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    @if($mode === 'gate')
        <section class="test-card field">
            <h2>Start a new test</h2>
            <p class="text-stone-600">Use the WhatsApp number where the result should be sent.</p>
            <form method="POST" action="{{ route('system-test.start') }}">
                @csrf
                <div class="hp" aria-hidden="true"><label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label></div>
                <label for="tester_name">Your name</label>
                <input id="tester_name" name="tester_name" required value="{{ old('tester_name') }}" placeholder="Your name">
                <label for="tester_phone">WhatsApp number</label>
                <input id="tester_phone" name="tester_phone" required value="{{ old('tester_phone') }}" placeholder="675321739" inputmode="tel" autocomplete="tel">
                <div class="actions" style="margin-top:1rem;">
                    <button class="go" type="submit">Start page 1</button>
                </div>
            </form>
        </section>
        <section class="test-card field">
            <h2>Continue a saved test</h2>
            <p class="text-stone-600">Enter the same WhatsApp number. We send a code to that phone, and the saved answers open after the code matches.</p>
            <form method="POST" action="{{ route('system-test.resume') }}">
                @csrf
                <div class="hp" aria-hidden="true"><label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label></div>
                <label for="resume_phone">WhatsApp number</label>
                <input id="resume_phone" name="tester_phone" required value="{{ old('tester_phone') }}" placeholder="675321739" inputmode="tel" autocomplete="tel">
                <div class="actions" style="margin-top:1rem;">
                    <button class="go" type="submit">Send a code</button>
                </div>
            </form>
        </section>
    @elseif($mode === 'verify')
        <section class="test-card field">
            <h2>Confirm your number</h2>
            <p class="text-stone-600">Enter the 6-digit code sent to WhatsApp {{ $pendingPhone }}.</p>
            <form method="POST" action="{{ route('system-test.verify') }}">
                @csrf
                <label for="code">Code</label>
                <input id="code" name="code" required inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="123456">
                <div class="actions" style="margin-top:1rem;">
                    <button class="go" type="submit" name="action" value="open">Open saved test</button>
                    <button class="go-ghost" type="submit" name="action" value="replace">Start this number again</button>
                </div>
                <p class="text-stone-600" style="margin-top:.8rem;">Start again clears the saved answers for this number.</p>
            </form>
        </section>
    @else
    <form method="POST" action="{{ route('system-test.save') }}" id="system-test" data-total="{{ $progress['total'] }}" data-elsewhere="{{ $progress['answered'] - $progress['pages'][$page - 1]['answered'] }}" data-page-count="{{ count($current['checks']) }}">
        @csrf
        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="action" id="picked-action" value="later">
        <input type="hidden" name="goto" id="picked-goto" value="">
        <div class="hp" aria-hidden="true">
            <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
        </div>

        <section class="test-card">
            <p class="test-kicker">Page {{ $page }} of {{ $pageCount }}</p>
            <h2>{{ $current['title'] }}</h2>
            <p class="text-stone-600 mb-3">{{ $current['intro'] }} Testing as {{ $draft['tester_name'] }}, {{ $draft['tester_phone'] }}.</p>
            @foreach($current['checks'] as $index => $check)
                @php
                    $taskNo = $numberStart + $index + 1;
                    $picked = old('checks.'.$check['id'], $draft['checks'][$check['id']] ?? '');
                    $note = old('notes.'.$check['id'], $draft['notes'][$check['id']] ?? '');
                @endphp
                <div class="check{{ $picked !== '' ? ' is-done' : '' }}" id="task-{{ $taskNo }}">
                    <div class="task-head">
                        <span class="task-no">{{ $taskNo }}</span>
                        <p class="instruction">{{ $check['text'] }}</p>
                    </div>
                    @if(!empty($check['steps']))
                        <ol class="how">
                            @foreach($check['steps'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>
                    @endif
                    <p class="result-label">Result</p>
                    <div class="choices">
                        <label class="choice ok"><input type="radio" name="checks[{{ $check['id'] }}]" value="works" @if($picked === 'works') checked @endif> Works</label>
                        <label class="choice bad"><input type="radio" name="checks[{{ $check['id'] }}]" value="fails" @if($picked === 'fails') checked @endif> Does not work</label>
                        <label class="choice skip"><input type="radio" name="checks[{{ $check['id'] }}]" value="skipped" @if($picked === 'skipped') checked @endif> Not tested</label>
                    </div>
                    <input class="note" type="text" name="notes[{{ $check['id'] }}]" value="{{ $note }}" placeholder="If it failed, what did you see?">
                </div>
            @endforeach
        </section>

        @if($page === $pageCount)
            <section class="test-card field">
                <h2>Anything else</h2>
                <textarea name="summary" rows="4" placeholder="Optional. Tell the administrator anything the list did not cover.">{{ old('summary', $draft['summary'] ?? '') }}</textarea>
            </section>
        @endif

        <div class="progress">
            <span id="progress-inline">Save this page when you are ready.</span>
            <div class="actions">
                <button class="go-ghost" type="submit" data-action="later">Save and continue later</button>
                @if($page > 1)
                    <button class="go-ghost" type="submit" data-action="prev">Previous page</button>
                @endif
                @if($page < $pageCount)
                    <button class="go" type="submit" data-action="next" id="next-page">Save and next page</button>
                @else
                    <button class="go" type="submit" data-action="submit" id="send-result">Send the result</button>
                @endif
            </div>
        </div>
    </form>
    @endif
</div>
<aside class="progress-rail" aria-live="polite">
    <h2>Progress</h2>
    <div class="bar-track"><div class="bar-fill" id="bar-fill" style="width: {{ $progress['total'] ? round($progress['answered'] / $progress['total'] * 100) : 0 }}%"></div></div>
    <p class="pct" id="progress-pct">{{ $progress['total'] ? round($progress['answered'] / $progress['total'] * 100) : 0 }}%</p>
    <p class="rail-detail" id="progress">{{ $progress['answered'] }} of {{ $progress['total'] }} answered</p>
    @if($mode === 'test')
        <div style="margin-top:.8rem;">
            @foreach($progress['pages'] as $index => $stat)
                <button class="page-jump {{ ($index + 1) === $page ? 'is-current' : '' }} {{ $stat['answered'] === $stat['total'] ? 'is-complete' : '' }}" type="submit" form="system-test" data-goto="{{ $index + 1 }}">
                    <span class="jump-no">{{ $index + 1 }}</span>
                    <span class="jump-title">{{ $stat['title'] }}</span>
                    <span>{{ $stat['answered'] }}/{{ $stat['total'] }}</span>
                </button>
            @endforeach
        </div>
    @endif
</aside>
</div>
<script>
(function () {
    var form = document.getElementById('system-test');
    if (!form) return;
    var out = document.getElementById('progress');
    var inline = document.getElementById('progress-inline');
    var pct = document.getElementById('progress-pct');
    var bar = document.getElementById('bar-fill');
    var elsewhere = parseInt(form.getAttribute('data-elsewhere'), 10) || 0;
    var total = parseInt(form.getAttribute('data-total'), 10) || 0;
    var pageCount = parseInt(form.getAttribute('data-page-count'), 10) || 0;
    var groups = {};
    Array.prototype.forEach.call(form.querySelectorAll('input[type=radio]'), function (input) {
        groups[input.name] = true;
        input.addEventListener('change', paint);
    });
    function paint() {
        var pageAnswered = 0;
        Object.keys(groups).forEach(function (name) {
            var picked = form.querySelector('input[name="'+name+'"]:checked');
            var any = form.querySelector('input[name="'+name+'"]');
            if (!picked) {
                if (any) any.closest('.check').classList.remove('is-done');
                return;
            }
            picked.closest('.check').classList.add('is-done');
            pageAnswered++;
        });
        var answered = elsewhere + pageAnswered;
        var percent = total ? Math.round(answered / total * 100) : 0;
        if (pct) pct.textContent = percent + '%';
        if (bar) bar.style.width = percent + '%';
        if (out) out.textContent = answered + ' of ' + total + ' answered';
        var next = document.getElementById('next-page');
        var send = document.getElementById('send-result');
        if (next) next.disabled = pageAnswered < pageCount;
        if (send) send.disabled = answered < total;
        if (inline) {
            if (send) inline.textContent = send.disabled ? (total - answered) + ' questions still need an answer before sending.' : 'Every question is answered. You can send the result.';
            else if (next) inline.textContent = next.disabled ? 'Answer every question on this page, then save and move on. Not tested counts.' : 'This page is complete.';
        }
    }
    document.addEventListener('click', function (event) {
        var button = event.target.closest('button[data-action], button[data-goto]');
        if (!button) return;
        var action = document.getElementById('picked-action');
        var gotoField = document.getElementById('picked-goto');
        if (!action || !gotoField) return;
        if (button.getAttribute('data-goto')) {
            gotoField.value = button.getAttribute('data-goto');
            action.value = 'goto';
        } else {
            gotoField.value = '';
            action.value = button.getAttribute('data-action') || 'later';
        }
    });
    paint();

    function freshToken() {
        return fetch('{{ route('system-test.csrf') }}', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (response) { return response.json(); }).then(function (data) {
            var input = form.querySelector('input[name="_token"]');
            if (input && data && data.token) input.value = data.token;
        });
    }
    setInterval(function () { freshToken().catch(function () {}); }, 4 * 60 * 1000);

    var sending = false;
    form.addEventListener('submit', function (event) {
        if (sending) return;
        event.preventDefault();
        freshToken().catch(function () {}).then(function () {
            sending = true;
            form.submit();
        });
    });
})();
</script>
@endsection
