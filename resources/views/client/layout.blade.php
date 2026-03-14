<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/main.jsx'])
    </head>
    <body class="font-sans">
        @auth
            @php($currentUser = auth()->user())

            <div class="client-shell" data-client-shell>
                <button
                    type="button"
                    class="client-shell__menu-toggle"
                    aria-label="{{ __('app.client.navigation.open_menu') }}"
                    aria-controls="client-sidebar"
                    aria-expanded="false"
                    data-client-menu-toggle
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <div class="client-shell__overlay" data-client-menu-close></div>

                <aside class="client-shell__sidebar" id="client-sidebar">
                    <button
                        type="button"
                        class="client-shell__menu-close"
                        aria-label="{{ __('app.client.navigation.close_menu') }}"
                        data-client-menu-close
                    >
                        &times;
                    </button>

                    <div class="client-shell__brand">
                        <div class="client-shell__brand-mark">N</div>
                        <div>
                            <div class="client-shell__brand-name">{{ config('app.name', 'Noma') }}</div>
                            <div class="client-shell__brand-role">{{ $currentUser->role->label() }}</div>
                        </div>
                    </div>

                    <nav class="client-shell__nav" aria-label="{{ __('app.client.navigation.aria') }}">
                        <a
                            href="{{ route('client.panel') }}"
                            class="client-shell__nav-link {{ request()->routeIs('client.panel') ? 'is-active' : '' }}"
                            data-client-nav-link
                        >
                            {{ __('app.client.navigation.panel') }}
                        </a>

                        @if ($currentUser->isAdmin())
                            <a
                                href="{{ route('client.admin.users.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.admin.users.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.users') }}
                            </a>
                            <a
                                href="{{ route('client.admin.owner-subscriptions.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.admin.owner-subscriptions.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.owner_subscriptions') }}
                            </a>
                        @elseif ($currentUser->isOwner())
                            <a
                                href="{{ route('client.properties.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.properties.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.properties') }}
                            </a>
                            <a
                                href="{{ route('client.units.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.units.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.units') }}
                            </a>
                            <a
                                href="{{ route('client.tenants.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.tenants.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.tenants') }}
                            </a>
                            <a
                                href="{{ route('client.leases.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.leases.*') || request()->routeIs('client.charge-rules.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.leases') }}
                            </a>
                            <a
                                href="{{ route('client.invoices.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.invoices.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.invoices') }}
                            </a>
                            <a
                                href="{{ route('client.meters.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.meters.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.meters') }}
                            </a>
                            <a
                                href="{{ route('client.reports.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.reports.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.reports') }}
                            </a>
                            <a
                                href="{{ route('client.exports.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.exports.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.exports') }}
                            </a>
                        @elseif ($currentUser->isTenant())
                            <a
                                href="{{ route('client.tenant-leases.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.tenant-leases.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.my_contracts') }}
                            </a>
                            <a
                                href="{{ route('client.tenant-invoices.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.tenant-invoices.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.my_invoices') }}
                            </a>
                            <a
                                href="{{ route('client.tenant-meters.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.tenant-meters.*') || request()->routeIs('client.tenant-meter-readings.*') ? 'is-active' : '' }}"
                                data-client-nav-link
                            >
                                {{ __('app.client.navigation.utility_readings') }}
                            </a>
                        @endif

                        <a
                            href="{{ route('client.profile.edit') }}"
                            class="client-shell__nav-link {{ request()->routeIs('client.profile.*') || request()->routeIs('client.billing.*') ? 'is-active' : '' }}"
                            data-client-nav-link
                        >
                            {{ __('app.client.navigation.profile') }}
                        </a>
                    </nav>
                </aside>

                <div class="client-shell__main">
                    <header class="client-shell__topbar">
                        <div class="client-shell__topbar-actions">
                            <button
                                type="button"
                                class="client-shell__icon-button"
                                disabled
                                aria-label="{{ __('app.client.topbar.language') }}"
                                title="{{ __('app.client.topbar.language_soon') }}"
                            >
                                {{ __('app.client.topbar.language_short') }}
                            </button>

                            <a
                                href="{{ route('client.profile.edit') }}"
                                class="client-shell__profile"
                                aria-label="{{ __('app.client.topbar.profile') }}"
                                title="{{ __('app.client.topbar.profile') }}"
                            >
                                {{ strtoupper(mb_substr($currentUser->name, 0, 1)) }}
                            </a>

                            <form method="POST" action="{{ route('client.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">
                                    {{ __('app.client.common.logout') }}
                                </button>
                            </form>
                        </div>
                    </header>

                    <main class="client-shell__content">
                        @if (session('status'))
                            <div class="alert alert-success mb-4">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger mb-4">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @yield('content')
                    </main>
                </div>
            </div>
        @else
            <div class="container py-5">
                @if (session('status'))
                    <div class="alert alert-success mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        @endauth
    </body>
</html>
