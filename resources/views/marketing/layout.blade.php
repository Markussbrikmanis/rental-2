<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Noma') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite('resources/css/app.css')
    </head>
    <body>
        <div class="marketing-page">
            <header class="marketing-nav">
                <a class="marketing-brand" href="{{ route('marketing.home') }}">
                    <span class="marketing-brand__mark">N</span>
                    <span>{{ config('app.name', 'Noma') }}</span>
                </a>

                <nav class="marketing-nav__links" aria-label="{{ __('marketing.nav.aria') }}">
                    <a href="{{ route('marketing.home') }}" @class(['is-active' => ($activePage ?? null) === 'home'])>
                        {{ __('marketing.nav.home') }}
                    </a>
                    <a href="{{ route('marketing.pricing') }}" @class(['is-active' => ($activePage ?? null) === 'pricing'])>
                        {{ __('marketing.nav.pricing') }}
                    </a>
                    <a href="{{ route('marketing.contact') }}" @class(['is-active' => ($activePage ?? null) === 'contact'])>
                        {{ __('marketing.nav.contact') }}
                    </a>
                </nav>

                <a class="marketing-nav__login" href="{{ route('login') }}">
                    {{ __('marketing.nav.login') }}
                </a>
            </header>

            <main>
                @yield('content')
            </main>

            <footer class="marketing-footer">
                <div>
                    <strong>{{ config('app.name', 'Noma') }}</strong>
                    <p>{{ __('marketing.footer.summary') }}</p>
                </div>

                <div class="marketing-footer__links">
                    <a href="{{ route('marketing.home') }}">{{ __('marketing.nav.home') }}</a>
                    <a href="{{ route('marketing.pricing') }}">{{ __('marketing.nav.pricing') }}</a>
                    <a href="{{ route('marketing.contact') }}">{{ __('marketing.nav.contact') }}</a>
                    <a href="{{ route('marketing.privacy') }}">{{ __('marketing.footer.privacy') }}</a>
                    <a href="{{ route('login') }}">{{ __('marketing.nav.login') }}</a>
                </div>
            </footer>
        </div>
    </body>
</html>
