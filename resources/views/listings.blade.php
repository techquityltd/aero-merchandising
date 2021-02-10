@extends('merchandising::layouts.main')
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


                <div class="card">
                    <button class="btn btn-primary sortby">Sort by Stock</button>
                </div>

                <div id="sortable" class="w-full flex flex-wrap">

                    @foreach($listings as $listing)

                        <div class="w-1/4 px-2 -mx-2 my-3 text-center sortable" key="{{$listing->id}}">

                            <div class="card">
                                <div class=""><img src="/image-factory/200x200:pad/{{$listing->images[0]['file']}}"/></div>
                                <div class="font-bold pt-3 pb-2 w-full text-center mx-auto">{{$listing->manufacturer['name']}}  {{$listing->name}}</div>
                                <div class="font-normal pt-3 pb-2 w-full text-center mx-auto">{{$listing->model}}</div>
                                <div class="font-normal w-full text-center mx-auto">Stock: {!!  $listing->stock_level !!}</div>
                                <input type="hidden" style="width:100px;" name="sorts[]" value="{{$listing->id}}" />
                                <input class="stock" type="text" style="width:100px;" name="stock[]" value="{{  $listing->stock_level }}" />

                            </div>

                        </div>

                    @endforeach
                </div>
                <div class="card mt-4 p-4 w-full fieldset-disabled-hide"><button type="submit" class="btn btn-secondary">Save</button></div>
            </form>
        </div>
    </div>



@endsection

@push('styles')
    <style>
        .selected .card {
            border-color: red !important;
            z-index: 1 !important;
        }
    </style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinysort/2.3.6/tinysort.min.js"></script>
<script>



    Sortable.create(sortable, {
        group: 'shared',
        multiDrag: true,
        selectedClass: "selected",
        animation: 150
    });

    document.addEventListener('click', function (event) {

        // If the clicked element doesn't have the right selector, bail
        if (!event.target.matches('.sortby')) return;

        // Don't follow the link
        event.preventDefault();

        // Log the clicked element in the console
        console.log(event.target);
        tinysort("div#sortable>div",{selector:'.stock', useVal:true,order:'desc'});
        //Sortable.sort();
    }, false);



</script>
@endpush


