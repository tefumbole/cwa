@extends('layout.main')

@section('content')
@php $cmTab = 'courses.index'; @endphp
<section class="forms">
    <div class="container-fluid cm-shell">
        @include('course_manager.partials.tabs')
        <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap" style="gap:12px;">
            <div>
                <h1 class="cm-title"><i class="fa fa-book"></i> Course Management</h1>
                <p class="cm-subtitle">Manage educational courses and training programs shown on the public Trainings page.</p>
            </div>
            <a href="{{ route('courses.create') }}" class="cm-btn-gold"><i class="dripicons-plus"></i> Add Course</a>
        </div>
        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        <form method="GET" class="mb-3">
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search courses…">
        </form>
        <div class="cm-page-card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th style="width:90px;">Order</th>
                            <th>Course Name</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $course)
                            <tr>
                                <td class="cm-order text-nowrap">
                                    @foreach(['top'=>'⇈','up'=>'↑','down'=>'↓','bottom'=>'⇊'] as $dir => $icon)
                                        <form method="POST" action="{{ route('courses.move', $course->id) }}" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="direction" value="{{ $dir }}">
                                            <button type="submit" title="{{ $dir }}">{{ $icon }}</button>
                                        </form>
                                    @endforeach
                                </td>
                                <td><strong>{{ $course->name }}</strong>
                                    @if($course->status !== 'active')
                                        <span class="cm-badge">{{ $course->status }}</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($course->description, 80) ?: '—' }}</td>
                                <td><span class="cm-badge">{{ $course->category ?: 'Training' }}</span></td>
                                <td class="cm-price">${{ number_format((float)$course->price, 0) }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('courses.course-feedback', $course->id) }}" class="cm-btn-outline"><i class="dripicons-message"></i> Feedback</a>
                                    <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-sm btn-primary"><i class="dripicons-pencil"></i></a>
                                    <form method="POST" action="{{ route('courses.destroy', $course->id) }}" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                        @csrf
                                        <button class="btn btn-sm btn-danger"><i class="dripicons-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No courses found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
