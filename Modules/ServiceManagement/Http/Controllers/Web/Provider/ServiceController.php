<?php

namespace Modules\ServiceManagement\Http\Controllers\Web\Provider;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\ServiceManagement\Entities\Faq;
use Modules\ServiceManagement\Entities\Service;
use Modules\BusinessSettingsModule\Entities\Translation;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ZoneManagement\Entities\Zone;
use Modules\ServiceManagement\Entities\Tag;
use Modules\ServiceManagement\Entities\ServiceRequest;
use Auth;

class ServiceController extends Controller
{
    private Service $service;
    private Review $review;
    private SubscribedService $subscribed_service;
    private Category $category;
    private Booking $booking;
    private Zone $zone;
    private Variation $variation;
    private Faq $faq;

    public function __construct(Variation $variation, Zone $zone, Service $service, Review $review, SubscribedService $subscribed_service, Category $category, Booking $booking, Faq $faq)
    {
        $this->zone = $zone;
        $this->variation = $variation;
        $this->service = $service;
        $this->review = $review;
        $this->subscribed_service = $subscribed_service;
        $this->category = $category;
        $this->booking = $booking;
        $this->faq = $faq;
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index(Request $request): View|Factory|Application
    {
        $activeCategory = $request->has('active_category') ? $request['active_category'] : 'all';

        $subscribedIds = $this->subscribed_service->where('provider_id', $request->user()->provider->id)
            ->ofStatus(1)
            ->pluck('sub_category_id')
            ->toArray();

        $categories = $this->category->ofStatus(1)->ofType('main')
            ->whereHas('zones', function ($query) use ($request) {
                return $query->where('zone_id', $request->user()->provider->zone_id);
            })->latest()->get();

        $subCategories = $this->category->with(['services'])
            ->with(['services' => function ($query) {
                $query->where(['is_active' => 1]);
            }])
            ->withCount(['services' => function ($query) {
                $query->where(['is_active' => 1]);
            }])
            ->when($activeCategory != 'all', function ($query) use ($activeCategory) {
                $query->where(['parent_id' => $activeCategory]);
            })
            ->when($request->has('category_id') && $request['category_id'] != 'all', function ($query) use ($request) {
                $query->where('parent_id', $request['category_id']);
            })
            ->whereHas('parent.zones', function ($query) use ($request) {
                $query->where('zone_id', $request->user()->provider->zone_id);
            })
            ->whereHas('parent', function ($query) {
                $query->where('is_active', 1);
            })
            ->ofStatus(1)->ofType('sub')
            ->latest()->get();

        return view('servicemanagement::provider.available-services', compact('categories', 'subCategories', 'subscribedIds', 'activeCategory'));
    }
    public function create(Request $request): View|Factory|Application
    {
        $categories = $this->category->where('position',2)->orderBy('name')->get();
        $zones = $this->zone->ofStatus(1)->latest()->get();

        return view('servicemanagement::provider.create', compact('categories', 'zones'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {  
        // dd($request->all());
        $variations = session('variations');
        session()->forget('variations');

        $request->validate([
                'name' => 'required|max:191',
                'name.0' => 'required|max:191',
                // 'category_id' => 'required|uuid',
                'sub_category_id' => 'required|uuid',
                // 'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:10000',
                // 'description' => 'nullable',
                // 'description.0' => 'nullable',
                'short_description' => 'required',
                'short_description.0' => 'required',
                // 'thumbnail' => 'nullable',
                // 'tax' => 'required|numeric|min:0|max:100',
                // 'min_bidding_price' => 'nullable|numeric|min:0',
            ]
        );


        $tagIds = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if (isset($tags)) {
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(['tag' => $value]);
                $tag->save();
                $tagIds[] = $tag->id;
            }
        }
        $parent = $this->category->where('position',1)->first()->id;        

        $service = $this->service;
        $service->name = $request->name[array_search('default', $request->lang)];
        $service->category_id = $parent;
        $service->sub_category_id = $request->sub_category_id;
        $service->short_description = $request->short_description[array_search('default', $request->lang)];
        if($request->has('description')){
        $service->description = $request->description[array_search('default', $request->lang)];
        }
        if($request->has('cover_images')){
            $service->cover_image = file_uploader('service/', 'png', $request->file('cover_image'));
        }
        if($request->has('thumbnail')){
        $service->thumbnail = file_uploader('service/', 'png', $request->file('thumbnail'));
        }
        $service->tax = $request->tax;
        $service->min_bidding_price = $request->min_bidding_price;
        $service->save();
        $service->tags()->sync($tagIds);

        //decoding url encoded keys
        $data = $request->all();
        $data = collect($data)->map(function ($value, $key) {
            $key = urldecode($key);
            return [$key => $value];
        })->collapse()->all();

        $variationFormat = [];
      
            $zones = $this->zone->ofStatus(1)->latest()->get();
            
                foreach ($zones as $zone) {
                    $variationFormat[] = [
                        'variant' => $request->name[0],
                        'variant_key' => str_replace(' ', '-', $request->name[0]),
                        'zone_id' => $zone->id,
                        'price' => 0,
                        'service_id' => $service->id
                    ];
                }
            
        

        $service->variations()->createMany($variationFormat);

        $defaultLang = str_replace('_', '-', app()->getLocale());

        foreach ($request->lang as $index => $key) {
            if ($defaultLang == $key && !($request->name[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $service->name]
                    );
                }
            } else {

                if ($request->name[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'name'],
                        ['value' => $request->name[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->short_description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $service->short_description]
                    );
                }
            } else {

                if ($request->short_description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'short_description'],
                        ['value' => $request->short_description[$index]]
                    );
                }
            }

            if ($defaultLang == $key && !($request->description[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $service->description]
                    );
                }
            } else {

                if ($request->description[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'Modules\ServiceManagement\Entities\Service',
                            'translationable_id' => $service->id,
                            'locale' => $key,
                            'key' => 'description'],
                        ['value' => $request->description[$index]]
                    );
                }
            }
        }

        Toastr::success(translate(SERVICE_STORE_200['message']));

        return back();
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function requestList(Request $request): View|Factory|Application
    {
        $search = $request['search'];
        $requests = ServiceRequest::with(['category'])
            ->where('user_id', Auth::id())
            ->when($request->has('search'), function ($query) use ($request) {
                $keys = explode(' ', $request['search']);
                return $query->whereHas('category', function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->where('name', 'LIKE', '%' . $key . '%');
                    }
                });
            })
            ->latest()
            ->paginate(pagination_limit());

        return view('servicemanagement::provider.service.request-list', compact('requests', 'search'));
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function makeRequest(): View|Factory|Application
    {
        $categories = $this->category->ofType('main')->select('id', 'name')->get();
        return view('servicemanagement::provider.service.make-request', compact('categories'));
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeRequest(Request $request): RedirectResponse
    {
        Validator::make($request->all(), [
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|uuid',
            'service_name' => 'required|max:255',
            'service_description' => 'required',
        ])->validate();

        ServiceRequest::create([
            'category_id' => strtolower($request['category_id']) == 'null' || $request['category_id'] == '' ? null : $request['category_id'],
            'service_name' => $request['service_name'],
            'service_description' => $request['service_description'],
            'status' => 'pending',
            'user_id' => $request->user()->id,
        ]);

        Toastr::success(translate(SERVICE_REQUEST_STORE_200['message']));
        return back();
    }


    public function updateSubscription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'required|uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 200);
        }

        $subscribedService = $this->subscribed_service::where('sub_category_id', $request['sub_category_id'])->where('provider_id', $request->user()->provider->id)->first();
        if (!isset($subscribedService)) {
            $subscribedService = $this->subscribed_service;
        }
        $subscribedService->provider_id = $request->user()->provider->id;
        $subscribedService->sub_category_id = $request['sub_category_id'];

        $parent = $this->category->where('id', $request['sub_category_id'])->whereHas('parent.zones', function ($query) {
            $query->where('zone_id', auth()->user()->provider->zone_id);
        })->first();

        if ($parent) {
            $subscribedService->category_id = $parent->parent_id;
            $subscribedService->is_subscribed = !$subscribedService->is_subscribed;
            $subscribedService->save();
            return response()->json(response_formatter(DEFAULT_200), 200);
        }
        return response()->json(response_formatter(DEFAULT_204), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param string $service_id
     * @return JsonResponse
     */
    public function review(Request $request, string $service_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'required|in:active,inactive,all'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $reviews = $this->review->where('provider_id', $request->user()->provider->id)->where('service_id', $service_id)
            ->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
                return $query->ofStatus(($request['status'] == 'active') ? 1 : 0);
            })->latest()->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        $ratingGroupCount = DB::table('reviews')->where('provider_id', $request->user()->provider->id)
            ->where('service_id', $service_id)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $totalAvg = 0;
        $mainDivider = 0;
        foreach ($ratingGroupCount as $count) {
            $totalAvg = round($count->review_rating / $count->total, 2);
            $mainDivider += 1;
        }

        $ratingInfo = [
            'rating_count' => $ratingGroupCount->count(),
            'average_rating' => round($totalAvg / ($mainDivider == 0 ? $mainDivider + 1 : $mainDivider), 2),
            'rating_group_count' => $ratingGroupCount,
        ];

        if ($reviews->count() > 0) {
            return response()->json(response_formatter(DEFAULT_200, ['reviews' => $reviews, 'rating' => $ratingInfo]), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }


    /**
     * Show the specified resource.
     * @param Request $request
     * @param string $id
     * @return Application|Factory|View|RedirectResponse
     */
    public function show(Request $request, string $id): View|Factory|RedirectResponse|Application
    {
        $service = $this->service->where('id', $id)->with(['category.children', 'variations.zone',
            'reviews' => function ($query) use ($request) {
            $query->where('provider_id', $request->user()->provider->id);
        },])->withCount(['bookings'])->first();
        $ongoing = $this->booking->whereHas('detail', function ($query) use ($id) {
            $query->where('service_id', $id);
        })->where(['booking_status' => 'ongoing'])->count();
        $canceled = $this->booking->whereHas('detail', function ($query) use ($id) {
            $query->where('service_id', $id);
        })->where('provider_id', $request->user()->provider->id)->where(['booking_status' => 'canceled'])->count();

        $faqs = $this->faq->latest()->where('service_id', $id)->get();

        $webPage = $request->has('review_page') ? 'review' : 'general';
        $query_param = ['web_page' => $webPage];

        $reviews = $this->review->with(['customer', 'booking'])
            ->where('service_id', $id)
            ->where('provider_id', $request->user()->provider->id)
            ->latest()->paginate(pagination_limit(), ['*'], 'review_page')->appends($query_param);

        $rating_group_count = DB::table('reviews')->where('provider_id', $request->user()->provider->id)
            ->where('service_id', $id)
            ->select('review_rating', DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        if (isset($service)) {
            $service['ongoing_count'] = $ongoing;
            $service['canceled_count'] = $canceled;
            return view('servicemanagement::provider.detail', compact('service', 'faqs', 'reviews', 'rating_group_count', 'webPage'));
        }

        Toastr::error(translate(DEFAULT_204['message']));
        return back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('servicemanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function statusUpdate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:1,0',
            'sub_category_ids' => 'required|array',
            'sub_category_ids.*' => 'uuid'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->subscribed_service->whereIn('sub_category_id', $request['sub_category_ids'])->update(['is_subscribed' => $request['status']]);

        return response()->json(response_formatter(DEFAULT_STATUS_UPDATE_200), 200);
    }

    /**
     * Show the specified resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'string' => 'required',
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $keys = explode(' ', base64_decode($request['string']));

        $service = $this->service->where(function ($query) use ($keys) {
            foreach ($keys as $key) {
                $query->orWhere('name', 'LIKE', '%' . $key . '%');
            }
        })->when($request->has('status') && $request['status'] != 'all', function ($query) use ($request) {
            if ($request['status'] == 'active') {
                return $query->where(['is_active' => 1]);
            } else {
                return $query->where(['is_active' => 0]);
            }
        })->with(['category.zonesBasicInfo'])->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        if (count($service) > 0) {
            return response()->json(response_formatter(DEFAULT_200, $service), 200);
        }
        return response()->json(response_formatter(DEFAULT_204, $service), 200);
    }
    public function ajaxChildes(Request $request, $id): JsonResponse
    {
        $categories = $this->category->ofStatus(1)->ofType('sub')->where('parent_id', $id)->orderBY('name', 'asc')->get();
        $category = $this->category->where('id', $id)->with(['zones'])->first();
        $zones = $category->zones;

        session()->put('category_wise_zones', $zones);

        $variants = $this->variation->where(['service_id' => $request['service_id']])->get();

        return response()->json([
            'template' => view('categorymanagement::admin.partials._childes-selector', compact('categories'))->render(),
            'template_for_zone' => view('servicemanagement::admin.partials._category-wise-zone', compact('zones'))->render(),
            'template_for_variant' => view('servicemanagement::admin.partials._variant-data', compact('zones'))->render(),
            'template_for_update_variant' => view('servicemanagement::admin.partials._update-variant-data', compact('zones', 'variants'))->render()
        ], 200);
    }

    public function ajaxAddVariant(Request $request): JsonResponse
    {
        $variation = [
            'variant' => $request['name'],
            'variant_key' => str_replace(' ', '-', $request['name']),
            'price' => $request['price']
        ];

        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];
        $editingVariants = session()->has('editing_variants') ? session('editing_variants') : [];

        if (!self::searchForKey($request['name'], $existingData) && !in_array(str_replace(' ', '-', $request['name']), $editingVariants)) {
            $existingData[] = $variation;
            session()->put('variations', $existingData);
        } else {
            return response()->json(['flag' => 0, 'message' => translate('already_exist')]);
        }

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::provider.partials._variant-data', compact('zones'))->render()]);
    }
    function searchForKey($variant, $array): int|string|null
    {
        foreach ($array as $key => $val) {
            if ($val['variant'] === $variant) {
                return true;
            }
        }
        return false;
    }

    public function ajaxRemoveVariant($variant_key)
    {
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : [];
        $existingData = session()->has('variations') ? session('variations') : [];

        $filtered = collect($existingData)->filter(function ($values) use ($variant_key) {
            return $values['variant_key'] != $variant_key;
        })->values()->toArray();

        session()->put('variations', $filtered);

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::provider.partials._variant-data', compact('zones'))->render()]);
    }

    public function ajaxDeleteDbVariant($variant_key, $service_id)
    {
        $zones = session()->has('category_wise_zones') ? session('category_wise_zones') : $this->zone->ofStatus(1)->latest()->get();
        $this->variation->where(['variant_key' => $variant_key, 'service_id' => $service_id])->delete();
        $variants = $this->variation->where(['service_id' => $service_id])->get();

        return response()->json(['flag' => 1, 'template' => view('servicemanagement::provider.partials._update-variant-data', compact('zones', 'variants'))->render()]);
    }
}
