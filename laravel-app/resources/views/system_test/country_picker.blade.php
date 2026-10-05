@php
    $countries = \App\Support\CountryDialCodes::all();
    $selectedCode = old('country_code', $selected ?? '+237');
    if (! isset($countries[$selectedCode])) {
        $selectedCode = '+237';
    }
    $selectedName = trim(preg_replace('/\s*\(\+\d+\)\s*$/', '', $countries[$selectedCode]));
@endphp
<div class="country-pick" data-country-picker>
    <input type="hidden" name="country_code" value="{{ $selectedCode }}" @if(!empty($pickerId)) id="{{ $pickerId }}" @endif>
    <button type="button" class="country-trigger" aria-haspopup="listbox" aria-expanded="false">
        <span class="country-code">{{ $selectedCode }}</span>
        <span class="country-name">{{ $selectedName }}</span>
        <span class="country-chevron" aria-hidden="true"></span>
    </button>
    <div class="country-panel" hidden>
        <input type="search" class="country-search" placeholder="Search country or code" autocomplete="off" aria-label="Search country">
        <ul class="country-list" role="listbox">
            @foreach($countries as $code => $label)
                @php $name = trim(preg_replace('/\s*\(\+\d+\)\s*$/', '', $label)); @endphp
                <li>
                    <button type="button" class="country-option{{ $code === $selectedCode ? ' is-selected' : '' }}" data-code="{{ $code }}" data-name="{{ $name }}" role="option">
                        <span class="country-code">{{ $code }}</span>
                        <span>{{ $name }}</span>
                    </button>
                </li>
            @endforeach
        </ul>
        <p class="country-empty" hidden>No country matches that search.</p>
    </div>
</div>
