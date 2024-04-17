@extends('layouts.app', ['title' => __('Site Setting')])

@section('content')
    @include('users.partials.header', [
        'title' => __('All User Settings') . ' ' . auth()->user()->name,
        'description' => __(
            'This is your settings page. You can update the setting that may effect your app\'s business logic.'
        ),
        'class' => 'col-lg-7',
    ])

    <div class="container-fluid mt--7">
        <div class="row">

            <div class="col-xl-8 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Add New User') }}</h3>
                        </div>
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

                            <div class="form-group">
                                <label for="username" class="form-control-label">{{ __('Username') }}<span class="required-fields">*</span></label>
                                    <input id="username" type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                    @error('username')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-control-label">{{ __('Email') }}<span class="required-fields">*</span></label>
                                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group">
                                <label for="full_name" class="form-control-label">{{ __('Full Name') }}<span class="required-fields">*</span></label>
                                    <input id="full_name" type="text" class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name') }}" required autocomplete="full_name">

                                    @error('full_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone_no" class="form-control-label">{{ __('Phone No') }}</label>
                                    <input id="phone_no" type="text" class="form-control @error('phone_no') is-invalid @enderror" name="phone_no" value="{{ old('phone_no') }}" autocomplete="phone_no">

                                    @error('phone_no')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group">
                                <label for="user_type" class="form-control-label">{{ __('Select User Type') }}<span class="required-fields">*</span></label>
                                    <select id="user_type" class="form-control @error('user_type') is-invalid @enderror" name="type" required>
                                        <option value="voter" selected>Voter</option>
                                        <option value="organizer">Organizer</option>
                                        <option value="participant">Participant</option>
                                        <option value="admin">Admin</option>
                                    </select>

                                    @error('user_type')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-control-label">{{ __('Password') }}<span class="required-fields">*</span></label>
                                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                            </div>

                            <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Save') }}
                                    </button>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
    


@endsection
