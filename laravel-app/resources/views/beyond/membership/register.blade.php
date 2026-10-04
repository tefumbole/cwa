@extends('beyond.layout')

@section('title', __('cwa.membership.page_title'))
@section('meta_description', __('cwa.membership.page_title'))

@php
    $memberGroup = request('group');
    if (! in_array($memberGroup, ['cameroon', 'diaspora'], true)) {
        $memberGroup = old('country') === 'Cameroon' ? 'cameroon' : (old('country') ? 'diaspora' : '');
    }
    $startOnDetails = $errors->any() || in_array($memberGroup, ['cameroon', 'diaspora'], true);
    $formCountries = \App\Support\CwaBranches::formCountries();
    $diasporaCountries = [];
    foreach ($formCountries as $countryName) {
        if ($countryName !== 'Cameroon') {
            $diasporaCountries[] = $countryName;
        }
    }
    $startCountry = old('country', $memberGroup === 'diaspora' ? '' : 'Cameroon');
    if ($memberGroup === 'diaspora' && ! old('country')) {
        $startCountry = '';
    }
    $startDial = old('country_code', $memberGroup === 'diaspora' ? '+1' : '+237');
    $dialByCountry = [];
    foreach (array_merge(['Cameroon', 'North America'], $diasporaCountries) as $dialName) {
        $code = \App\Support\CountryDialCodes::codeForName($dialName);
        if ($code) {
            $dialByCountry[$dialName] = $code;
        }
    }
@endphp

