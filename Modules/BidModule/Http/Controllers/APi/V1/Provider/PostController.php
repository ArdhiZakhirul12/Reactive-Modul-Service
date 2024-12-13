<?php

namespace Modules\BidModule\Http\Controllers\APi\V1\Provider;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\IgnoredPost;
use Modules\BidModule\Entities\Post;
use Modules\BidModule\Entities\CustomRequest;
use Modules\CategoryManagement\Entities\Category;
use Illuminate\Support\Facades\Auth;
use Modules\CartModule\Entities\Cart;
use Modules\BidModule\Traits\CustomRequestTrait;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;
use Modules\ProviderManagement\Entities\SubscribedService;
use function response;
use function response_formatter;

class PostController extends Controller
{
    use CustomRequestTrait;

    public function __construct(
        private Post              $post,
        private SubscribedService $subscribed_service,
        private IgnoredPost       $ignored_post,
        private Category          $category,
        private Service           $service,
        private Variation         $variation,
        private Cart         $cart,
        private Auth         $auth,
        private CustomRequest $custom_request

    ) {
    }


    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:1000000',
            'offset' => 'required|numeric|min:1|max:100000',
            'custom_request_status' => 'required|in:all,' . implode(',', array_column(CUSTOM_REQUEST_TYPE, 'key')),
            'string' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $custom_request = $this->custom_request
            ->when($request['custom_request_status'] != 'all', function ($query) use ($request) {
                return $query->where('status', $request->custom_request_status);
            })
            ->with('address')
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $custom_request), 200);
    }
    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'status' => 'in:new_request,placed_offer,booking_placed'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $subscribed_sub_categories = $this->subscribed_service
            ->where(['provider_id' => $request->user()->provider->id])
            ->where(['is_subscribed' => 1])->pluck('sub_category_id')->toArray();

        $ignored_posts = $this->ignored_post->where('provider_id', $request->user()->provider->id)->pluck('post_id')->toArray();
        $bidding_post_validity = (int)(business_config('bidding_post_validity', 'bidding_system'))->live_values;
        $posts = $this->post
            ->with(['addition_instructions', 'service', 'category', 'sub_category', 'booking', 'customer'])
            ->where('is_booked', 0)
            ->whereNotIn('id', $ignored_posts)
            ->whereIn('sub_category_id', $subscribed_sub_categories)
            ->where('zone_id', $request->user()->provider->zone_id)
            ->whereBetween('created_at', [Carbon::now()->subDays($bidding_post_validity), Carbon::now()])
            ->when($request->has('status') && $request['status'] != 'new_request', function ($query) use ($request) {
                $query->whereHas('bids', function ($query) use ($request) {
                    if ($request['status'] == 'placed_offer') {
                        $query->where('status', 'pending')->where('provider_id', $request->user()->provider->id);
                    } else if ($request['status'] == 'booking_placed') {
                        $query->where('status', 'accepted');
                    }
                });
            })
            ->when($request->has('status') && $request['status'] == 'new_request', function ($query) use ($request) {
                if ($request->user()?->provider?->service_availability && (!$request->user()?->provider?->is_suspended || !business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values)) {
                    $query->whereDoesntHave('bids', function ($query) use ($request) {
                        $query->where('provider_id', $request->user()->provider->id);
                    });
                } else {
                    $query->whereNull('id');
                }
            })
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])
            ->withPath('');

        if ($posts->count() < 1) {
            return response()->json(response_formatter(DEFAULT_404, null), 404);
        }

        $coordinates = auth()->user()->provider->coordinates ?? null;
        foreach ($posts as $post) {
            $distance = null;
            if (!is_null($coordinates) && $post->service_address) {
                $distance = get_distance(
                    [$coordinates['latitude'] ?? null, $coordinates['longitude'] ?? null],
                    [$post->service_address?->lat, $post->service_address?->lon]
                );
                $distance = ($distance) ? number_format($distance, 2) . ' km' : null;
            }
            $post->distance = $distance;
        }

        return response()->json(response_formatter(DEFAULT_200, $posts), 200);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request, $custom_request_id)
    {
        // dd($request);
        // dd(Provider::where('user_id',Auth::id())->first()->id);
        $validator = Validator::make($request->all(), [
            'provider_id' => 'nullable|uuid',
            'service_id' => 'required|uuid',
            'category_id' => 'nullable|uuid',
            'sub_category_id' => 'required|uuid',
            'variant_keys' => 'required',
            // 'quantity' => 'required|numeric|min:1|max:1000',
            'quantitys.*' => 'required|numeric|min:1|max:1000',
            'service_schedule' => 'required|date',
        ]);
        // $request['variant_keys'] = json_decode($request->variant_keys, true);
        // $request['quantitys'] = json_decode($request->quantitys, true);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $custom_request = CustomRequest::find($custom_request_id);
        $custom_user_id = $custom_request->user_id;
        $category_id = $this->category->find($request->sub_category_id)->id;

        foreach ($request['variant_keys'] as $key => $value) {
            $first_cart = new Cart();
            $variation = $this->variation
                ->where(['service_id' => $request['service_id']])
                ->where(['variant_key' => $value])
                ->first();

            if (isset($variation)) {
                $service = $this->service->find($request['service_id']);
                // $service = $this->service->withoutGlobalScope('zone_wise_data')->get();
                $checkCart = $first_cart->where([
                    'service_id' => $request['service_id'],
                    'variant_key' => $value,
                    'customer_id' => $custom_user_id
                ])->first();

                $cart = $checkCart ?? $first_cart;
                $quantity = $request['quantitys'][$key];

                // \dd($this->cart, $checkCart, $cart, $quantity);

                $basicDiscount = basic_discount_calculation($service, $variation->price * $quantity);
                $campaignDiscount = campaign_discount_calculation($service, $variation->price * $quantity);
                $subtotal = round($variation->price * $quantity, 2);

                $applicableDiscount = ($campaignDiscount >= $basicDiscount) ? $campaignDiscount : $basicDiscount;

                $tax = round((($variation->price * $quantity - $applicableDiscount) * $service['tax']) / 100, 2);

                //between normal discount & campaign discount, greater one will be calculated
                $basicDiscount = $basicDiscount > $campaignDiscount ? $basicDiscount : 0;
                $campaignDiscount = $campaignDiscount >= $basicDiscount ? $campaignDiscount : 0;

                $cart->provider_id = Provider::where('user_id', Auth::id())->first()->id;
                $cart->customer_id = $custom_user_id;
                $cart->service_id = $request['service_id'];
                $cart->category_id = $category_id;
                $cart->sub_category_id = $request['sub_category_id'];
                $cart->variant_key = $value;
                $cart->quantity = $request['quantitys'][$key];
                $cart->service_cost = $variation->price;
                $cart->discount_amount = $basicDiscount;
                $cart->campaign_discount = $campaignDiscount;
                $cart->coupon_discount = 0;
                $cart->coupon_code = null;
                $cart->is_guest = false;
                $cart->tax_amount = round($tax, 2);
                $cart->total_cost = round($subtotal - $basicDiscount - $campaignDiscount + $tax, 2);
                $cart->save();

                CustomRequest::where('id', $custom_request_id)->update(['status' => "Done"]);
            }
        }

        $response = $this->placeBookingRequest($custom_user_id, $request, 'cash-payment', 0, $custom_request);
        // dd($booking_id);
        return response()->json(response_formatter(DEFAULT_STORE_200), 200);
        // return redirect(route('provider.booking.list',['booking_status'=> 'accepted']));
    }
    /**
     * Display a listing of the resource.
     * @param $post_id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id,): JsonResponse
    {
        $custom_request = $this->custom_request
            ->with('address')
            ->find($id);

        return response()->json(response_formatter(DEFAULT_200, $custom_request), 200);
    }
    // public function show($post_id, Request $request): JsonResponse
    // {
    //     $post = $this->post
    //         ->with(['customer', 'addition_instructions', 'service', 'category', 'sub_category', 'booking', 'service_address'])
    //         ->withCount(['bids'])
    //         ->where('id', $post_id)
    //         ->first();

    //     if (!isset($post)) {
    //         return response()->json(response_formatter(DEFAULT_404, null), 404);
    //     }

    //     $coordinates = auth()->user()->provider->coordinates ?? null;
    //     $distance = null;
    //     if(!is_null($coordinates) && $post->service_address) {
    //         $distance = get_distance(
    //             [$coordinates['latitude']??null, $coordinates['longitude']??null],
    //             [$post->service_address?->lat, $post->service_address?->lon]
    //         );
    //         $distance = ($distance) ? number_format($distance, 2) .' km' : null;
    //     }
    //     $post->distance = $distance;

    //     return response()->json(response_formatter(DEFAULT_200, $post), 200);
    // }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getVariations($serviceId)
    {
        $variations = Variation::where('service_id', $serviceId)->groupBy('variant_key')->withoutGlobalScope('zone_wise_data')->get();
        return response()->json($variations);
    }
    public function decline(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $this->ignored_post->updateOrCreate(
            ['post_id' => $request['post_id'], 'provider_id' => $request->user()->provider->id],
            [
                'post_id' => $request['post_id'],
            ]
        );

        return response()->json(response_formatter(DEFAULT_200, null), 200);
    }
}
