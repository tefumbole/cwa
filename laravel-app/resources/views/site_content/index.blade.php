@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-1"><i class="dripicons-web"></i> Site Content</h4>
                <p class="text-muted">Manage what appears on the site and in what order.</p>

                @if(session('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif

                <div class="mb-4">
                    <a class="btn {{ $tab == 'landing-menu' ? 'btn-primary' : 'btn-outline-primary' }} mr-2 mb-2" href="{{ url('/admin/site-content?tab=landing-menu') }}">Landing Menu</a>
                    <a class="btn {{ $tab == 'side-menu' ? 'btn-primary' : 'btn-outline-primary' }} mb-2" href="{{ url('/admin/site-content?tab=side-menu') }}">Side Menu</a>
                </div>

                @if($tab == 'side-menu')
                    @php $items = $side; $order = $sideOrder; $action = route('site-content.side-menu'); $heading = 'Side Menu — Order'; $hint = 'Reorder the admin sidebar with the arrows, then press Save. Changes apply to the sidebar on the left.'; @endphp
                @else
                    @php $items = $landing; $order = $landingOrder; $action = route('site-content.landing-menu'); $heading = 'Landing Menu — Order'; $hint = 'Reorder the public site header menu with the arrows, then press Save. This controls the order on the landing page.'; @endphp
                @endif

                <h5 class="mb-1">{{ $heading }}</h5>
                <p class="text-muted" style="font-size:13px;">{{ $hint }}</p>

                <form method="POST" action="{{ $action }}">
                    @csrf
                    <ul class="list-group reorder-list" id="reorder-list" style="max-width:640px;">
                        @foreach($order as $key)
                            @if(isset($items[$key]))
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $items[$key] }}</span>
                                    <span class="reorder-actions">
                                        <input type="hidden" name="order[]" value="{{ $key }}">
                                        <button type="button" class="btn btn-sm btn-light move-up" title="Move up">&#9650;</button>
                                        <button type="button" class="btn btn-sm btn-light move-down" title="Move down">&#9660;</button>
                                    </span>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                    <button type="submit" class="btn btn-primary mt-3">Save</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
(function () {
    var list = document.getElementById('reorder-list');
    if (!list) return;
    list.addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        var li = btn.closest('li');
        if (!li) return;
        if (btn.classList.contains('move-up')) {
            var prev = li.previousElementSibling;
            if (prev) list.insertBefore(li, prev);
        } else if (btn.classList.contains('move-down')) {
            var next = li.nextElementSibling;
            if (next) list.insertBefore(next, li);
        }
    });
})();
</script>
@endsection
