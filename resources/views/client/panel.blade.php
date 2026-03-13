@extends('client.layout', ['title' => __('app.client.panel.page_title')])

@php
    $roleCards = match (true) {
        $user->isAdmin() => trans('app.client.panel.cards.admin'),
        $user->isOwner() => trans('app.client.panel.cards.owner'),
        default => trans('app.client.panel.cards.tenant'),
    };
@endphp

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <span class="badge bg-primary text-uppercase mb-2">{{ $user->role->label() }}</span>
            <h1 class="h2 mb-1">{{ __('app.client.panel.heading') }}</h1>
            <p class="text-body-secondary mb-0">
                {{ __('app.client.panel.signed_in_as', ['name' => $user->name, 'email' => $user->email]) }}
            </p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">{{ __('app.client.panel.workspace_title') }}</h2>
                <p class="text-body-secondary mb-0">
                    {{ __('app.client.panel.workspace_intro') }}
                </p>
            </div>
        </div>

        <div class="row g-4">
            @foreach ($roleCards as $card)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h2 class="h5">{{ $card['title'] }}</h2>
                            <p class="text-body-secondary mb-0">{{ $card['text'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($user->isOwner())
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h2 class="h5 mb-2">{{ __('app.properties.owner_panel.title') }}</h2>
                        <p class="text-body-secondary mb-0">{{ __('app.properties.owner_panel.description') }}</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('client.properties.index') }}" class="btn btn-primary">
                            {{ __('app.properties.actions.open_list') }}
                        </a>
                        <a href="{{ route('client.properties.create') }}" class="btn btn-outline-secondary">
                            {{ __('app.properties.actions.create') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">{{ __('app.client.panel.seeded_accounts') }}</h2>
                <ul class="mb-0 ps-3">
                    <li><strong>admin@example.com</strong> / <code>password</code></li>
                    <li><strong>owner@example.com</strong> / <code>password</code></li>
                    <li><strong>tenant@example.com</strong> / <code>password</code></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
