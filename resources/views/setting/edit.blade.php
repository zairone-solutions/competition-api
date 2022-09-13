@extends('layouts.app', ['title' => __('User Profile')])

@section('content')
    @include('users.partials.header', [
        'title' => __('Site Settings') . ' ' . auth()->user()->name,
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
                            <h3 class="mb-0">{{ __('Edit Settings') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('setting.update') }}" autocomplete="off">
                            @csrf
                            @method('put')


                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif
                            @php
                                $settings = \App\Models\Setting::isParent()->get();
                            @endphp
                            @foreach ($settings as $setting)
                                @php
                                    $items = $setting->children()->get();
                                @endphp
                                <h6 class="heading-small text-muted mb-4">{{ __($setting->title) }}</h6>


                                <div class="pl-lg-4">
                                    @foreach ($items as $item)
                                        <div class="form-group{{ $errors->has($item->key) ? ' has-danger' : '' }}">
                                            <label class="form-control-label"
                                                for="input-name">{{ __($item->title) }}</label>
                                            <input type="text" name="{{ $item->key }}" id="{{ $item->key }}"
                                                class="form-control form-control-alternative{{ $errors->has($item->key) ? ' is-invalid' : '' }}"
                                                placeholder="{{ __($item->title) }}"
                                                value="{{ old($item->key, $item->value) }}" required autofocus>

                                            @if ($errors->has($item->key))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first($item->key) }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach

                                </div>
                                <hr class="my-4" />
                            @endforeach
                            <div class="text-center">
                                <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>

        @include('layouts.footers.auth')
    </div>
@endsection
