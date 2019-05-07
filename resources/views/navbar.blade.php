<nav class="navbar is-transparent" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.jpg') }}" width="28" height="28">
        </a>

        <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false"
            data-target="navbar">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbar" class="navbar-menu">
        <div class="navbar-start">
            <a class="navbar-item" href="{{ route('home') }}">
                @lang('ui.home')
            </a>

            <a class="navbar-item" href="{{ route('pages.users_and_groups') }}">
                @lang('ui.users_and_groups')
            </a>

            <a class="navbar-item" href="{{ route('pages.areas_and_shifts') }}">
                @lang('ui.areas_and_shifts')
            </a>

        </div>

        @guest
        <div class="navbar-end">
            <div class="navbar-item">
                <div class="buttons">
                    <a class="button is-primary" href="{{ route('login') }}">
                        @lang('ui.login')
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="navbar-end">
            <div class="navbar-item has-dropdown is-hoverable" onclick="this.classList.toggle('is-active')">
                <a class="navbar-link">
                    {{ Auth::user()->display_name ?? Auth::user()->username }}
                </a>
        
                <div class="navbar-dropdown is-right is-boxed">
                    <a class="navbar-item" href="{{ route('users.show', Auth::user()->username) }}">
                        @lang('ui.user')({{ Auth::user()->username }})
                    </a>

                    <hr class="navbar-divider">

                    <a class="navbar-item" href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                        @lang('ui.logout')
                    </a>
                    
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
        @endguest
    </div>
</nav>