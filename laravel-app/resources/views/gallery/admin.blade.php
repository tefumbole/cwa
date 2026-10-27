@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h4 class="mb-1"><i class="dripicons-photo-group"></i> Gallery</h4>
                <p class="text-muted">Upload photos here. They appear on the public Gallery page.</p>

                @if(session('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                @include('site_content.gallery_tab')
            </div>
        </div>
    </div>
</section>
@endsection
