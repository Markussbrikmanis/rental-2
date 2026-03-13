@extends('client.layout', ['title' => __('app.properties.create.page_title')])

@section('content')
    <div class="card border-0 shadow-sm my-4">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h2 mb-2">{{ __('app.properties.create.heading') }}</h1>
            <p class="text-body-secondary mb-4">{{ __('app.properties.create.description') }}</p>

            <form method="POST" action="{{ route('client.properties.store') }}">
                @include('client.properties._form', [
                    'submitLabel' => __('app.properties.actions.save'),
                ])
            </form>
        </div>
    </div>
@endsection
