@extends('layouts.app')

@section('title')
@lang('ui.login')
@endsection

@php
if (!isset($verifyV2Checkbox)) $verifyV2Checkbox = ReCAPTCHA::v2Available();
if (!isset($verifyV3)) $verifyV3 = ReCAPTCHA::v3Available();
@endphp

@if ($verifyV3)
@push('headers')
<script src="https://www.google.com/recaptcha/api.js?render={{ ReCAPTCHA::v3SiteKey() }}"></script>
<script>
grecaptcha.ready(function() {
    grecaptcha.execute('{{ ReCAPTCHA::v3SiteKey() }}', {action: 'login'}).then(function(token) {
        document.getElementById('reCAPTCHA_v3_token').value = token;
    });
});
</script>
@endpush
@endif

@if ($verifyV2Checkbox)
@push('headers')
<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
    async defer>
</script>
<script type="text/javascript">
    var verifyCallback = function(response) {
        document.getElementById('reCAPTCHA_v2_token').value = response;
    };
    var onloadCallback = function() {
        grecaptcha.render(document.getElementById('reCAPTCHA-v2-checkbox'), {
            'sitekey' : '{{ ReCAPTCHA::v2SiteKey() }}',
            'callback' : verifyCallback,
        });
    };
</script>
@endpush
@endif

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">@lang('ui.login')</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        @if ($verifyV3)
                        <input id="reCAPTCHA_v3_token" type="hidden" name="reCAPTCHA_v3_token" value>
                        @endif

                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">@lang('ui.username')</label>

                            <div class="col-md-6">
                                <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">@lang('ui.password')</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        @lang('ui.login_remember')
                                    </label>
                                </div>
                            </div>
                        </div>

                        @if ($verifyV2Checkbox)
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div id="reCAPTCHA-v2-checkbox"></div>

                                <input id="reCAPTCHA_v2_token" type="hidden" name="reCAPTCHA_v2_token" class="form-control @error('reCAPTCHA_v2_token') is-invalid @enderror" value>

                                @error('reCAPTCHA_v2_token')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @lang('ui.login')
                                </button>

                                @if (Route::has('password.requestReset'))
                                    <a class="btn btn-link" href="{{ route('password.requestReset') }}">
                                        @lang('ui.forget_password')
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
