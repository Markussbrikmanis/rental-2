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

            <div class="client-shell">
                <aside class="client-shell__sidebar">
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
                        >
                            {{ __('app.client.navigation.panel') }}
                        </a>

                        @if ($currentUser->isOwner())
                            <a
                                href="{{ route('client.properties.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.properties.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.properties') }}
                            </a>
                            <a
                                href="{{ route('client.units.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.units.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.units') }}
                            </a>
                            <a
                                href="{{ route('client.tenants.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.tenants.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.tenants') }}
                            </a>
                            <a
                                href="{{ route('client.leases.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.leases.*') || request()->routeIs('client.charge-rules.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.leases') }}
                            </a>
                            <a
                                href="{{ route('client.invoices.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.invoices.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.invoices') }}
                            </a>
                            <a
                                href="{{ route('client.meters.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.meters.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.meters') }}
                            </a>
                            <a
                                href="{{ route('client.reports.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.reports.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.reports') }}
                            </a>
                            <a
                                href="{{ route('client.exports.index') }}"
                                class="client-shell__nav-link {{ request()->routeIs('client.exports.*') ? 'is-active' : '' }}"
                            >
                                {{ __('app.client.navigation.exports') }}
                            </a>
                        @endif
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

                @yield('content')
            </div>
        @endauth
    </body>
</html>
