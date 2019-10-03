@extends('layouts.app')

@section('title')
@lang('ui.reset_password')
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
                <div class="card-header">@lang('ui.reset_password')</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error_message'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error_message') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.requestReset') }}">
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

                        @if ($verifyV2Checkbox)
                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <input id="reCAPTCHA_v2_token" type="hidden" name="reCAPTCHA_v2_token" value>

                                <div id="reCAPTCHA-v2-checkbox"></div>

                                @error('reCAPTCHA_v2_token')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    @lang('ui.send_reset_password_request')
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
