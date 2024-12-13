<?php

namespace Modules\BidModule\Http\Controllers\Api\V1\Provider;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\CustomRequest;
use Modules\BidModule\Traits\CustomRequestTrait;
use Modules\CartModule\Entities\Cart;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;

class CustomRequestController extends Controller
{

    use CustomRequestTrait;

    private Category $category;
    private Service $service;
    private Variation $variation;
    private Cart $cart;
    private CustomRequest $custom_request;

    public function __construct(Category $category, Service $service, Variation $variation, Cart $cart, CustomRequest $custom_request)
    {
        $this->category = $category;
        $this->service = $service;
        $this->variation = $variation;
        $this->cart = $cart;
        $this->custom_request = $custom_request;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'custom_request_status' => 'required|in:all,' . implode(',', array_column(CUSTOM_REQUEST_TYPE, 'key')),
            'string' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $custom_request_status = $request->custom_request_status;

        $customRequest = $this->custom_request
            ->when($request->has('string'), function ($query) use ($request) {
                $keys = explode(' ', base64_decode($request['string']));
                foreach ($keys as $key) {
                    $query->orWhere('id', 'LIKE', '%' . $key . '%');
                }
            })
            ->when($request['custom_request_status'] != 'all', function ($query) use ($request) {
                return $query->where('status', $request->custom_request_status);
            })
            ->with('address')
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $customRequest), 200);

    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('bidmodule::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function show($id)
    {
        $post = CustomRequest::where('id', $id)->first();
        $provider = Provider::first();
        $machine = Category::where('name', $post->machine_name)->with('services', 'services.variations')->withoutGlobalScopes(['zone_wise_data'])->first();
        $services = Service::where('sub_category_id', $machine->id)->withoutGlobalScope('zone_wise_data')->get();

        // \dd($provider);
        return response()->json([
            'post' => $post,
            'provider' => $provider,
            'machine' => $machine,
            'services' => $services
        ], 200);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function store(Request $request, $custom_request_id)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'nullable|uuid',
            'service_id' => 'required|uuid',
            // 'category_id' => 'nullable|uuid',
            'sub_category_id' => 'required|uuid',
            // 'variant_keys' => 'required',
            // 'quantity' => 'required|numeric|min:1|max:1000',
            // 'quantitys.*' => 'required|numeric|min:1|max:1000',
            'service_schedule' => 'required|date',
        ]);
        $variation = $this->service->where('id', $request->service_id)->first()->name;
        $variation = str_replace(' ', '-', $variation);
        $request['quantitys'] = "[1]";
        $request['variant_keys'] = [$variation];
        // $request['variant_keys'] = json_decode($request->variant_keys, true);
        $request['quantitys'] = json_decode($request->quantitys, true);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        // dd($request->all());

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
        $booking_id = $response['booking_id'][0]->toString();
        $this->customRequestBooking($booking_id, $custom_request_id);
        return response()->json(response_formatter(DEFAULT_204), 200);

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('bidmodule::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $response = $this->rejectCustom($request->note, "Reject", $id);

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(DEFAULT_204), 200);
        }
        return response()->json(response_formatter(DEFAULT_400,), 400);

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
