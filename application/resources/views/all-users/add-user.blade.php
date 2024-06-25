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
                        <h6 class="h2 text-white d-inline-block mb-0">Users</h6>
                        <nav aria-label="breadcrumb" class="d-none d-md-inline-block ml-md-4">
                            <ol class="breadcrumb breadcrumb-links breadcrumb-dark">
                                <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i></a></li>
                                <li class="breadcrumb-item"><a href="#">Users</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Add new user</li>
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
                            <h3 class="mb-0">{{ __('Add New User') }}</h3>
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


                    <form method="POST" action="{{ route('storeuser') }}">
                        @csrf

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="username" class="form-control-label">{{ __('Username') }}<span class="required-fields">*</span></label>
                                    <input id="username" type="text" class="form-control form-control-alternative @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                    @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="email" class="form-control-label">{{ __('Email') }}<span class="required-fields">*</span></label>
                                    <input id="email" type="email" class="form-control form-control-alternative @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="full_name" class="form-control-label">{{ __('Full Name') }}<span class="required-fields">*</span></label>
                                    <input id="full_name" type="text" class="form-control form-control-alternative @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required autocomplete="full_name">

                                    @error('full_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="phone_no" class="form-control-label">{{ __('Phone Number') }}<span class="required-fields">*</span></label>
                                <input id="phone_no" type="tel" class="form-control form-control-alternative @error('phone_no') is-invalid @enderror" name="phone_no" value="{{ old('phone_no') }}" required autocomplete="phone_no">
                                <input type="hidden" id="phone_code" name="phone_code" value="">
                                @error('phone_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>                            
                            
                        </div>

                        <div class="row">
                            <div class="form-group focused col-12 col-lg-6">
                                <label for="user_type" class="form-control-label">{{ __('Select User Type') }}<span class="required-fields">*</span></label>
                                    <select id="user_type" class="form-control form-control-alternative @error('user_type') is-invalid @enderror" name="type" required>
                                        <option value="voter" selected>Voter</option>
                                        <option value="organizer">Organizer</option>
                                        <option value="participant">Participant</option>
                                        {{-- <option value="admin">Admin</option> --}}
                                    </select>

                                    @error('user_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group focused col-12 col-lg-6">
                                <label for="password" class="form-control-label">{{ __('Password') }}<span class="required-fields">*</span></label>
                                    <input id="password" type="password" class="form-control form-control-alternative @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                    @error('password')
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
