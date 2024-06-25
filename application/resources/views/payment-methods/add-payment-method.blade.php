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
                        <h6 class="h2 text-white d-inline-block mb-0">Payment Methods</h6>
                        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#">Payment Methods</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add payment method</li>
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
                            <h3 class="mb-0">{{ __('Add New Payment Method') }}</h3>
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

                    <form method="POST" action="{{ route('storepaymentmethod') }}">
                        @csrf

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="title" class="form-control-label">{{ __('Title') }}<span class="required-fields">*</span></label>
                                    <input id="title" type="text" class="form-control form-control-alternative @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" required>

                                    @error('title')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="code" class="form-control-label">{{ __('Code') }}<span class="required-fields">*</span></label>
                                    <input id="code" type="text" class="form-control form-control-alternative @error('code') is-invalid @enderror" name="code" value="{{ old('code') }}" required>

                                    @error('code')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="image" class="form-control-label">{{ __('Image') }}</label>
                                <input id="image" type="file" class="form-control form-control-alternative @error('image') is-invalid @enderror" name="image">

                                @error('image')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="active" class="form-control-label">{{ __('Active/InActive') }}</label>
                                <select class="form-control form-control-alternative @error('active') is-invalid @enderror" name="active" id="active">
                                    <option value="1" selected>Active</option>
                                    <option value="0">InActive</option>
                                </select>

                                @error('active')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>                            
                            
                        </div>

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="credentials" class="form-control-label">{{ __('Credentials') }}<span class="required-fields">*</span></label>
                                <input id="credentials" type="text" class="form-control form-control-alternative @error('credentials') is-invalid @enderror" name="credentials" value="{{ old('credentials') }}" required>

                                @error('credentials')
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
