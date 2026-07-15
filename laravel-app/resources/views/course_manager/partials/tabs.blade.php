@include('course_manager.partials.styles')
@php
    $cmTab = $cmTab ?? '';
    $tabs = [
        ['courses.index', 'Course List', 'dripicons-view-list'],
        ['courses.create', 'Add Course', 'dripicons-plus'],
        ['courses.registrations', 'Registrations', 'dripicons-user-group'],
        ['courses.invoices', 'Invoices', 'dripicons-document'],
        ['courses.certificates', 'Certificates', 'dripicons-trophy'],
        ['courses.progress', 'Student Progress', 'dripicons-graph-line'],
        ['courses.feedback', 'Feedback', 'dripicons-message'],
    ];
@endphp
<nav class="cm-nav" aria-label="Course Management">
    @foreach($tabs as $tab)
        <a href="{{ route($tab[0]) }}" class="{{ $cmTab === $tab[0] ? 'is-active' : '' }}">
            <i class="{{ $tab[2] }}"></i> {{ $tab[1] }}
        </a>
    @endforeach
</nav>
