@extends('layouts.app', ['title' => __('Site Setting')])

@section('content')
    {{-- @include('users.partials.header', [
        'title' => __('All User Settings') . ' ' . auth()->user()->name,
        'description' => __(
            'This is your settings page. You can update the setting that may effect your app\'s business logic.'
        ),
        'class' => 'col-lg-7',
    ]) --}}
    <div class="header bg-primary pb-6 pt-5 pt-lg-6">
        <div class="container-fluid">
            <div class="header-body">
                <div class="row align-items-center py-4">
                    <div class="col-lg-12 col-12">
                        <h6 class="h2 text-white d-inline-block mb-0">Categories</h6>
                        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#">Categories</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add new category</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt--6">
        <div class="row">

            <div class="col">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                            <h3 class="mb-0">{{ __('Add New Category') }}</h3>
                    </div>
                    <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <form method="POST" action="{{ route('storecategory') }}">
                        @csrf

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="title" class="form-control-label">{{ __('Title') }}<span class="required-fields">*</span></label>
                                    <input id="title" type="text" class="form-control form-control-alternative @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required autocomplete="username" autofocus>

                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="slug" class="form-control-label">{{ __('Slug') }}<span class="required-fields">*</span></label>
                                    <input id="slug" type="slug" class="form-control form-control-alternative @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug') }}" required autocomplete="slug">

                                    @error('slug')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="verified" class="form-control-label">{{ __('Verified') }}</label>
                                    <input id="verified" type="checkbox" class="form-control @error('verified') is-invalid @enderror" name="verified" value="1">

                                    @error('verified')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-lg-6">
                                <label for="suggest_id" class="form-control-label">{{ __('Suggested Id') }}<span class="required-fields">*</span></label>
                                <select class="form-control form-control-alternative js-example-basic-single" id="suggest_id" name="suggest_id">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                                @error('suggest_id')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>                            
                            
                        </div>

                        <div class="row">
                            <div class="form-group text-center col-12">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Save') }}
                                    </button>
                            </div>
                        </div>
                    </form>
                        
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
    


@endsection
