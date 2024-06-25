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
                                <li class="breadcrumb-item active" aria-current="page">Edit Users</li>
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
                            <h3 class="mb-0">{{ __('Edit User') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('updateuser', $user->id) }}" enctype="multipart/form-data">
                            @csrf
                           
                            <div class="row">
	                            <div class="form-group focused col-12 col-lg-6">
	                                <label for="username" class="form-control-label">{{ __('Username') }}</label>
	                                    <input id="username" type="text" class="form-control form-control-alternative @error('username') is-invalid @enderror" name="username" value="{{ $user->username }}"  autofocus>

	                                    @error('username')
	                                        <span class="invalid-feedback" role="alert">
	                                            <strong>{{ $message }}</strong>
	                                        </span>
	                                    @enderror
	                            </div>

	                            <div class="form-group focused col-12 col-lg-6">
	                                <label for="email" class="form-control-label">{{ __('E-Mail Address') }}</label>
	                                    <input id="email" type="email" class="form-control form-control-alternative @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" >

	                                    @error('email')
	                                        <span class="invalid-feedback" role="alert">
	                                            <strong>{{ $message }}</strong>
	                                        </span>
	                                    @enderror
	                            </div>
                            </div>

                            <div class="row">
	                            <div class="form-group focused col-12 col-lg-6">
	                                <label for="full_name" class="form-control-label">{{ __('Full Name') }}</label>
	                                    <input id="full_name" type="text" class="form-control form-control-alternative @error('full_name') is-invalid @enderror" name="full_name" value="{{ $user->full_name }}" >

	                                    @error('full_name')
	                                        <span class="invalid-feedback" role="alert">
	                                            <strong>{{ $message }}</strong>
	                                        </span>
	                                    @enderror
	                            </div>

								<div class="form-group focused col-12 col-lg-6">
									<label for="phone_no" class="form-control-label">{{ __('Phone No') }}</label>
									<input id="phone_no" type="tel" class="form-control form-control-alternative @error('phone_no') is-invalid @enderror" name="phone_no" value="{{ $user->phone_code }} {{ $user->phone_no }}" >
									<input type="hidden" id="phone_code" name="phone_code" value="{{ $user->phone_code }}">
								  
									@error('phone_no')
										<span class="invalid-feedback" role="alert">
											<strong>{{ $message }}</strong>
										</span>
									@enderror
								</div>
								
								
								
								{{-- <div class="form-group col-12 col-lg-6">
									<label for="phone_no" class="form-control-label">{{ __('Phone No') }}</label>
									<input id="phone_no" type="tel" class="form-control @error('phone_no') is-invalid @enderror" name="phone_no" value="{{ old('phone_no', $user->phone_no) }}">
									<input type="hidden" id="phone_code" name="phone_code" value="{{ old('phone_code', $user->phone_code) }}">
								
									@error('phone_no')
										<span class="invalid-feedback" role="alert">
											<strong>{{ $message }}</strong>
										</span>
									@enderror
								</div>
								 --}}
								



                            </div>

                            <div class="row">
	                            <div class="form-group focused col-12 col-lg-6">
	                                <label for="user_type" class="form-control-label">{{ __('Select User Type') }}</label>
	                                <select id="user_type" class="form-control form-control-alternative @error('user_type') is-invalid @enderror" name="type">
	                                    <option value="voter" {{ $user->type == 'voter' ? 'selected' : '' }}>Voter</option>
	                                    <option value="organizer" {{ $user->type == 'organizer' ? 'selected' : '' }}>Organizer</option>
	                                    <option value="participant" {{ $user->type == 'participant' ? 'selected' : '' }}>Participant</option>
	                                    <option value="admin" {{ $user->type == 'admin' ? 'selected' : '' }}>Admin</option>
	                                </select>
	                            
	                                @error('user_type')
	                                    <span class="invalid-feedback" role="alert">
	                                        <strong>{{ $message }}</strong>
	                                    </span>
	                                @enderror
	                            </div>                            

	                            <div class="form-group focused col-12 col-lg-6">
	                                <label for="password" class="form-control-label">{{ __('Password') }}</label>
	                                    <input id="password" type="password" class="form-control form-control-alternative @error('password') is-invalid @enderror" name="password" autocomplete="new-password">

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
	                                        {{ __('Update') }}
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