@push('head')
<style>
    .mship-page { max-width: 38rem; margin: 0 auto; padding: 1.1rem 0.9rem 5.5rem; }
    .mship-back {
        display: inline-flex; align-items: center; gap: 0.35rem;
        color: #003D82; font-weight: 700; font-size: 0.88rem; text-decoration: none; margin-bottom: 0.85rem;
    }
    .mship-back:hover { color: #D4AF37; }
    .mship-page h1 {
        margin: 0 0 1.15rem;
        font-size: clamp(1.7rem, 4vw, 2.15rem);
        font-weight: 800;
        color: #003D82;
        letter-spacing: -0.03em;
    }
    .mship-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.4rem;
        list-style: none;
        margin: 0 0 1.35rem;
        padding: 0 0 1.05rem;
        border-bottom: 1px solid #f0ebe1;
    }
    .mship-steps li {
        display: flex; flex-direction: column; align-items: center; gap: 0.35rem;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;
        color: #94a3b8;
    }
    .mship-steps i {
        width: 1.85rem; height: 1.85rem; border-radius: 999px;
        display: flex; align-items: center; justify-content: center;
        background: #fff; border: 1.5px solid #e7e0d4; color: #94a3b8;
        font-style: normal; font-size: 0.75rem;
    }
    .mship-steps li.is-on { color: #003D82; }
    .mship-steps li.is-on i { background: #003D82; border-color: #003D82; color: #D4AF37; }
    .mship-steps li.is-done { color: #003D82; }
    .mship-steps li.is-done i { background: #D4AF37; border-color: #D4AF37; color: #003D82; }
    .mship-card {
        background: #fff;
        border: 1px solid #ece6db;
        border-radius: 1.5rem;
        padding: 1.4rem 1.25rem 1.55rem;
        box-shadow: 0 20px 50px rgba(26, 31, 46, 0.07);
    }
    @media (min-width: 640px) { .mship-card { padding: 1.7rem 1.7rem 1.8rem; } }
    .mship-kicker {
        margin: 0 0 0.35rem;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.16em;
        text-transform: uppercase; color: #D4AF37;
    }
    .mship-card h2 { margin: 0 0 0.35rem; font-size: 1.25rem; font-weight: 800; color: #003D82; }
    .mship-lead { margin: 0 0 1.15rem; color: #64748b; font-size: 0.9rem; line-height: 1.5; }
    .mship-field { margin-top: 1.15rem; }
    .mship-label {
        display: block; margin-bottom: 0.4rem;
        font-size: 0.8rem; font-weight: 800; color: #003D82;
    }
    .mship-label em { color: #dc2626; font-style: normal; }
    .mship-hint { margin: 0.4rem 0 0; font-size: 0.78rem; color: #64748b; line-height: 1.45; }
    .mship-input {
        width: 100%;
        border-radius: 0.9rem;
        border: 1.5px solid #e7e0d4;
        background: #fbfaf7;
        padding: 0.78rem 0.95rem;
        font-size: 1rem;
        color: #1A1F2E;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .mship-input:focus {
        outline: none; border-color: #D4AF37; background: #fff;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.2);
    }
    .mship-combo {
        display: flex; align-items: stretch;
        border-radius: 0.9rem; border: 1.5px solid #e7e0d4; background: #fbfaf7;
        overflow: visible; position: relative;
    }
    .mship-combo:focus-within {
        border-color: #D4AF37; background: #fff;
        box-shadow: 0 0 0 4px rgba(212,175,55,0.2);
    }
    .mship-combo .mship-cc {
        min-height: 3rem; padding: 0 0.85rem;
        background: transparent; border: 0; border-right: 1px solid #e7e0d4;
        font-size: 0.82rem; font-weight: 800; color: #003D82; white-space: nowrap;
    }
    .mship-combo input[type="tel"] {
        width: 100%; border: 0; background: transparent; outline: none;
        padding: 0.78rem 0.95rem; font-size: 1rem;
    }
    .mship-menu {
        position: absolute; left: 0; top: calc(100% + 0.35rem); z-index: 30;
        width: 18rem; max-height: 16rem; overflow: hidden;
        background: #fff; border: 1px solid #e7e0d4; border-radius: 1rem;
        box-shadow: 0 16px 40px rgba(26,31,46,0.12);
    }
    .mship-menu.is-wide { width: 100%; }
    .mship-menu input { width: 100%; padding: 0.65rem 0.85rem; border: 0; border-bottom: 1px solid #f0ebe1; outline: none; font-size: 1rem; }
    .mship-menu button { width: 100%; text-align: left; padding: 0.5rem 0.85rem; font-size: 0.85rem; border: 0; background: transparent; color: #334155; }
    .mship-menu button:hover, .mship-menu button.is-on { background: rgba(212,175,55,0.16); color: #003D82; font-weight: 800; }
    .mship-pick-wrap { position: relative; }
    .mship-pick {
        width: 100%;
        min-height: 3.1rem;
        border-radius: 0.9rem;
        border: 1.5px solid #e7e0d4;
        background: #fbfaf7;
        padding: 0.78rem 2.2rem 0.78rem 0.95rem;
        font-size: 1rem;
        color: #1A1F2E;
    }
    .mship-pick::placeholder { color: #94a3b8; }
    .mship-pick:focus { outline: none; border-color: #D4AF37; background: #fff; box-shadow: 0 0 0 4px rgba(212,175,55,0.2); }
    .mship-pick-wrap::after {
        content: "";
        position: absolute;
        top: 50%;
        right: 0.9rem;
        width: 0.45rem;
        height: 0.45rem;
        border-right: 1.5px solid #64748b;
        border-bottom: 1.5px solid #64748b;
        transform: translateY(-70%) rotate(45deg);
        pointer-events: none;
    }
    .mship-check {
        display: flex; align-items: center; gap: 0.55rem;
        margin-top: 0.45rem; color: #475569; font-size: 0.9rem; cursor: pointer;
    }
    .mship-check input { width: 1.05rem; height: 1.05rem; accent-color: #003D82; }
    .mship-grid { display: grid; gap: 0.85rem; margin-top: 1.15rem; }
    @media (min-width: 640px) {
        .mship-grid { grid-template-columns: 1fr 1fr; }
        .mship-grid .mship-span-2 { grid-column: 1 / -1; }
    }
    .mship-choices { display: grid; gap: 0.7rem; }
    @media (min-width: 520px) { .mship-choices { grid-template-columns: 1fr 1fr; } }
    .mship-choice {
        display: flex; flex-direction: column; align-items: flex-start; gap: 0.35rem;
        padding: 1rem 1rem 1.05rem; border-radius: 1rem;
        border: 1.5px solid #e7e0d4; background: #fbfaf7;
        text-align: left; font-weight: 800; color: #003D82;
        transition: border-color .15s, background .15s, box-shadow .15s;
    }
    .mship-choice:hover { border-color: #D4AF37; }
    .mship-choice.is-on {
        border-color: #D4AF37; background: rgba(212,175,55,0.14);
        box-shadow: 0 0 0 4px rgba(212,175,55,0.12);
    }
    .mship-choice small { font-weight: 600; color: #64748b; font-size: 0.75rem; }
    @media (max-width: 420px) {
        .mship-steps li { font-size: 0; gap: 0.2rem; }
        .mship-steps i { font-size: 0.75rem; }
    }
    @media (max-width: 520px) {
        .mship-combo { flex-direction: column; }
        .mship-combo .mship-cc { border-right: 0; border-bottom: 1px solid #e7e0d4; width: 100%; justify-content: flex-start; }
        .mship-menu { width: 100%; left: 0; }
        .mship-actions { flex-direction: column; }
    }
    .mship-actions { display: flex; gap: 0.7rem; margin-top: 1.4rem; }
    .mship-btn {
        flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;
        min-height: 3.1rem; padding: 0.7rem 1.1rem; border-radius: 999px;
        font-weight: 800; text-decoration: none; border: 0; cursor: pointer;
    }
    .mship-btn.gold { background: #D4AF37; color: #003D82; box-shadow: 0 10px 22px rgba(212,175,55,0.28); }
    .mship-btn.gold:hover { background: #c4a030; }
    .mship-btn.ghost { background: #fff; color: #003D82; border: 1.5px solid #d6deea; }
    .mship-btn.ghost:hover { border-color: #003D82; }
    .mship-error { margin-bottom: 1rem; border-radius: 1rem; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; padding: 0.75rem 1rem; font-size: 0.88rem; }
    .mship-chip { display: inline-flex; border-radius: 999px; padding: 0.15rem 0.65rem; font-size: 0.7rem; font-weight: 800; }
</style>
@endpush

@section('content')
<div class="mship-page">
    <a href="{{ route('beyond.membership', ['open' => 1]) }}" class="mship-back">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        {{ __('cwa.membership.back') }}
    </a>
    <h1>{{ __('cwa.membership.page_title') }}</h1>

    @if ($errors->any())
        <div class="mship-error">
            <ul class="list-disc pl-5 space-y-1 mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('beyond.membership.store') }}" enctype="multipart/form-data"
          class="mship-card" id="membership-registration-form"
          x-data="membershipWizard()">
        @csrf
        <ol class="mship-steps" aria-hidden="true" x-show="step !== 'where'" x-cloak>
            <li :class="{ 'is-on': stage() === 1, 'is-done': stage() > 1 }"><i>1</i>{{ __('cwa.membership.step_details') }}</li>
            <li :class="{ 'is-on': stage() === 2, 'is-done': stage() > 2 }"><i>2</i>{{ __('cwa.membership.step_id') }}</li>
            <li :class="{ 'is-on': stage() === 3, 'is-done': stage() > 3 }"><i>3</i>{{ __('cwa.membership.step_photo') }}</li>
            <li :class="{ 'is-on': stage() === 4, 'is-done': stage() > 4 }"><i>4</i>{{ __('cwa.membership.step_sign') }}</li>
        </ol>
        <input type="hidden" name="id_stored_path" x-model="idPath">
        <input type="hidden" name="selfie_stored" x-model="selfiePath">
        <input type="hidden" name="signature" id="membership_signature_input" x-model="signature">
        <input type="hidden" name="id_type" :value="idType">
        <input type="file" name="id_front" id="id-file-input" accept="image/*" class="sr-only">
        <input type="file" name="selfie" id="membership-selfie-input" accept="image/*" class="sr-only">

        {{-- Cameroon or Diaspora --}}
        <div x-show="step === 'where'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.subscribe') }}</p>
            <h2>{{ __('cwa.membership.where_title') }}</h2>
            <p class="mship-lead">{{ __('cwa.membership.where_lead') }}</p>
            <div class="mship-choices">
                <button type="button" @click="chooseGroup('cameroon')" class="mship-choice">
                    {{ __('cwa.membership.where_cameroon') }}
                    <small>{{ __('cwa.membership.where_cameroon_hint') }}</small>
                </button>
                <button type="button" @click="chooseGroup('diaspora')" class="mship-choice">
                    {{ __('cwa.membership.where_diaspora') }}
                    <small>{{ __('cwa.membership.where_diaspora_hint') }}</small>
                </button>
            </div>
        </div>

        {{-- Phone --}}
        <div x-show="step === 'phone'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.step_details') }}</p>
            <div class="mship-field" style="margin-top:0">
                <label class="mship-label">{{ __('cwa.membership.phone') }} <em>*</em></label>
                <div class="mship-combo">
                    <input type="hidden" name="country_code" x-model="countryCode">
                    <div class="relative shrink-0" @click.outside="ccOpen = false">
                        <button type="button" class="mship-cc" @click="ccOpen = !ccOpen; ccQuery = ''; $nextTick(() => { var el = $refs.ccSearch; if (el) el.focus(); })">
                            <span x-text="countryLabel(countryCode)"></span>
                        </button>
                        <div x-show="ccOpen" x-cloak class="mship-menu">
                            <input x-ref="ccSearch" x-model="ccQuery" type="search" placeholder="{{ __('cwa.membership.country_search') }}">
                            <ul class="max-h-52 overflow-auto m-0 p-0 list-none">
                                <template x-for="c in filteredCountries(ccQuery)" :key="c.code">
                                    <li>
                                        <button type="button" @click="pickCountry(c.code)"
                                                :class="countryCode === c.code ? 'is-on' : ''"
                                                x-text="c.label"></button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="filteredCountries(ccQuery).length === 0" class="px-3 py-2 text-xs text-stone-400">{{ __('cwa.membership.country_empty') }}</p>
                        </div>
                    </div>
                    <input required name="phone" x-model="phone" @input="normalizePhone(); scheduleLookup()" type="tel"
                           inputmode="numeric" autocomplete="tel-national" placeholder="6XX XXX XXX">
                </div>
                <p class="mship-hint">{{ __('cwa.membership.phone_hint') }}</p>
                <p x-show="looking" x-cloak class="mship-hint">{{ __('cwa.membership.checking') }}</p>
            </div>

            <div class="mship-field">
                <label class="mship-label">{{ __('cwa.membership.name') }} <em>*</em></label>
                <input required name="name" x-model="fullName" class="mship-input">
                <p class="mship-hint">{{ __('cwa.membership.name_hint') }}</p>
            </div>

            <div class="mship-field">
                <label class="mship-label">{{ __('cwa.membership.whatsapp_opt') }}</label>
                <label class="mship-check">
                    <input type="checkbox" x-model="waSame"> {{ __('cwa.membership.whatsapp_same') }}
                </label>
                <div class="mship-combo mt-2" x-show="!waSame" x-cloak>
                    <input type="hidden" name="whatsapp_country" x-model="waCountry">
                    <div class="relative shrink-0" @click.outside="waOpen = false">
                        <button type="button" class="mship-cc" @click="waOpen = !waOpen; waQuery = ''">
                            <span x-text="countryLabel(waCountry)"></span>
                        </button>
                        <div x-show="waOpen" x-cloak class="mship-menu">
                            <input x-model="waQuery" type="search" placeholder="{{ __('cwa.membership.country_search') }}">
                            <ul class="max-h-52 overflow-auto m-0 p-0 list-none">
                                <template x-for="c in filteredCountries(waQuery)" :key="'wa-'+c.code">
                                    <li>
                                        <button type="button" @click="waCountry = c.code; waOpen = false"
                                                :class="waCountry === c.code ? 'is-on' : ''"
                                                x-text="c.label"></button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <input name="whatsapp_phone" x-model="waPhone" type="tel" inputmode="numeric">
                </div>
            </div>

            <div class="mship-grid">
                <div>
                    <label class="mship-label">{{ __('cwa.join.diocese') }} <em>*</em></label>
                    <div x-show="hasDioceseList()" class="mship-pick-wrap" @click.outside="closePick('diocese')">
                        <input type="hidden" name="diocese" :value="diocese" :disabled="!hasDioceseList()">
                        <input type="search" class="mship-pick" autocomplete="off" enterkeyhint="search"
                               x-ref="dioceseSearch" x-model="dioceseQuery"
                               :placeholder="diocesePlaceholder"
                               @focus="openPick('diocese')"
                               @keydown.escape.prevent="closePick('diocese')"
                               @keydown.enter.prevent="pickFirst('diocese')">
                        <div x-show="dioceseOpen" x-cloak class="mship-menu is-wide">
                            <ul class="max-h-52 overflow-auto m-0 p-0 list-none">
                                <template x-for="(name, i) in filteredDioceses()" :key="'d-'+i+'-'+name">
                                    <li>
                                        <button type="button" @mousedown.prevent="pickDiocese(name)"
                                                :class="diocese === name ? 'is-on' : ''"
                                                x-text="name"></button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="filteredDioceses().length === 0" class="px-3 py-2 text-xs text-stone-400">{{ __('cwa.join.diocese_empty') }}</p>
                        </div>
                    </div>
                    <input x-show="!hasDioceseList()" x-cloak required name="diocese" x-model="diocese" class="mship-input" placeholder="{{ __('cwa.join.diocese_other') }}" :disabled="hasDioceseList()">
                </div>
                <div>
                    <label class="mship-label">{{ __('cwa.join.parish') }} <em>*</em></label>
                    <input required name="parish" x-model="parish" class="mship-input">
                </div>
                <div x-show="branchKind === 'cameroon'">
                    <label class="mship-label">{{ __('cwa.join.region') }} <em>*</em></label>
                    <div class="mship-pick-wrap" @click.outside="closePick('region')">
                        <input type="hidden" name="region" :value="region" :disabled="branchKind !== 'cameroon'">
                        <input type="search" class="mship-pick" autocomplete="off" enterkeyhint="search"
                               x-ref="regionSearch" x-model="regionQuery"
                               :placeholder="regionPlaceholder"
                               @focus="openPick('region')"
                               @keydown.escape.prevent="closePick('region')"
                               @keydown.enter.prevent="pickFirst('region')">
                        <div x-show="regionOpen" x-cloak class="mship-menu is-wide">
                            <ul class="max-h-52 overflow-auto m-0 p-0 list-none">
                                <template x-for="name in filteredRegions()" :key="'r-'+name">
                                    <li>
                                        <button type="button" @mousedown.prevent="pickRegion(name)"
                                                :class="region === name ? 'is-on' : ''"
                                                x-text="name"></button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="filteredRegions().length === 0" class="px-3 py-2 text-xs text-stone-400">{{ __('cwa.join.region_empty') }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="mship-label">{{ __('cwa.join.country') }} <em>*</em></label>
                    <input type="hidden" name="country" :value="country">
                    <p x-show="branchKind === 'cameroon'" class="mship-input mb-0 flex items-center">{{ __('cwa.membership.where_cameroon') }}</p>
                    <div x-show="branchKind !== 'cameroon'" class="mship-pick-wrap" @click.outside="closePick('country')">
                        <input type="search" class="mship-pick" autocomplete="off" enterkeyhint="search"
                               x-ref="countrySearch" x-model="countryQuery"
                               :placeholder="countryPlaceholder"
                               @focus="openPick('country')"
                               @keydown.escape.prevent="closePick('country')"
                               @keydown.enter.prevent="pickFirst('country')">
                        <div x-show="countryOpen" x-cloak class="mship-menu is-wide">
                            <ul class="max-h-52 overflow-auto m-0 p-0 list-none">
                                <template x-for="name in filteredResidenceCountries()" :key="'c-'+name">
                                    <li>
                                        <button type="button" @mousedown.prevent="pickResidence(name)"
                                                :class="country === name ? 'is-on' : ''"
                                                x-text="name"></button>
                                    </li>
                                </template>
                            </ul>
                            <p x-show="filteredResidenceCountries().length === 0" class="px-3 py-2 text-xs text-stone-400">{{ __('cwa.join.country_empty') }}</p>
                        </div>
                    </div>
                </div>
                <div class="mship-span-2">
                    <label class="mship-label">{{ __('cwa.join.address') }} <em>*</em></label>
                    <input required name="address" x-model="address" class="mship-input" autocomplete="street-address">
                </div>
                <div>
                    <label class="mship-label">{{ __('cwa.join.city') }} <em>*</em></label>
                    <input required name="city" x-model="city" class="mship-input" autocomplete="address-level2">
                </div>
                <div>
                    <label class="mship-label">{{ __('cwa.join.state') }} <em>*</em></label>
                    <input required name="state" x-model="state" class="mship-input" autocomplete="address-level1">
                </div>
            </div>
            <div class="mship-actions">
                <button type="button" @click="step = 'where'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="goIdType()" class="mship-btn gold">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- ID type --}}
        <div x-show="step === 'idtype'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.step_id') }}</p>
            <h2>{{ __('cwa.membership.id_step_title') }}</h2>
            <p class="mship-lead">{{ __('cwa.membership.id_step_hint') }}</p>
            <p class="mship-label">{{ __('cwa.membership.id_which') }}</p>
            <div class="mship-choices">
                <button type="button" @click="idType = 'national_id'" class="mship-choice" :class="{ 'is-on': idType === 'national_id' }">
                    {{ __('cwa.membership.id_national') }}
                </button>
                <button type="button" @click="idType = 'passport'" class="mship-choice" :class="{ 'is-on': idType === 'passport' }">
                    {{ __('cwa.membership.id_passport') }}
                </button>
            </div>
            <div class="mship-actions">
                <button type="button" @click="step = 'phone'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="idType && (step = 'idhow')" class="mship-btn gold">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Upload or scan --}}
        <div x-show="step === 'idhow'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.step_id') }}</p>
            <p class="mship-label">{{ __('cwa.membership.id_how') }}</p>
            <div class="mship-choices">
                <button type="button" @click="chooseUpload()" class="mship-choice">{{ __('cwa.membership.id_upload') }}</button>
                <button type="button" @click="chooseScan()" class="mship-choice is-on">{{ __('cwa.membership.id_scan') }}</button>
            </div>
            <div x-show="qrSrc || handoffUrl" x-cloak class="mt-6 text-center">
                <p class="mship-hint mb-3">{{ __('cwa.membership.id_qr_hint') }}</p>
                <img x-show="qrSrc" :src="qrSrc" alt="" class="mx-auto w-48 h-48 bg-white p-2 rounded-xl border">
                <p class="text-xs text-slate-400 mt-2 break-all" x-text="handoffUrl"></p>
                <p class="text-sm text-brand-blue mt-3" x-text="waitingId ? '{{ __('cwa.membership.id_waiting') }}' : ''"></p>
            </div>
            <p x-show="reading" class="mship-hint mt-4">{{ __('cwa.membership.id_reading') }}</p>
            <div class="mship-actions">
                <button type="button" @click="step = 'idtype'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
            </div>
        </div>

        {{-- ID fields --}}
        <div x-show="step === 'idfields'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.step_id') }}</p>
            <div class="mship-field" style="margin-top:0">
                <label class="mship-label">{{ __('cwa.membership.id_name') }}</label>
                <input name="ocr_name" x-model="idName" @input="if (idName) fullName = idName" class="mship-input">
            </div>
            <div class="mship-field">
                <label class="mship-label">{{ __('cwa.membership.id_issue_date') }}</label>
                <input name="id_issue_date" x-model="issueDate" class="mship-input">
            </div>
            <div class="mship-field">
                <label class="mship-label">{{ __('cwa.membership.id_issue_place') }}</label>
                <input name="id_issue_place" x-model="issuePlace" class="mship-input">
            </div>
            <div class="mship-actions">
                <button type="button" @click="step = 'idhow'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="step = 'selfie'" class="mship-btn gold">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Selfie --}}
        <div x-show="step === 'selfie'" x-cloak class="text-center">
            <p class="mship-kicker">{{ __('cwa.membership.step_photo') }}</p>
            <button type="button" data-cwa-selfie-open class="mship-btn gold" style="width:auto; margin: 0 auto;">
                {{ __('cwa.membership.selfie_btn') }}
            </button>
            <p class="text-sm text-emerald-700 mt-3" id="membership-selfie-status">{{ __('cwa.membership.no_file') }}</p>
            <div class="cwa-selfie-preview mx-auto mt-4" id="wizard-selfie-wrap" x-show="hasSelfie" x-cloak>
                <img id="membership-selfie-preview" alt="" class="hidden">
                <span id="membership-selfie-placeholder" class="cwa-selfie-placeholder hidden"></span>
            </div>
            <div class="mship-actions">
                <button type="button" @click="step = 'idfields'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
                <button type="button" @click="goSign()" class="mship-btn gold">{{ __('cwa.membership.next') }}</button>
            </div>
        </div>

        {{-- Sign --}}
        <div x-show="step === 'sign'" x-cloak>
            <p class="mship-kicker">{{ __('cwa.membership.step_sign') }}</p>
            <p class="font-extrabold text-brand-blue text-lg" x-text="fullName"></p>
            <button type="button" @click="openSign()" class="mship-btn gold mt-4" style="width:100%">
                {{ __('cwa.membership.sign_btn') }}
            </button>
            <div x-show="signPanel" x-cloak class="mt-4 border-2 border-dashed border-brand-blue/40 rounded-xl p-3 bg-[#fbfaf7]">
                <canvas id="membership-signature-pad" width="500" height="160" class="w-full bg-white rounded-lg touch-none"></canvas>
                <button type="button" @click="clearPad()" class="mt-2 text-sm font-semibold text-brand-blue underline">Clear</button>
            </div>
            <div x-show="signQr" x-cloak class="mt-6 text-center">
                <p class="mship-hint mb-3">{{ __('cwa.membership.sign_qr_hint') }}</p>
                <img :src="signQr" alt="" class="mx-auto w-48 h-48 bg-white p-2 rounded-xl border">
            </div>
            <p x-show="signature" class="text-sm text-emerald-700 mt-3">{{ __('cwa.membership.sign_ready') }}</p>
            <img x-show="signature" :src="signature" alt="" class="mt-2 max-h-20 border rounded">
            <div class="mship-actions">
                <button type="button" @click="step = 'selfie'" class="mship-btn ghost">{{ __('cwa.membership.prev') }}</button>
                <button type="submit" class="mship-btn gold">{{ __('cwa.membership.submit') }}</button>
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
        step: @json($startOnDetails ? 'phone' : 'where'),
        branchKind: @json($memberGroup),
        stage: function () {
            if (this.step === 'where') return 0;
            if (this.step === 'phone') return 1;
            if (this.step === 'selfie') return 3;
            if (this.step === 'sign') return 4;
            return 2;
        },
        countryCode: @json($startDial),
        countries: @json(collect($countryCodes)->map(function ($label, $code) { return ['code' => $code, 'label' => $label]; })->values()),
        ccOpen: false,
        ccQuery: '',
        waOpen: false,
        waQuery: '',
        phone: @json(old('phone', '')),
        fullName: @json(old('name', '')),
        looking: false,
        timer: null,
        last: '',
        waSame: true,
        waCountry: @json($startDial),
        waPhone: '',
        diocese: @json(old('diocese', '')),
        parish: @json(old('parish', '')),
        region: @json(old('region', '')),
        country: @json($startCountry),
        dioceseMap: @json(\App\Support\CwaBranches::formMap()),
        featuredAll: @json($formCountries),
        featuredDiaspora: @json($diasporaCountries),
        extraCountries: @json(array_values(array_diff(\App\Support\CountryDialCodes::names(), $formCountries))),
        regions: @json(\App\Support\CameroonRegions::all()),
        dioceseOpen: false,
        dioceseQuery: @json(old('diocese', '')),
        diocesePlaceholder: @json(__('cwa.join.diocese_search')),
        regionOpen: false,
        regionQuery: @json(old('region', '')),
        regionPlaceholder: @json(__('cwa.join.region_search')),
        countryOpen: false,
        countryQuery: @json($startCountry),
        countryPlaceholder: @json(__('cwa.join.country_search')),
        address: @json(old('address', '')),
        city: @json(old('city', '')),
        state: @json(old('state', '')),
        dialByCountry: @json($dialByCountry),
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
            this.$nextTick(function () {
                if (window.lucide) window.lucide.createIcons();
            });
        },
        csrf: function () {
            var el = document.querySelector('#membership-registration-form input[name=_token]');
            return el ? el.value : '';
        },
        countryLabel: function (code) {
            var list = this.countries || [];
            for (var i = 0; i < list.length; i++) {
                if (list[i].code === code) return list[i].label;
            }
            return code || '+237';
        },
        filteredCountries: function (q) {
            var query = String(q || '').toLowerCase().trim();
            var list = this.countries || [];
            if (!query) return list;
            return list.filter(function (c) {
                return (c.label + ' ' + c.code).toLowerCase().indexOf(query) !== -1;
            });
        },
        pickCountry: function (code) {
            this.countryCode = code;
            this.ccOpen = false;
            this.ccQuery = '';
            this.last = '';
            if (this.branchKind === 'diaspora' && !this.country) {
                var name = this.countryFromDial(code);
                if (name && name !== 'Cameroon') {
                    this.country = name;
                    this.countryQuery = name;
                    this.onCountryChange();
                }
            }
            this.scheduleLookup();
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
                if (res.name && !self.fullName) self.fullName = res.name;
            }).catch(function () { self.looking = false; });
        },
        countryFromDial: function (code) {
            var map = this.dialByCountry || {};
            var keys = Object.keys(map);
            for (var i = 0; i < keys.length; i++) {
                if (map[keys[i]] === code) return keys[i];
            }
            return '';
        },
        diasporaBranchList: function () {
            var map = this.dioceseMap || {};
            var countries = this.featuredDiaspora || [];
            var names = [];
            var seen = {};
            for (var i = 0; i < countries.length; i++) {
                var list = map[countries[i]] || [];
                for (var j = 0; j < list.length; j++) {
                    if (!seen[list[j]]) {
                        seen[list[j]] = true;
                        names.push(list[j]);
                    }
                }
            }
            return names;
        },
        branchCountry: function (name) {
            var map = this.dioceseMap || {};
            var countries = this.featuredDiaspora || [];
            for (var i = 0; i < countries.length; i++) {
                var list = map[countries[i]] || [];
                if (list.indexOf(name) !== -1) return countries[i];
            }
            return '';
        },
        dioceseOptions: function () {
            var map = this.dioceseMap || {};
            var selected = map[this.country] || [];
            if (selected.length) return selected;
            if (this.branchKind === 'diaspora') {
                var fromPhone = this.countryFromDial(this.countryCode);
                if (fromPhone && fromPhone !== 'Cameroon' && map[fromPhone] && map[fromPhone].length) {
                    return map[fromPhone];
                }
                return this.diasporaBranchList();
            }
            return [];
        },
        hasDioceseList: function () {
            return this.dioceseOptions().length > 0;
        },
        filterNames: function (list, q) {
            var query = String(q || '').toLowerCase().trim();
            var items = list || [];
            if (!query) return items;
            return items.filter(function (name) {
                return String(name).toLowerCase().indexOf(query) !== -1;
            });
        },
        filteredDioceses: function () {
            return this.filterNames(this.dioceseOptions(), this.dioceseQuery);
        },
        filteredRegions: function () {
            return this.filterNames(this.regions, this.regionQuery);
        },
        featuredCountries: function () {
            if (this.branchKind === 'diaspora') return this.featuredDiaspora || [];
            if (this.branchKind === 'cameroon') return ['Cameroon'];
            return this.featuredAll || [];
        },
        filteredResidenceCountries: function () {
            var featured = this.featuredCountries();
            if (!String(this.countryQuery || '').trim()) return featured;
            return this.filterNames(featured.concat(this.extraCountries || []), this.countryQuery);
        },
        chooseGroup: function (kind) {
            this.branchKind = kind;
            if (kind === 'cameroon') {
                this.country = 'Cameroon';
                this.countryQuery = 'Cameroon';
                this.countryCode = '+237';
                this.waCountry = '+237';
            } else {
                if (this.country === 'Cameroon') {
                    this.country = '';
                    this.countryQuery = '';
                }
                this.region = '';
                this.regionQuery = '';
                this.countryCode = '+1';
                this.waCountry = '+1';
            }
            this.onCountryChange();
            this.step = 'phone';
        },
        restorePickQueries: function (except) {
            if (except !== 'diocese') {
                this.dioceseOpen = false;
                this.dioceseQuery = this.diocese || '';
            }
            if (except !== 'region') {
                this.regionOpen = false;
                this.regionQuery = this.region || '';
            }
            if (except !== 'country') {
                this.countryOpen = false;
                this.countryQuery = this.country || '';
            }
        },
        openPick: function (kind) {
            this.restorePickQueries(kind);
            if (kind === 'diocese') {
                this.dioceseOpen = true;
                this.dioceseQuery = '';
                return;
            }
            if (kind === 'region') {
                this.regionOpen = true;
                this.regionQuery = '';
                return;
            }
            this.countryOpen = true;
            this.countryQuery = '';
        },
        closePick: function (kind) {
            if (kind === 'diocese') {
                this.dioceseOpen = false;
                this.dioceseQuery = this.diocese || '';
                return;
            }
            if (kind === 'region') {
                this.regionOpen = false;
                this.regionQuery = this.region || '';
                return;
            }
            this.countryOpen = false;
            this.countryQuery = this.country || '';
        },
        pickFirst: function (kind) {
            var list = kind === 'diocese'
                ? this.filteredDioceses()
                : (kind === 'region' ? this.filteredRegions() : this.filteredResidenceCountries());
            if (!list.length) return;
            if (kind === 'diocese') this.pickDiocese(list[0]);
            else if (kind === 'region') this.pickRegion(list[0]);
            else this.pickResidence(list[0]);
        },
        pickDiocese: function (name) {
            this.diocese = name;
            this.dioceseQuery = name;
            this.dioceseOpen = false;
            if (this.branchKind === 'diaspora' && !this.country) {
                var owner = this.branchCountry(name);
                if (owner) {
                    this.country = owner;
                    this.countryQuery = owner;
                }
            }
        },
        pickRegion: function (name) {
            this.region = name;
            this.regionQuery = name;
            this.regionOpen = false;
        },
        pickResidence: function (name) {
            this.country = name;
            this.countryQuery = name;
            this.countryOpen = false;
            var code = (this.dialByCountry || {})[name];
            if (code) {
                this.countryCode = code;
                if (this.waSame) this.waCountry = code;
            }
            this.onCountryChange();
        },
        onCountryChange: function () {
            var opts = this.dioceseOptions();
            if (opts.length && opts.indexOf(this.diocese) === -1) {
                this.diocese = '';
                this.dioceseQuery = '';
            }
            this.dioceseOpen = false;
        },
        goIdType: function () {
            if (!this.phone || !this.fullName || !this.diocese || !this.parish || !this.country || !this.address || !this.city || !this.state) return;
            if (this.branchKind === 'cameroon' && !this.region) return;
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
