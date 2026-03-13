@extends('client.layout', ['title' => __('app.rental.meters.create.page_title')])

@section('content')
    <div class="card border-0 shadow-sm my-4">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h2 mb-2">{{ __('app.rental.meters.create.heading') }}</h1>
            <p class="text-body-secondary mb-4">{{ __('app.rental.meters.create.description') }}</p>
            <form method="POST" action="{{ route('client.meters.store') }}">
                @include('client.meters._form', ['submitLabel' => __('app.rental.common.save')])
            </form>
        </div>
    </div>
@endsection
