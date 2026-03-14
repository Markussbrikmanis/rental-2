@extends('client.layout', ['title' => __('app.subscription.admin.index.page_title')])

@section('content')
    <div class="vstack gap-4 py-4">
        <div>
            <h1 class="h2 mb-1">{{ __('app.subscription.admin.index.heading') }}</h1>
            <p class="text-body-secondary mb-0">{{ __('app.subscription.admin.index.description') }}</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <h2 class="h4 mb-1">{{ __('app.subscription.admin.plans.heading') }}</h2>
                        <p class="text-body-secondary mb-0">{{ __('app.subscription.admin.plans.description') }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('client.admin.owner-subscriptions.plans.store') }}" class="row g-3">
                    @csrf
                    @include('client.admin.owner-subscriptions.partials.plan-form', ['plan' => $newPlan, 'prefix' => 'new'])

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">{{ __('app.subscription.actions.create_plan') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($plans->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.subscription.admin.plans.empty') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.subscription.fields.plan') }}</th>
                                    <th>{{ __('app.subscription.fields.price_display') }}</th>
                                    <th>{{ __('app.subscription.fields.property_limit') }}</th>
                                    <th>{{ __('app.subscription.fields.stripe_price_id') }}</th>
                                    <th>{{ __('app.subscription.fields.visibility') }}</th>
                                    <th>{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($plans as $plan)
                                    <tr>
                                        <td class="fw-semibold">{{ $plan->name }}</td>
                                        <td>{{ $plan->display_price }}</td>
                                        <td>{{ $plan->propertyLimitLabel() }}</td>
                                        <td>{{ $plan->stripe_price_id ?: '—' }}</td>
                                        <td>
                                            <div>{{ $plan->is_public ? __('app.subscription.admin.public') : __('app.subscription.admin.private') }}</div>
                                            <div class="small text-body-secondary">{{ $plan->is_active ? __('app.subscription.admin.active') : __('app.subscription.admin.inactive') }}</div>
                                        </td>
                                        <td class="text-nowrap">
                                            <form method="POST" action="{{ route('client.admin.owner-subscriptions.plans.destroy', $plan) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('app.subscription.admin.plans.delete_confirm', ['name' => $plan->name]) }}')">
                                                    {{ __('app.rental.common.delete') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="bg-body-tertiary">
                                            <form method="POST" action="{{ route('client.admin.owner-subscriptions.plans.update', $plan) }}" class="row g-3 p-3">
                                                @csrf
                                                @method('PUT')
                                                @include('client.admin.owner-subscriptions.partials.plan-form', ['plan' => $plan, 'prefix' => 'plan_'.$plan->id])

                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">{{ __('app.rental.common.update') }}</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if ($owners->isEmpty())
                    <div class="p-4 text-body-secondary">{{ __('app.subscription.admin.empty') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('app.client.common.full_name') }}</th>
                                    <th>{{ __('app.client.common.email') }}</th>
                                    <th>{{ __('app.subscription.fields.plan') }}</th>
                                    <th>{{ __('app.subscription.fields.property_limit') }}</th>
                                    <th>{{ __('app.subscription.fields.properties_used') }}</th>
                                    <th>{{ __('app.subscription.owner.status') }}</th>
                                    <th>{{ __('app.subscription.fields.trial_ends_at') }}</th>
                                    <th class="text-end">{{ __('app.rental.common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($owners as $owner)
                                    <tr>
                                        <td>{{ $owner->name }}</td>
                                        <td>{{ $owner->email }}</td>
                                        <td colspan="6">
                                            <form method="POST" action="{{ route('client.admin.owner-subscriptions.update', $owner) }}" class="row g-3 align-items-end">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-lg-3">
                                                    <label class="form-label">{{ __('app.subscription.fields.plan') }}</label>
                                                    <select name="subscription_plan_id" class="form-select">
                                                        @foreach ($plans as $plan)
                                                            <option value="{{ $plan->id }}" @selected($owner->subscription_plan_id === $plan->id)>
                                                                {{ $plan->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">{{ __('app.subscription.fields.property_limit') }}</label>
                                                    <input type="text" class="form-control" value="{{ $owner->ownerPlan()?->propertyLimitLabel() ?? '—' }}" disabled>
                                                </div>
                                                <div class="col-lg-1">
                                                    <label class="form-label">{{ __('app.subscription.fields.properties_used') }}</label>
                                                    <input type="text" class="form-control" value="{{ $owner->properties_count }}" disabled>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">{{ __('app.subscription.owner.status') }}</label>
                                                    <input type="text" class="form-control" value="{{ $owner->ownerHasUnlimitedPlan() ? __('app.subscription.statuses.unlimited') : ($owner->subscribed('default') ? __('app.subscription.statuses.active') : ($owner->ownerTrialActive() ? __('app.subscription.statuses.trial') : __('app.subscription.statuses.inactive')) ) }}" disabled>
                                                </div>
                                                <div class="col-lg-2">
                                                    <label class="form-label">{{ __('app.subscription.fields.trial_ends_at') }}</label>
                                                    <input name="owner_trial_ends_at" type="date" value="{{ $owner->owner_trial_ends_at?->format('Y-m-d') }}" class="form-control">
                                                </div>
                                                <div class="col-lg-2 text-lg-end">
                                                    <button type="submit" class="btn btn-primary">{{ __('app.rental.common.update') }}</button>
                                                </div>
                                            </form>
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
