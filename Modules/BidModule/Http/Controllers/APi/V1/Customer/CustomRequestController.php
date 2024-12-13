<?php

namespace Modules\BidModule\Http\Controllers\APi\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\BidModule\Entities\CustomRequest;
use Modules\BidModule\Traits\CustomRequestTrait;
use Modules\CustomerModule\Traits\CustomerAddressTrait;

class CustomRequestController extends Controller
{
    use CustomRequestTrait, CustomerAddressTrait;

    protected CustomRequest $custom_request;
    protected bool $is_customer_logged_in;
    protected mixed $customer_user_id;

    // public function __construct(Booking $booking, BookingStatusHistory $booking_status_history, Request $request, OfflinePayment $offline_payment)
    // {
    //     $this->booking = $booking;
    //     $this->booking_status_history = $booking_status_history;
    //     $this->offline_payment = $offline_payment;

    //     $this->is_customer_logged_in = (bool)auth('api')->user();
    //     $this->customer_user_id = $this->is_customer_logged_in ? auth('api')->user()->id : $request['guest_id'];
    // }

    public function __construct(CustomRequest $custom_request, Request $request)
    {
        $this->custom_request = $custom_request;

        $this->is_customer_logged_in = (bool)auth('api')->user();
        $this->customer_user_id = $this->is_customer_logged_in ? auth('api')->user()->id : $request['guest_id'];
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'custom_request_status' => 'required|in:all,' . implode(',', array_column(CUSTOM_REQUEST_TYPE, 'key')),
            'string' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $custom_request = $this->custom_request->where('user_id',$request->user()->id)
            // ->when($request->has('string'), function ($query) use ($request) {
            //     $keys = explode(' ', base64_decode($request['string']));
            //     foreach ($keys as $key) {
            //         $query->orWhere('id', 'LIKE', '%' . $key . '%');
            //     }
            // })
            ->when($request['custom_request_status'] != 'all', function ($query) use ($request) {
                return $query->where('status',$request->custom_request_status);
            })
            ->with('address')
            ->latest()
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $custom_request), 200);
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
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'service_address_id' => is_null($request['service_address']) ? 'required' : 'nullable',
            'guest_id' => $this->is_customer_logged_in ? 'nullable' : 'required|uuid',
            'service_address' => is_null($request['service_address_id']) ? [
                'required',
                'json',
                function ($attribute, $value, $fail) {
                    $decoded = json_decode($value, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $fail($attribute.' must be a valid JSON string.');
                        return;
                    }

                    if (is_null($decoded['lat']) || $decoded['lat'] == '') $fail($attribute.' must contain "lat" properties.');
                    if (is_null($decoded['lon']) || $decoded['lon'] == '') $fail($attribute.' must contain "lon" properties.');
                    if (is_null($decoded['address']) || $decoded['address'] == '') $fail($attribute.' must contain "address" properties.');
                    if (is_null($decoded['contact_person_name']) || $decoded['contact_person_name'] == '') $fail($attribute.' must contain "contact_person_name" properties.');
                    if (is_null($decoded['contact_person_number']) || $decoded['contact_person_number'] == '') $fail($attribute.' must contain "contact_person_number" properties.');
                    if (is_null($decoded['address_label']) || $decoded['address_label'] == '') $fail($attribute.' must contain "address_label" properties.');
                },
            ] : '',

            'machine_name' => 'required',
            'description' => 'required',
            'no_seri' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        //service address create (if no saved address)
        if (is_null($request['service_address_id'])) {
            try {
                $service_address = $this->add_address(json_decode($request['service_address']), null, !$this->is_customer_logged_in);
                $response = $this->addCustomRequest($request,$service_address);
            } catch (\Throwable $th) {
                $response = [
                    'flag' => 'failed',
                    'message' => $th
                ];
            }
        }

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(CUSTOM_REQUEST_CREATED_SUCCESS_200, $response), 200);
        } else {
            return response()->json(response_formatter(CUSTOM_REQUEST_CREATED_FAILED_200), 200);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $custom_request = $this->custom_request
            ->with('address')
            ->find($id);

        return response()->json(response_formatter(DEFAULT_200, $custom_request), 200);
    }
    public function list(Request $request)
    {
        
        $custom_request = $this->custom_request
            ->where('user_id', $request->user()->id)
            ->get();

            return response()->json(response_formatter(DEFAULT_200, $custom_request), 200);
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

    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $custom_request_status = \array_column(\CUSTOM_REQUEST_TYPE,'value');
        try {
            $custom_request = CustomRequest::where('status',$custom_request_status[0])->findOrFail($id);
        } catch (ModelNotFoundException $th) {
            return response()->json(response_formatter(\CUSTOM_REQUEST_DATA_NOT_FOUND_200, errors:$th), 200);
        }
        $response = $this->deleteCustomRequest($custom_request);

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(CUSTOM_REQUEST_DELETED_SUCCESS_200, $response), 200);
        } else {
            return response()->json(response_formatter(CUSTOM_REQUEST_DELETED_FAILED_200,$response), 200);
        }
    }

    public function cancelCustomRequest(Request $request, $id) {

        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $response = $this->updateStatusCustomRequest($request, $id);

        if ($response['flag'] == 'success') {
            return response()->json(response_formatter(CUSTOM_RERQUEST_SUCCESS_200, $response), 200);
        } else {
            return response()->json(response_formatter(CUSTOM_RERQUEST_FAILED_200), 200);
        }

    }
}
