@extends('layout.main')

@section('content')
@php
    $shot = url('public/help');
    $publicMenu = \App\Support\SiteMenu::landingNavLinks();
    $sideItems = \App\Support\SiteMenu::sideItems();
    $sideHidden = \App\Support\SiteMenu::sideHidden();
    $adminMenu = [];
    foreach (\App\Support\SiteMenu::sideOrder() as $key) {
        if (in_array($key, $sideHidden, true) || ! isset($sideItems[$key])) {
            continue;
        }
        $adminMenu[] = $sideItems[$key];
    }
    $peopleMenu = [];
    $peopleItems = \App\Support\SiteMenu::peopleItems();
    foreach (\App\Support\SiteMenu::peopleOrder() as $key) {
        if (isset($peopleItems[$key])) {
            $peopleMenu[] = $peopleItems[$key];
        }
    }
    $settingsMenu = [];
    $settingsItems = \App\Support\SiteMenu::settingsItems();
    foreach (\App\Support\SiteMenu::settingsOrder() as $key) {
        if (isset($settingsItems[$key])) {
            $settingsMenu[] = $settingsItems[$key];
        }
    }
@endphp
<section class="help-guide">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
            <div>
                <h3 class="mb-1" style="color:#0b3f90;font-weight:800;">Help — Website testing guide</h3>
                <p class="text-muted mb-0">Use this checklist on <strong>cwacam.org</strong> before you sign off a change. Each section says what to do and what you should see.</p>
            </div>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fa fa-external-link"></i> Open the website</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <strong>Send this link to the person who will test</strong>
                <p class="mb-2">The page starts with instructions, then the checks. When they submit, the result goes to their WhatsApp number and a copy goes to the administrator. Reports are listed under <a href="{{ route('system-test.index') }}">Test results</a>.</p>
                <p class="mb-0"><a href="{{ route('system-test.show') }}" target="_blank">{{ route('system-test.show') }}</a></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <strong>Contents</strong>
                <ol class="mb-0 mt-2">
                    <li><a href="#start">Before you start</a></li>
                    <li><a href="#menus">Current menus</a></li>
                    <li><a href="#home">Homepage</a></li>
                    <li><a href="#about">About Us and leaders</a></li>
                    <li><a href="#branches">Branches</a></li>
                    <li><a href="#membership">Membership registration</a></li>
                    <li><a href="#rest">Events, gallery, contact and donate</a></li>
                    <li><a href="#mobile">Phones and small screens</a></li>
                    <li><a href="#admin-leaders">Admin: About Us Leaders</a></li>
                    <li><a href="#admin-members">Admin: Membership</a></li>
                    <li><a href="#admin-content">Admin: Site Content</a></li>
                    <li><a href="#signoff">Sign-off checklist</a></li>
                </ol>
            </div>
        </div>

        <article class="card mb-4" id="start">
            <div class="card-header bg-white"><strong>1. Before you start</strong></div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Use a normal browser window, then repeat the public pages on a phone or a narrow window.</li>
                    <li>Stay on <strong>cwacam.org</strong>. Do not test another organisation’s site.</li>
                    <li>For admin checks, sign in as an administrator. The menu item for this page is <strong>Help</strong>, and it stays at the bottom of the side menu.</li>
                    <li>Mark each box only when the result matches the expected result. If something fails, note the page address and what you clicked.</li>
                </ul>
            </div>
        </article>

        <article class="card mb-4" id="menus">
            <div class="card-header bg-white"><strong>2. Current menus</strong></div>
            <div class="card-body">
                <p>These lists follow <strong>Site Content</strong>. If you drag, rename, or hide a row and save, this page shows the new menu. Hidden rows are left out. <strong>Help</strong> stays last on the admin menu.</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>Public website</h5>
                        <ol class="mb-2">
                            @foreach($publicMenu as $link)
                                <li><a href="{{ $link['url'] }}" target="_blank">{{ $link['label'] }}</a></li>
                            @endforeach
                        </ol>
                        <p class="mb-0 small text-muted">On the homepage header, also confirm <strong>EN / FR</strong>, <strong>Donate</strong> and <strong>Login</strong>. <strong>Join CWA</strong> is the gold button on the homepage and in the phone menu. On other pages the header shows <strong>Join CWA</strong> next to Donate.</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5>Admin side menu</h5>
                        <ol class="mb-2">
                            @foreach($adminMenu as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                            <li>Internships <span class="text-muted">(when your role includes it)</span></li>
                            <li>Supervisor <span class="text-muted">(when your role includes it)</span></li>
                            <li>Help</li>
                        </ol>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>People</h5>
                        <ol class="mb-0">
                            @foreach($peopleMenu as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h5>Settings</h5>
                        <ol class="mb-0">
                            @foreach($settingsMenu as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
                <p class="mb-1"><strong>Pass when</strong> the public header and the blue admin menu match these lists, in this order.</p>
            </div>
        </article>

        <article class="card mb-4" id="home">
            <div class="card-header bg-white"><strong>3. Homepage</strong></div>
            <div class="card-body">
                <img src="{{ $shot }}/home.jpg" alt="CWACAM homepage with the main menu, Donate and Login" class="help-shot">
                <ol>
                    <li>Open <a href="{{ url('/') }}" target="_blank">the homepage</a>.</li>
                    <li>Confirm the logo and the public menu from section 2, in that order.</li>
                    <li>Confirm <strong>EN / FR</strong>, <strong>Donate</strong> and <strong>Login</strong> are visible.</li>
                    <li>Click <strong>Join CWA</strong> on the page. It should open the membership page.</li>
                    <li>Click <strong>FR</strong>, then <strong>EN</strong>. The language should switch and return.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> the page loads, the menu does not overflow the screen, and Join CWA and Donate both open the right pages.</p>
            </div>
        </article>

        <article class="card mb-4" id="about">
            <div class="card-header bg-white"><strong>4. About Us and leaders</strong></div>
            <div class="card-body">
                <img src="{{ $shot }}/about.jpg" alt="About Us page showing the Motto and leadership portraits with gold and navy rings" class="help-shot">
                <ol>
                    <li>Open <a href="{{ url('/about') }}" target="_blank">About Us</a>.</li>
                    <li>Read Vision, Mission and Motto. The Motto sits above <strong>Our Leadership</strong>.</li>
                    <li>Each published leader shows a round photo, the name, a country flag at the end of the name when a country is saved, and the title. Email, phone and biography are not shown on this page.</li>
                    <li>The frame is a gold ring on the outside, a navy ring inside it, and a soft gold glow. The page behind the portraits stays white.</li>
                    <li>Portraits sit close together in a row and wrap onto the next line when there are many leaders.</li>
                    <li>A leader with no photo still shows a letter. A leader marked unpublished does not appear here.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> every published leader is visible under the Motto, the photo fills the circle, and the flag appears only for leaders who have a country.</p>
            </div>
        </article>

        <article class="card mb-4" id="branches">
            <div class="card-header bg-white"><strong>5. Branches</strong></div>
            <div class="card-body">
                <img src="{{ $shot }}/branches.jpg" alt="Branches page with Cameroon and Diaspora tabs and a search box" class="help-shot">
                <ol>
                    <li>Open <a href="{{ url('/branches') }}" target="_blank">Branches</a>.</li>
                    <li>Cameroon is selected first. Province headings and diocese cards are listed.</li>
                    <li>Type part of a diocese name, for example <em>Buea</em>. Only matching cards stay visible.</li>
                    <li>Clear the search, then open <strong>Diaspora</strong>. Country names and branch cards appear, with a flag for that country.</li>
                    <li>Search again inside Diaspora, for example <em>Belgium</em> or <em>Antwerpen</em>.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> both tabs work, search filters the list, and clearing the search restores the full list.</p>
            </div>
        </article>

        <article class="card mb-4" id="membership">
            <div class="card-header bg-white"><strong>6. Membership registration</strong></div>
            <div class="card-body">
                <img src="{{ $shot }}/membership.jpg" alt="Membership page with the Register button" class="help-shot">
                <p>Open <a href="{{ url('/membership') }}" target="_blank">Membership</a>. Use a test name and a phone number you control. Do not approve the test record until the admin section below.</p>
                <h5 class="mt-3">Start</h5>
                <ol>
                    <li>Click <strong>Register</strong>. Two choices appear: <strong>Cameroon</strong> and <strong>Diaspora</strong>. The form does not continue until one is chosen.</li>
                    <li>If bylaws and articles have not been opened, the site asks you to confirm before continuing. Open both documents once, then return and register again.</li>
                </ol>
                <h5>Cameroon</h5>
                <ol>
                    <li>Choose Cameroon. Country stays Cameroon.</li>
                    <li>Click Diocese and type. The official diocese names appear. You should not need a second click to start typing.</li>
                    <li>Click Region and type. Only Cameroon regions appear.</li>
                    <li>Address, City and State are required.</li>
                    <li>Continue through identity, portrait and signature. Submit.</li>
                </ol>
                <h5>Diaspora</h5>
                <ol>
                    <li>Start again and choose Diaspora. The Cameroon Region field is not shown.</li>
                    <li>Click Country. The first names are Cameroon, North America, then the diaspora countries (Canada, United Kingdom, Belgium, Germany, Finland, South Africa, Norway). Typing finds other countries.</li>
                    <li>The phone country list puts those same countries first.</li>
                    <li>Click Diocese and type. Official branch names for the selected country appear. If the country is still empty, branches for the phone country appear, or all diaspora branches.</li>
                    <li>Address, City and State / Province are required.</li>
                    <li>Submit a second test registration.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> Cameroon asks for Region, Diaspora does not, both ask for address, city and state, and diocese search shows real branch names rather than an empty text box.</p>
            </div>
        </article>

        <article class="card mb-4" id="rest">
            <div class="card-header bg-white"><strong>7. Events, gallery, contact and donate</strong></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img src="{{ $shot }}/events.jpg" alt="Events page" class="help-shot">
                    </div>
                    <div class="col-md-6 mb-3">
                        <img src="{{ $shot }}/contact.jpg" alt="Contact page" class="help-shot">
                    </div>
                </div>
                <ol>
                    <li>Open <a href="{{ url('/events') }}" target="_blank">Events</a>. The list or the empty state loads. Open one event if any are published. Filters and search should not break the page.</li>
                    <li>Open <a href="{{ url('/gallery') }}" target="_blank">Gallery</a>. Photos display, or the empty state is clear.</li>
                    <li>Open <a href="{{ url('/contact') }}" target="_blank">Contact</a>. The form is readable. Send a short test message only if you are allowed to create a real enquiry.</li>
                    <li>Open <a href="{{ url('/donate') }}" target="_blank">Donate</a>. The amount and payment choices are visible. Stop before paying unless a test payment was agreed.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> each page returns a normal screen, not an error page.</p>
            </div>
        </article>

        <article class="card mb-4" id="mobile">
            <div class="card-header bg-white"><strong>8. Phones and small screens</strong></div>
            <div class="card-body">
                <img src="{{ $shot }}/mobile.jpg" alt="Homepage on a narrow phone screen" class="help-shot help-shot-narrow">
                <ol>
                    <li>Narrow the browser or use a phone.</li>
                    <li>Open the menu. It lists the same public items as section 2, then <strong>Join CWA</strong>, <strong>Donate</strong>, <strong>Resources</strong>, language, and <strong>Login</strong>. The drawer scrolls if the list is long.</li>
                    <li>On About Us, leadership portraits wrap and the gold glow is not cut off by the edge of the screen.</li>
                    <li>On Branches, Cameroon and Diaspora stay on one row, and the search box is full width.</li>
                    <li>On the registration form, tapping Diocese, Region or Country focuses the search field immediately. The page does not jump sideways.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> you can complete the menu, a branch search, and the first registration step without horizontal scrolling.</p>
            </div>
        </article>

        <article class="card mb-4" id="admin-leaders">
            <div class="card-header bg-white"><strong>9. Admin: About Us Leaders</strong></div>
            <div class="card-body">
                <p>Menu: <strong>About Us Leaders</strong>. This is limited to administrators.</p>
                <ol>
                    <li>Add a leader with name, title, country and a clear portrait photo. Leave the profile published.</li>
                    <li>Save. The new card appears in the directory. The page must not show a server error.</li>
                    <li>Open <a href="{{ url('/about') }}#leadership" target="_blank">About Us</a>. The new portrait is under the Motto, with the country flag after the name.</li>
                    <li>Edit the leader and turn publishing off. Refresh About Us. That person disappears. Turn publishing on again.</li>
                    <li>Change the order and save. About Us follows the new order.</li>
                    <li>Remove the test leader when the check is finished, unless the profile should stay.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> the photo saves, the public card shows photo, name, flag and title only, and unpublishing hides the person.</p>
            </div>
        </article>

        <article class="card mb-4" id="admin-members">
            <div class="card-header bg-white"><strong>10. Admin: Membership</strong></div>
            <div class="card-body">
                <p>Menu: <strong>Membership</strong> on the admin side menu, then Awaiting Approvals, Members and Rejected.</p>
                <ol>
                    <li>Open <strong>Awaiting Approvals</strong>. The Cameroon test and the Diaspora test from section 6 are listed.</li>
                    <li>Open the Cameroon record. Region, address, city and state are stored.</li>
                    <li>Open the Diaspora record. Country, diocese or branch, address, city and state are stored. Region is empty.</li>
                    <li>Approve one test record. It leaves Awaiting and appears under <strong>Members</strong>.</li>
                    <li>Reject the other test record and enter a short reason. It appears under <strong>Rejected</strong>.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> both records can be found by name, the address fields are complete, and approve and reject move them to the correct list.</p>
            </div>
        </article>

        <article class="card mb-4" id="admin-content">
            <div class="card-header bg-white"><strong>11. Admin: Site Content</strong></div>
            <div class="card-body">
                <ol>
                    <li>Open <strong>Site Content</strong>.</li>
                    <li>Change one About Us sentence, such as a word in the motto, and save.</li>
                    <li>Refresh the public About page. The new sentence is there. Change it back and save again.</li>
                    <li>Open <strong>Landing Menu</strong>. Drag one item, save, and refresh the homepage. The header follows that order. Restore the previous order.</li>
                    <li>Open <strong>Side Menu</strong>. Drag one item or hide one that is not Dashboard or Site Content, save, and look at the blue menu. It matches. Restore the previous order. Help stays last.</li>
                    <li>Open <strong>People</strong> and <strong>Settings</strong> if those lists were changed. The submenus match section 2.</li>
                </ol>
                <p class="mb-1"><strong>Pass when</strong> a saved text change appears on the public page, and a saved menu order appears on the website header and the admin side menu.</p>
            </div>
        </article>

        <article class="card mb-4" id="signoff">
            <div class="card-header bg-white"><strong>12. Sign-off checklist</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-light">
                            <tr><th>Check</th><th style="width:90px;">Pass</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Public menu matches Help, then language, Donate, Login and Join CWA</td><td></td></tr>
                            <tr><td>Admin side menu, People and Settings match Help, and Help is last</td><td></td></tr>
                            <tr><td>About Us motto, then leadership portraits, flags and titles</td><td></td></tr>
                            <tr><td>Branches: Cameroon, Diaspora and search</td><td></td></tr>
                            <tr><td>Membership: Cameroon path, including Region and address</td><td></td></tr>
                            <tr><td>Membership: Diaspora path, searchable country and branch names</td><td></td></tr>
                            <tr><td>Events, gallery, contact and donate open without an error</td><td></td></tr>
                            <tr><td>Phone menu, branch search and registration search</td><td></td></tr>
                            <tr><td>Admin leader photo publishes and unpublishes on About Us</td><td></td></tr>
                            <tr><td>Admin can approve and reject a membership</td><td></td></tr>
                            <tr><td>Site Content edit shows on the public page and can be undone</td><td></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
    </div>
</section>
<style>
    .help-guide h5 { color: #0b3f90; font-weight: 800; }
    .help-shot {
        display: block;
        width: 100%;
        max-width: 920px;
        height: auto;
        margin: 0 0 1rem;
        border: 1px solid #e6e1d6;
        border-radius: 12px;
        background: #fff;
    }
    .help-shot-narrow { max-width: 320px; }
    .help-guide ol li, .help-guide ul li { margin-bottom: .35rem; }
</style>
@endsection
