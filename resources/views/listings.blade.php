@extends('admin::layouts.main')

@section('content')
    <h2>
        <a href="{{ route('admin.modules.merchandising.index') }}" class="btn mr-4">« Back</a>
        <span class="flex-1">Merchandising :: {{$combination->label}}</span>
    </h2>
    @include('admin::partials.alerts')


    <div class="flex" >
        <div class="w-full mb-4">
            <form method="post" action="{{ route('admin.modules.merchandising.store')}}">
                @csrf
                <input name="combination_id" type="hidden" value="{{request()->input('combination')}}" />




                <draggable class="w-full flex flex-wrap" group="listings" @start="drag=true" @end="drag=false">

                    @foreach($listings as $listing)

                        <div class="w-1/4 px-2 -mx-2 my-3 text-center" :key="{{$listing->id}}">

                            <div class="card">
                                <div class=""><img src="/image-factory/200x200:pad/{{$listing->images[0]['file']}}"/></div>
                                <div class="font-bold pt-3 pb-2 w-full text-center mx-auto">{{$listing->manufacturer['name']}}  {{$listing->name}}</div>
                                <div class="font-normal pt-3 pb-2 w-full text-center mx-auto">{{$listing->model}}</div>
                                <div class="font-normal w-full text-center mx-auto">Stock: {!!  $listing->stock_level !!}</div>
                                <input type="hidden" style="width:100px;" name="sorts[]" :value="{{$listing->id}}" />
                            </div>

                        </div>

                    @endforeach
                </draggable>
                <div class="card mt-4 p-4 w-full fieldset-disabled-hide"><button type="submit" class="btn btn-secondary">Save</button></div>
            </form>
        </div>
    </div>


{{--    <script src='/vendor/merchandising/js/html5sortable.min.js'></script>--}}
{{--    <script>--}}


{{--        //alert(document.getElementById('merchandising'));--}}
{{--        sortable('.grid', {--}}
{{--            forcePlaceholderSize: true,--}}
{{--            placeholderClass: 'border border-2 bg-red'--}}
{{--        });--}}

{{--    </script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>--}}

{{--    <script>--}}

{{--        new Sortable(document.getElementById('merchandising'), {--}}
{{--            swap: true, // Enable swap plugin--}}
{{--            swapClass: 'highlight', // The class applied to the hovered swap item--}}
{{--            animation: 150--}}
{{--        });--}}
{{--    </script>--}}
@endsection
@push('scripts')


@endpush


