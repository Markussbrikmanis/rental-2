@extends('client.layout', ['title' => __('app.rental.tenants.edit.page_title')])

@section('content')
    <div class="card border-0 shadow-sm my-4">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h2 mb-2">{{ __('app.rental.tenants.edit.heading') }}</h1>
            <p class="text-body-secondary mb-4">{{ __('app.rental.tenants.edit.description', ['name' => $tenant->full_name]) }}</p>
            <form method="POST" action="{{ route('client.tenants.update', $tenant) }}">
                @method('PUT')
                @include('client.tenants._form', ['submitLabel' => __('app.rental.common.update')])
            </form>
        </div>
    </div>
@endsection
