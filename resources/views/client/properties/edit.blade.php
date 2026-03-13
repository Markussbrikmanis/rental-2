@extends('client.layout', ['title' => __('app.properties.edit.page_title')])

@section('content')
    <div class="card border-0 shadow-sm my-4">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h2 mb-2">{{ __('app.properties.edit.heading') }}</h1>
            <p class="text-body-secondary mb-4">
                {{ __('app.properties.edit.description', ['name' => $property->name]) }}
            </p>

            <form method="POST" action="{{ route('client.properties.update', $property) }}">
                @method('PUT')

                @include('client.properties._form', [
                    'submitLabel' => __('app.properties.actions.update'),
                ])
            </form>
        </div>
    </div>
@endsection
