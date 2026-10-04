@extends('beyond.layout')

@section('title', 'System test')

@section('content')
<style>
    .test-wrap { max-width: 880px; margin: 0 auto; padding: 2rem 1rem 5rem; }
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
    .check p { margin: 0 0 .7rem; line-height: 1.45; }
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
    .go {
        background: #003D82; color: #fff; border: 0; border-radius: 999px;
        padding: .85rem 1.3rem; font-weight: 800; cursor: pointer;
    }
    .go:hover { background: #002855; }
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

<div class="test-wrap">
    <p class="test-kicker">CWACAM</p>
    <h1 class="text-3xl md:text-4xl text-brand-blue mb-2" style="font-family: Fraunces, Georgia, serif;">Test the website</h1>
    <p class="text-stone-600 mb-4">You do not need to know the system. Follow the steps, try each thing, then mark what worked.</p>

    @if($errors->any())
        <div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <section class="test-card">
        <h2>Start here</h2>
        <p class="text-stone-600 mb-2">Read this before you mark any result. Open the website in a second tab and leave this page open.</p>
        <ol class="steps">
            <li><span class="step-no">1</span><span>Open the website: <a href="{{ url('/') }}" target="_blank">cwacam.org</a>. The first screen is the <strong>homepage</strong>. You should see the CWACAM logo, a row of menu links, <strong>EN / FR</strong>, <strong>Donate</strong>, and <strong>Login</strong>.</span></li>
            <li><span class="step-no">2</span><span>Click the menu links. <strong>About Us</strong> tells the story and shows leaders. <strong>Branches</strong> lists Cameroon and Diaspora. <strong>Membership</strong> is where a woman registers. You do not need a password for these pages.</span></li>
            <li><span class="step-no">3</span><span>On a phone, tap the menu button (three lines) at the top. The same links open in a list, with <strong>Join CWA</strong> and <strong>Donate</strong>.</span></li>
            <li><span class="step-no">4</span><span>To test the office, click <strong>Login</strong>. Type the username and password you were given. If nobody gave you a login, leave the office steps as <strong>Not tested</strong>.</span></li>
            <li><span class="step-no">5</span><span>After login, a <strong>blue menu</strong> is on the left. Each name opens one part of the office: People, Membership, Letters, Settings, and the rest. Click a name, look at the screen, then come back here.</span></li>
            <li><span class="step-no">6</span><span>Do not pay any money. Do not click <strong>Empty Database</strong>. Use a test name if you register a member.</span></li>
            <li><span class="step-no">7</span><span>For each line below, choose <strong>Works</strong>, <strong>Does not work</strong>, or <strong>Not tested</strong>. If it failed, write one sentence about what you saw.</span></li>
            <li><span class="step-no">8</span><span>Put your name and WhatsApp number, then press <strong>Send the result</strong>. The summary goes to your WhatsApp, and a copy goes to the administrator.</span></li>
        </ol>
    </section>

    <form method="POST" action="{{ route('system-test.store') }}" id="system-test">
        @csrf
        <div class="hp" aria-hidden="true">
            <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
        </div>

        <section class="test-card field">
            <h2>Your details</h2>
            <p class="text-stone-600">The result is sent to this WhatsApp number.</p>
            <label for="tester_name">Your name</label>
            <input id="tester_name" name="tester_name" required value="{{ old('tester_name') }}" placeholder="Your name">
            <label for="tester_phone">WhatsApp number</label>
            <input id="tester_phone" name="tester_phone" required value="{{ old('tester_phone') }}" placeholder="675321739" inputmode="tel" autocomplete="tel">
        </section>

        @foreach($sections as $section)
            <section class="test-card">
                <h2>{{ $section['title'] }}</h2>
                <p class="text-stone-600 mb-3">{{ $section['intro'] }}</p>
                @foreach($section['checks'] as $check)
                    <div class="check">
                        <p>{{ $check['text'] }}</p>
                        <div class="choices">
                            <label class="choice ok"><input type="radio" name="checks[{{ $check['id'] }}]" value="works" @if(old('checks.'.$check['id']) === 'works') checked @endif> Works</label>
                            <label class="choice bad"><input type="radio" name="checks[{{ $check['id'] }}]" value="fails" @if(old('checks.'.$check['id']) === 'fails') checked @endif> Does not work</label>
                            <label class="choice skip"><input type="radio" name="checks[{{ $check['id'] }}]" value="skipped" @if(old('checks.'.$check['id']) === 'skipped') checked @endif> Not tested</label>
                        </div>
                        <input class="note" type="text" name="notes[{{ $check['id'] }}]" value="{{ old('notes.'.$check['id']) }}" placeholder="If it failed, what did you see?">
                    </div>
                @endforeach
            </section>
        @endforeach

        <section class="test-card field">
            <h2>Anything else</h2>
            <textarea name="summary" rows="4" placeholder="Optional. Tell the administrator anything the list did not cover.">{{ old('summary') }}</textarea>
        </section>

        <div class="progress">
            <span id="progress">Mark the steps above, then send.</span>
            <button class="go" type="submit">Send the result</button>
        </div>
    </form>
</div>
<script>
(function () {
    var form = document.getElementById('system-test');
    var out = document.getElementById('progress');
    var groups = {};
    Array.prototype.forEach.call(form.querySelectorAll('input[type=radio]'), function (input) {
        groups[input.name] = true;
        input.addEventListener('change', paint);
    });
    function paint() {
        var total = Object.keys(groups).length, answered = 0, fails = 0, works = 0;
        Object.keys(groups).forEach(function (name) {
            var picked = form.querySelector('input[name="'+name+'"]:checked');
            if (!picked) return;
            answered++;
            if (picked.value === 'fails') fails++;
            if (picked.value === 'works') works++;
        });
        out.textContent = answered + ' of ' + total + ' marked · ' + works + ' working · ' + fails + ' not working';
    }
    paint();
})();
</script>
@endsection
