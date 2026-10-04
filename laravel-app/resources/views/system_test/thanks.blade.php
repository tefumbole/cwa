@extends('beyond.layout')

@section('title', 'Test result sent')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10 pb-24">
    <div class="bg-white border border-[#e7e1d4] rounded-2xl shadow-sm p-6 md:p-8">
        <p class="uppercase tracking-[.16em] text-xs font-extrabold text-[#8a6d1d]">CWACAM</p>
        <h1 class="text-3xl text-brand-blue mt-1 mb-2" style="font-family: Fraunces, Georgia, serif;">Result sent</h1>
        <p>Thank you, {{ $report['tester_name'] }}. Report <strong>{{ $report['id'] }}</strong>.</p>
        @if($testerSent)
            <p>A summary was sent by WhatsApp to <strong>{{ $report['tester_phone'] }}</strong>.</p>
        @else
            <p>The result is saved. WhatsApp to {{ $report['tester_phone'] }} did not go out. The administrator can still open it from Help.</p>
        @endif
        <p>A copy was sent to the administrator@if(count($adminPhones)) on WhatsApp@endif @if($mailed) and by email@endif.</p>
        <div class="grid grid-cols-3 gap-3 my-5">
            <div class="border border-[#e7e1d4] rounded-xl p-3"><strong class="block text-2xl text-brand-blue">{{ $report['counts']['works'] }}</strong>Works</div>
            <div class="border border-[#e7e1d4] rounded-xl p-3"><strong class="block text-2xl text-[#8a1f1f]">{{ $report['counts']['fails'] }}</strong>Does not work</div>
            <div class="border border-[#e7e1d4] rounded-xl p-3"><strong class="block text-2xl text-stone-500">{{ $report['counts']['skipped'] }}</strong>Not tested</div>
        </div>
        <h2 class="text-brand-blue text-xl" style="font-family: Fraunces, Georgia, serif;">Does not work</h2>
        <ul class="mt-2 space-y-2">
            @php $fails = array_filter($report['rows'], function ($row) { return $row['result'] === 'fails'; }); @endphp
            @forelse($fails as $row)
                <li class="border-l-4 border-[#D4AF37] pl-3"><strong>{{ $row['section'] }}.</strong> {{ $row['text'] }}@if($row['note'] !== '') — {{ $row['note'] }}@endif</li>
            @empty
                <li>Nothing was marked as failing.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
