@extends('layout')

@section('content')

<div class="columns is-centered">
    <div class="column is-three-quarters-mobile is-two-thirds-tablet is-half-desktop is-one-third-widescreen is-one-quarter-fullhd">
        
        <p class="title is-2">@lang('ui.user_basic_info')</p>
        
        <div class="content">
            <p class="subtitle is-4">@lang('ui.username')</p>
            <p class="title is-3">{{ Auth::user()->username }}</p>
        </div>
        
        <div class="content">
            @if (Auth::user()->display_name)
            <p class="subtitle is-4">@lang('ui.display_name')</p>
            <p class="title is-3">{{ Auth::user()->display_name }}</p>
            @else
            <p class="subtitle is-4 has-text-grey-light">@lang('ui.display_name')</p>
            <p class="title is-3 has-text-grey-light">@lang('ui.not_set')</p>
            @endif
        </div>
        
        <div class="content">
            @if (Auth::user()->mobile_ext)
            <p class="subtitle is-4">@lang('ui.mobile_ext')</p>
            <p class="title is-3">{{ Auth::user()->mobile_ext }}</p>
            @else
            <p class="subtitle is-4 has-text-grey-light">@lang('ui.mobile_ext')</p>
            <p class="title is-3 has-text-grey-light">@lang('ui.not_set')</p>
            @endif
        </div>
        
        <div class="tile">
            <div class="content notification is-light">
                <p class="subtitle">@lang('ui.reset_password')</p>
                @if (session('status'))
                <p class="subtitle is-6 has-text-success">{{ session('status') }}</p>
                @endif
                <div class="content">
                    <form action="{{ route('user.reset_password') }}" method="POST" class="control">
                        @csrf

                        <div class="field">
                            <label class="label">@lang('ui.current_password')</label>
                            <div class="control has-icons-left has-icons-right">
                                <input name="current_password" class="input" type="password" placeholder="@lang('ui.current_password')">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            @if ($errors->has('current_password'))
                            <p class="help is-danger">{{ $errors->first('current_password') }}</p>
                            @endif
                        </div>

                        <div class="field">
                            <label class="label">@lang('ui.new_password')</label>
                            <div class="control has-icons-left has-icons-right">
                                <input name="new_password" class="input" type="password" placeholder="@lang('ui.new_password')">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            @if ($errors->has('new_password'))
                            <p class="help is-danger">{{ $errors->first('new_password') }}</p>
                            @endif
                        </div>
                        <div class="field">
                            <label class="label">@lang('ui.password_confirmation')</label>
                            <div class="control has-icons-left">
                                <input name="password_confirmation" class="input" type="password" placeholder="@lang('ui.password_confirmation')">
                                <span class="icon is-small is-left">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            @if ($errors->has('password_confirmation'))
                            <p class="help is-danger">{{ $errors->first('password_confirmation') }}</p>
                            @endif
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