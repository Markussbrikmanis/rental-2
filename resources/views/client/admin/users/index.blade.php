@extends('client.layout', ['title' => __('app.admin.users.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.admin.users.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.admin.users.index.description') }}</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($users->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.admin.users.empty') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.client.common.full_name') }}</th>
                                    <th>{{ __('app.client.common.email') }}</th>
                                    <th>{{ __('app.client.common.account_type') }}</th>
                                    <th>{{ __('app.subscription.fields.plan') }}</th>
                                    <th>{{ __('app.admin.users.fields.trial_ends_at') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role->label() }}</td>
                                        <td>{{ $user->isOwner() ? ($user->ownerPlan()?->name ?? '—') : '—' }}</td>
                                        <td>{{ $user->owner_trial_ends_at?->format('d.m.Y') ?? '—' }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                                <a href="{{ route('client.admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary">
                                                    {{ __('app.rental.common.edit') }}
                                                </a>
                                                <form method="POST" action="{{ route('client.admin.users.send-password-reset', $user) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        {{ __('app.admin.users.actions.send_password_reset') }}
                                                    </button>
                                                </form>
                                                @if (! auth()->user()->is($user))
                                                    <form method="POST" action="{{ route('client.admin.users.destroy', $user) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.admin.users.delete_confirm', ['name' => $user->name]) }}')">
                                                            {{ __('app.rental.common.delete') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
