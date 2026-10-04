<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CWACAM system test</title>
    <style>
        :root { --navy:#0b3f90; --gold:#d4af37; --ink:#1a1f2e; --line:#e6e1d6; --fail:#8a1f1f; --ok:#0f6b4c; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Nunito, system-ui, sans-serif; background:#f6f3ec; color:var(--ink); }
        header { background:var(--navy); color:#fff; padding:22px 16px; }
        header h1 { margin:0 0 6px; font-size:26px; }
        header p { margin:0; max-width:760px; line-height:1.45; color:rgba(255,255,255,.88); }
        main { max-width:860px; margin:0 auto; padding:18px 14px 48px; }
        .card { background:#fff; border:1px solid var(--line); border-radius:14px; padding:16px 16px 8px; margin-bottom:16px; }
        h2 { margin:0 0 6px; color:var(--navy); font-size:18px; }
        .intro { color:#5c6570; margin:0 0 12px; }
        .check { border-top:1px solid #f0ece4; padding:12px 0; }
        .check p { margin:0 0 8px; line-height:1.4; }
        .choices { display:flex; flex-wrap:wrap; gap:8px; }
        .choices label { border:1px solid var(--line); border-radius:999px; padding:6px 12px; cursor:pointer; font-size:14px; }
        .choices input { margin-right:6px; }
        .note { width:100%; margin-top:8px; border:1px solid var(--line); border-radius:8px; padding:8px 10px; font:inherit; }
        .who label { display:block; font-weight:700; margin:8px 0 4px; }
        .who input, .who textarea { width:100%; border:1px solid var(--line); border-radius:8px; padding:10px; font:inherit; }
        .submit { background:var(--navy); color:#fff; border:0; border-radius:10px; padding:12px 18px; font-weight:800; cursor:pointer; }
        .bar { position:sticky; bottom:0; background:#fff; border-top:1px solid var(--line); padding:10px 14px; display:flex; justify-content:space-between; gap:12px; align-items:center; }
        .hp { position:absolute; left:-9999px; }
        .err { background:#ffe5e5; color:var(--fail); padding:10px 12px; border-radius:8px; }
    </style>
</head>
<body>
<header>
    <h1>CWACAM system test</h1>
    <p>Work through the list. Mark what works and what does not. When you submit, the result is emailed to {{ implode(', ', $reportTo) ?: 'the site administrator' }}.</p>
</header>
<main>
    @if($errors->any())
        <div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif
    <form method="POST" action="{{ route('system-test.store') }}" id="system-test">
        @csrf
        <div class="hp" aria-hidden="true">
            <label>Company website<input type="text" name="company_website" tabindex="-1" autocomplete="off"></label>
        </div>
        <section class="card who">
            <h2>Who is testing</h2>
            <label for="tester_name">Your name</label>
            <input id="tester_name" name="tester_name" required value="{{ old('tester_name') }}" placeholder="Name">
            <label for="tester_email">Your email, if you want a reply</label>
            <input id="tester_email" name="tester_email" type="email" value="{{ old('tester_email') }}" placeholder="Optional">
        </section>

        @foreach($sections as $section)
            <section class="card">
                <h2>{{ $section['title'] }}</h2>
                <p class="intro">{{ $section['intro'] }}</p>
                @foreach($section['checks'] as $check)
                    <div class="check">
                        <p>{{ $check['text'] }}</p>
                        <div class="choices">
                            @foreach(['works' => 'Works', 'fails' => 'Does not work', 'skipped' => 'Not tested'] as $value => $label)
                                <label>
                                    <input type="radio" name="checks[{{ $check['id'] }}]" value="{{ $value }}" @if(old('checks.'.$check['id']) === $value) checked @endif>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        <input class="note" type="text" name="notes[{{ $check['id'] }}]" value="{{ old('notes.'.$check['id']) }}" placeholder="What happened, if it failed">
                    </div>
                @endforeach
            </section>
        @endforeach

        <section class="card who">
            <h2>Overall note</h2>
            <textarea name="summary" rows="4" placeholder="Anything else the administrator should know">{{ old('summary') }}</textarea>
        </section>
        <div class="bar">
            <span id="progress">Not started</span>
            <button class="submit" type="submit">Send the result</button>
        </div>
    </form>
</main>
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
        var total = Object.keys(groups).length;
        var answered = 0, fails = 0, works = 0;
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
</body>
</html>
