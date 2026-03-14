@extends('client.layout', ['title' => __('app.admin.users.edit.page_title', ['name' => $editedUser->name])])

@section('content')
    <div class="vstack gap-4 py-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <h1 class="h2 mb-1">{{ __('app.admin.users.edit.heading', ['name' => $editedUser->name]) }}</h1>
                <p class="text-body-secondary mb-0">{{ __('app.admin.users.edit.description') }}</p>
            </div>

            <a href="{{ route('client.admin.users.index') }}" class="btn btn-outline-secondary">
                {{ __('app.admin.users.actions.back_to_list') }}
            </a>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <form method="POST" action="{{ route('client.admin.users.update', $editedUser) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-md-6">
                        <label for="name" class="form-label">{{ __('app.client.common.full_name') }}</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $editedUser->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">{{ __('app.client.common.email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $editedUser->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label">{{ __('app.client.common.account_type') }}</label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required data-admin-user-role>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role', $editedUser->role->value) === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6" data-owner-fields>
                        <label for="subscription_plan_id" class="form-label">{{ __('app.subscription.fields.plan') }}</label>
                        <select id="subscription_plan_id" name="subscription_plan_id" class="form-select @error('subscription_plan_id') is-invalid @enderror">
                            <option value="">{{ __('app.subscription.select_plan') }}</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('subscription_plan_id', $editedUser->subscription_plan_id) === (string) $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                        @error('subscription_plan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6" data-owner-fields>
                        <label for="owner_trial_ends_at" class="form-label">{{ __('app.admin.users.fields.trial_ends_at') }}</label>
                        <input id="owner_trial_ends_at" name="owner_trial_ends_at" type="date" value="{{ old('owner_trial_ends_at', $editedUser->owner_trial_ends_at?->format('Y-m-d')) }}" class="form-control @error('owner_trial_ends_at') is-invalid @enderror">
                        @error('owner_trial_ends_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-3">
                        <button type="submit" class="btn btn-primary">{{ __('app.rental.common.update') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <h2 class="h4 mb-3">{{ __('app.admin.users.reset.heading') }}</h2>
                <p class="text-body-secondary mb-4">{{ __('app.admin.users.reset.description') }}</p>

                <form method="POST" action="{{ route('client.admin.users.send-password-reset', $editedUser) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">{{ __('app.admin.users.actions.send_password_reset') }}</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const roleField = document.querySelector('[data-admin-user-role]');
            const ownerFields = document.querySelectorAll('[data-owner-fields]');

            if (!roleField || ownerFields.length === 0) {
                return;
            }

            const syncOwnerFields = () => {
                const isOwner = roleField.value === 'owner';

                ownerFields.forEach((field) => {
                    field.style.display = isOwner ? '' : 'none';
                });
            };

            roleField.addEventListener('change', syncOwnerFields);
            syncOwnerFields();
        });
    </script>
@endsection
