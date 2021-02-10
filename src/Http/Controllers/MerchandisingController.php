<?php

namespace Aero\Merchandising\Http\Controllers;

use Aero\Admin\Http\Controllers\Controller;
use Aero\Admin\ResourceLists\CategoriesResourceList;
use Aero\Admin\ResourceLists\CombinationsResourceList;
use Aero\Catalog\Events\ListingsUpdated;
use Aero\Catalog\Models\Category;
use Aero\Catalog\Models\Combination;
use Aero\Catalog\Models\Tag;
use Aero\Common\Services\CombinationSerializer;
use Aero\Responses\ProcessesResponseBuilder;
use Aero\Responses\ResponseHandler;
use Aero\Search\Contracts\ListingsRepository;
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

        $combinations = $list->apply($request->all())->orWhereNull('label')
            ->paginate($request->input('per_page', 15));



        return view('merchandising::index',
            compact('combinations', 'list', 'sortBy', 'searchTerm'));
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
        if(config('queue.default') != 'sync') {
            event(new ListingsUpdated($combination->listings));
        }

        return redirect(route('admin.modules.merchandising.listings', ['combination' => $combination->id]))->with([
            'message' => __('Merchandising has been saved'),
        ]);
    }

    public function listings(Request $request)
    {
        $combination = Combination::find($request->input('combination'));

        $listings = $this->getListings($combination);

        return view('merchandising::listings', compact('listings', 'combination'));
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
