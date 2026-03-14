<div class="col-md-6 col-xl-3">
    <label class="form-label" for="{{ $prefix }}_name">{{ __('app.subscription.fields.plan_name') }}</label>
    <input id="{{ $prefix }}_name" name="name" type="text" class="form-control" value="{{ old('name', $plan->name) }}" required>
</div>

<div class="col-md-6 col-xl-3">
    <label class="form-label" for="{{ $prefix }}_slug">{{ __('app.subscription.fields.slug') }}</label>
    <input id="{{ $prefix }}_slug" name="slug" type="text" class="form-control" value="{{ old('slug', $plan->slug) }}" required>
</div>

<div class="col-md-6 col-xl-3">
    <label class="form-label" for="{{ $prefix }}_display_price">{{ __('app.subscription.fields.price_display') }}</label>
    <input id="{{ $prefix }}_display_price" name="display_price" type="text" class="form-control" value="{{ old('display_price', $plan->display_price) }}" required>
</div>

<div class="col-md-6 col-xl-3">
    <label class="form-label" for="{{ $prefix }}_stripe_price_id">{{ __('app.subscription.fields.stripe_price_id') }}</label>
    <input id="{{ $prefix }}_stripe_price_id" name="stripe_price_id" type="text" class="form-control" value="{{ old('stripe_price_id', $plan->stripe_price_id) }}">
</div>

<div class="col-12">
    <label class="form-label" for="{{ $prefix }}_description">{{ __('app.subscription.fields.description') }}</label>
    <textarea id="{{ $prefix }}_description" name="description" rows="2" class="form-control">{{ old('description', $plan->description) }}</textarea>
</div>

<div class="col-md-4 col-xl-2">
    <label class="form-label" for="{{ $prefix }}_currency">{{ __('app.subscription.fields.currency') }}</label>
    <input id="{{ $prefix }}_currency" name="currency" type="text" class="form-control" value="{{ old('currency', $plan->currency) }}" maxlength="3" required>
</div>

<div class="col-md-4 col-xl-2">
    <label class="form-label" for="{{ $prefix }}_billing_interval">{{ __('app.subscription.fields.billing_interval') }}</label>
    <select id="{{ $prefix }}_billing_interval" name="billing_interval" class="form-select" required>
        @foreach (['day', 'week', 'month', 'year'] as $interval)
            <option value="{{ $interval }}" @selected(old('billing_interval', $plan->billing_interval) === $interval)>{{ __('app.subscription.intervals.'.$interval) }}</option>
        @endforeach
    </select>
</div>

<div class="col-md-4 col-xl-2">
    <label class="form-label" for="{{ $prefix }}_property_limit">{{ __('app.subscription.fields.property_limit') }}</label>
    <input id="{{ $prefix }}_property_limit" name="property_limit" type="number" min="1" class="form-control" value="{{ old('property_limit', $plan->property_limit) }}">
</div>

<div class="col-md-4 col-xl-2">
    <label class="form-label" for="{{ $prefix }}_trial_days">{{ __('app.subscription.fields.trial_days') }}</label>
    <input id="{{ $prefix }}_trial_days" name="trial_days" type="number" min="1" class="form-control" value="{{ old('trial_days', $plan->trial_days) }}">
</div>

<div class="col-md-4 col-xl-2">
    <label class="form-label" for="{{ $prefix }}_sort_order">{{ __('app.subscription.fields.sort_order') }}</label>
    <input id="{{ $prefix }}_sort_order" name="sort_order" type="number" min="0" class="form-control" value="{{ old('sort_order', $plan->sort_order) }}">
</div>

<div class="col-md-4 col-xl-2">
    <div class="form-check mt-md-4 pt-md-2">
        <input id="{{ $prefix }}_trial_enabled" name="trial_enabled" type="checkbox" value="1" class="form-check-input" @checked(old('trial_enabled', $plan->trial_enabled))>
        <label class="form-check-label" for="{{ $prefix }}_trial_enabled">{{ __('app.subscription.fields.trial_enabled') }}</label>
    </div>
</div>

<div class="col-md-4 col-xl-2">
    <div class="form-check mt-md-4 pt-md-2">
        <input id="{{ $prefix }}_is_active" name="is_active" type="checkbox" value="1" class="form-check-input" @checked(old('is_active', $plan->is_active))>
        <label class="form-check-label" for="{{ $prefix }}_is_active">{{ __('app.subscription.fields.is_active') }}</label>
    </div>
</div>

<div class="col-md-4 col-xl-2">
    <div class="form-check mt-md-4 pt-md-2">
        <input id="{{ $prefix }}_is_public" name="is_public" type="checkbox" value="1" class="form-check-input" @checked(old('is_public', $plan->is_public))>
        <label class="form-check-label" for="{{ $prefix }}_is_public">{{ __('app.subscription.fields.is_public') }}</label>
    </div>
</div>

<div class="col-md-4 col-xl-2">
    <div class="form-check mt-md-4 pt-md-2">
        <input id="{{ $prefix }}_is_unlimited" name="is_unlimited" type="checkbox" value="1" class="form-check-input" @checked(old('is_unlimited', $plan->is_unlimited))>
        <label class="form-check-label" for="{{ $prefix }}_is_unlimited">{{ __('app.subscription.fields.is_unlimited') }}</label>
    </div>
</div>
