<!doctype html>
<html>

<head lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@lang('admin.change_user_password_page_title')</title>
    <link rel="stylesheet" href="{{ url(mix('css/bulma.css')) }}">
    <link rel="stylesheet" href="{{ url(mix('css/materialize/checkboxes.css')) }}">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

    @if (session('status'))
    <script>
        return_function('{{ session('status') }}');
        window.close();
    </script>
    @endif
</head>

<body>
    <section class="section">
        <div class="container">
        
            <div class="tile">
                <div class="content notification is-light">
                    <p class="subtitle">@lang('admin.change_user_password_page_title')</p>
                    @if (session('message'))
                    <p class="subtitle is-6 has-text-success">{{ session('message') }}</p>
                    @endif
                    <div class="content">
                        <form action="{{ route('admin.changeUserPassword', $user->username) }}" method="POST" class="control">
                            @csrf

                            <div class="field">
                                <label class="label">@lang('ui.user')</label>
                                <div>{{ $user->username }}</div>
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
    </section>
</body>

</html>