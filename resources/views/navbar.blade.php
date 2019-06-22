<nav class="navbar is-transparent has-shadow is-spaced" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.jpg') }}" width="28" height="28">
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbar">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbar" class="navbar-menu">
            <div class="navbar-start">

                <!-- <a class="navbar-item" href="{{ route('home') }}">
                    @lang('ui.home')
                </a> -->

                <a class="navbar-item" href="{{ route('pages.shifts_arrangements_table') }}">
                    @lang('ui.shifts_arrangements_table')
                </a>

                <a class="navbar-item" href="{{ route('pages.areas') }}">
                    @if (Auth::user()->is_admin)
                        @lang('ui.areas_management')
                    @else
                        @lang('ui.areas')
                    @endif
                </a>

                <a class="navbar-item" href="{{ route('pages.shifts') }}">
                    @if (Auth::user()->is_admin)
                        @lang('ui.shifts_management')
                    @else
                        @lang('ui.shifts')
                    @endif
                </a>

                <a class="navbar-item" href="{{ route('pages.users') }}">
                    @if (Auth::user()->is_admin)
                        @lang('ui.users_management')
                    @else
                        @lang('ui.users')
                    @endif
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
                        <a class="navbar-item" href="{{ route('user.self') }}">
                            @lang('ui.user')({{ Auth::user()->username }})
                        </a>

                        <hr class="navbar-divider">

                        <a class="navbar-item" href="{{ route('logout') }}" onclick="event.preventDefault();
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
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Get all "navbar-burger" elements
        const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

        // Check if there are any navbar burgers
        if ($navbarBurgers.length > 0) {

            // Add a click event on each of them
            $navbarBurgers.forEach(el => {
                el.addEventListener('click', function() {

                    // Get the target from the "data-target" attribute
                    const target = el.dataset.target;
                    const $target = document.getElementById(target);

                    // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
                    el.classList.toggle('is-active');
                    $target.classList.toggle('is-active');

                });
            });
        }

    });
</script>