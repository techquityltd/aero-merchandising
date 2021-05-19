<?php

namespace Aero\Merchandising\Http\Controllers;

use Aero\Admin\Http\Controllers\Controller;
use Aero\Admin\ResourceLists\CategoriesResourceList;
use Aero\Admin\ResourceLists\CombinationsResourceList;
use Aero\Catalog\Events\ListingsUpdated;
use Aero\Catalog\Models\Category;
use Aero\Catalog\Models\Combination;
use Aero\Catalog\Models\Tag;
use Aero\Catalog\Models\TagGroup;
use Aero\Catalog\Models\Product;
use Aero\Common\Services\CombinationSerializer;
use Aero\Responses\ProcessesResponseBuilder;
use Aero\Responses\ResponseHandler;
use Aero\Search\Contracts\ListingsRepository;
use Aero\Search\Elastic\Documents\ListingDocument;
use Aero\Store\Http\Responses\ListingsJson;
use Aero\Store\Http\Responses\ListingsPage;
use Aero\Store\Models\Slug;
use Aero\Store\Routing\Slugs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Container\Container;

class MerchandisingController extends Controller
{
    use ProcessesResponseBuilder;


    public function index(CombinationsResourceList $list, Request $request)
    {
        $sortBy = $request->input('sort');
        $searchTerm = $request->input('q');

        // Check for null combinations labels and try and find a match
        $this->fixNullLabels();

        $combinations = $list->apply($request->all())
            ->paginate($request->input('per_page', 15));

        return view('merchandising::index',
            compact('combinations', 'list', 'sortBy', 'searchTerm'));
    }


    public function fixNullLabels() {

        $combinations = Combination::where('label', null)->get();

        foreach ($combinations as $combination) {

            $models = \Aero\Common\Services\CombinationSerializer::deserialize($combination);

            $models = $models->map(static function ($model, $key) {
                if ($model instanceof \Illuminate\Support\Collection) {
                    $model = $model->first();
                }
                return $model;
            });

            $label = $models->map(function ($model) {
                if($model instanceof \Aero\Catalog\Models\Category) {
                    return implode(' > ', $model->breadcrumb->pluck('name')->toArray());
                }
                return $model->name ." > ";
            })->implode(' ');

            if ($label) {
                $label =  trim($label, ' > ');
                $combination->label = $label;
                $combination->save();
            }
        }
    }


    public function store(Request $request)
    {

        $combination = Combination::find($request->input('combination_id'));

        $merchandised = $request->input('sorts');

        // Clear sorts for combination
        DB::connection('mysql')->table('combination_listing')->where('combination_id', $combination->id)->delete();

        // Insert in correct order
        foreach($merchandised as $sort => $listing) {

            DB::connection('mysql')->table('combination_listing')->insert(['combination_id' => $combination->id, 'listing_id' => $listing, 'sort' => $sort]);
        }

        // Update search (if not sync as it's really slow)
        //if(config('queue.default') != 'sync') {
        event(new ListingsUpdated($combination->listings));
        //}

        return redirect(route('admin.modules.merchandising.listings', ['combination' => $combination->id]))->with([
            'message' => __('Merchandising has been saved'),
        ]);
    }

    public function listings(Request $request)
    {
        $combination = Combination::find($request->input('combination'));

        $listings = $this->getListings($combination);

        $tags = TagGroup::whereIn('id', config('merchandising.sortables'))->get();

        //dd(config('merchandising.sortables'));
        //$tags->each(function($tag) {
        //    //dd($tag->tags);
        //});


        //$listings->map(function($listing) use (&$tags) {
        //
        //    //dump($listing->product->tags->firstWhere('tag_group_id', 2));
        //    $product =  Product::find ($listing->product_id);
        //
        //    $product->tags->whereIn('tag_group_id', [2])->map(function($tag) use (&$tags) {
        //        if(!isset($tags[$tag->group->name]) || !in_array($tag->name, $tags[$tag->group->name])) {
        //            $tags[$tag->group->name][] = $tag->name;
        //        }
        //    });
        //
        //});

        //dump(($listings));

        return view('merchandising::listings', compact('listings', 'combination', 'tags'));
    }

    private function getListings(Combination $combination)
    {
        $models = CombinationSerializer::deserialize($combination);

        $models = $models->map(static function ($model, $key) {
            if ($model instanceof Collection) {
                $model = $model->first();
            }
            return $model;
        });

        $slugs = app(Slugs::class);

        $models->each(function ($model) use ($slugs) {
            $slugs->add($model->slug);
        });

        $result = app(ListingsRepository::class)->apply(new ParameterBag())
            ->search(null, collect([$combination]))
            ->paginate(1000);

        $order = DB::connection('mysql')->table('combination_listing')->select('listing_id')->where('combination_id', $combination->id)->orderBy('sort')->get()->pluck('listing_id')->toArray();

        if($order) {
            $return = $result->toArray()['listings']->sortBy(function($model) use ($order){
                return array_search($model->getKey(), $order);
            });
        }

        return $return ?? $result->toArray()['listings'];
    }
}
