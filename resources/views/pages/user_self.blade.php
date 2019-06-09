@extends('layout')

@section('content')

<div class="columns is-centered">
    <div class="column is-three-quarters-mobile is-two-thirds-tablet is-half-desktop is-one-third-widescreen is-one-quarter-fullhd">
        <div class="tile">
            <div class="content notification is-light">
                <p class="subtitle">@lang('ui.reset_password')</p>
                <div class="content">
                    <form action="{{ route('user.reset_password') }}" method="POST" class="control">
                        @csrf

                        <div class="field">
                            <label class="label">@lang('ui.password')</label>
                            <p class="control has-icons-left has-icons-right">
                                <input name="password" class="input" type="password" placeholder="@lang('ui.password')">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </p>
                        </div>
                        <div class="field">
                            <label class="label">@lang('ui.password_confirmation')</label>
                            <p class="control has-icons-left">
                                <input name="password_confirmation" class="input" type="password" placeholder="@lang('ui.password_confirmation')">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </p>
                        </div>
                        <div class="field">
                            <p class="control">
                                <button class="button is-success">
                                    @lang('ui.reset_password')
                                </button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection