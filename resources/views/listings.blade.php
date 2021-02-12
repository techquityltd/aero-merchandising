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
                    <button class="btn btn-primary sortby sortby_stock">Sort by Stock</button>
                    @isset($tags)
                        @foreach($tags as $tag => $value)
                            <button class="btn btn-primary sortby sortby_{{$value->name}}">Sort by {{$value->name}}</button>
                        @endforeach
                    @endisset
                </div>

                <div id="sortable" class="w-full flex flex-wrap">

                    @foreach($listings as $listing)

                        <div class="w-1/4 my-3 text-center sortable" key="{{$listing->id}}">

                            <div class="card">
                                <div class=""><img src="/image-factory/200x200:pad/{{$listing->images[0]['file']}}"/></div>
                                <div class="font-bold pt-3 pb-2 w-full text-center mx-auto">{{$listing->manufacturer['name']}}  {{$listing->name}}</div>
                                <div class="font-normal pt-3 pb-2 w-full text-center mx-auto">{{$listing->model}}</div>
                                <div class="font-normal w-full text-center mx-auto mb-2">Stock: {!!  $listing->stock_level !!}</div>
                                <input type="hidden" style="width:100px;" name="sorts[]" value="{{$listing->id}}" />
                                <input class="stock" type="hidden" style="width:100px;" name="stock[]" value="{{  $listing->stock_level }}" />
                                @if(isset($listing->product->tags) && isset($tags) )
                                    @foreach($tags as $tag => $value)

                                        @forelse($listing->product->tags->whereIn('tag_group_id', $value->id) as $tag)
                                            <input class="{{$value->name}}" type="hidden" style="width:100px;" name="{{$value->name}}[]" value="{{$tag->name}}" />
                                        @empty
                                            <input class="{{$value->name}}" type="hidden" style="width:100px;" name="{{$value->name}}[]" value="ZZ99" />
                                        @endforelse
                                    @endforeach
                                @else
                                    <input class="{{$value->name}}" type="hidden" style="width:100px;" name="{{$value->name}}[]" value="ZZ99" />
                                @endif
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

        if (event.target.matches('.sortby_stock')) {
            tinysort("div#sortable>div",{selector:'.stock', useVal:true,order:'desc'});
        }
        @isset($tags)
            @foreach($tags as $tag => $value)
                if (event.target.matches('.sortby_{{$value->name}}')) {
                    //alert('{{$tag}}');
                    tinysort("div#sortable>div",{selector:'.{{$value->name}}', useVal:true,order:'asc'});
                }
            @endforeach
        @endisset



    }, false);

</script>
@endpush


