@extends('admin::layouts.main')


@section('content')
    <h2>
        <span class="flex-1">Merchandising</span>
    </h2>
    @include('admin::partials.alerts')
    <div class="card p-0">
        @include('merchandising::partials.list-table')
        {{ $combinations->appends(request()->except('page'))->links() }}
    </div>
@endsection
