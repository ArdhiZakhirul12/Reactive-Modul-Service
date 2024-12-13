<?php

namespace Modules\AdminModule\Http\Controllers\Web\Admin;

use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\BidModule\Entities\CustomRequest;
use Modules\BookingModule\Entities\Booking;
use Modules\BookingModule\Entities\BookingDetailsAmount;
use Modules\ChattingModule\Entities\ChannelList;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ServiceManagement\Entities\Service;
use Modules\TransactionModule\Entities\Account;
use Modules\TransactionModule\Entities\Transaction;
use Modules\UserManagement\Entities\User;
use function auth;
use function bcrypt;
use function file_uploader;
use function response;
use function response_formatter;
use function view;

class AdminController extends Controller
{
    protected Provider $provider;
    protected CustomRequest $customRequest;
    protected Account $account;
    protected Booking $booking;
    protected Service $service;
    protected User $user;
    protected Transaction $transaction;
    protected ChannelList $channelList;
    protected BookingDetailsAmount $booking_details_amount;

    public function __construct(ChannelList $channelList, Provider $provider, Service $service, Account $account, Booking $booking, User $user, Transaction $transaction, BookingDetailsAmount $booking_details_amount, CustomRequest $customRequest)
    {
        $this->provider = $provider;
        $this->customRequest = $customRequest;
        $this->service = $service;
        $this->account = $account;
        $this->booking = $booking;
        $this->user = $user;
        $this->transaction = $transaction;
        $this->channelList = $channelList;
        $this->booking_details_amount = $booking_details_amount;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @param Transaction $transaction
     * @return Application|Factory|View
     */
    public function dashboard(Request $request, Transaction $transaction)
    {
        //earning
        $commission_earning = BookingDetailsAmount::whereHas('booking', function ($query) use ($request) {
            $query->ofBookingStatus('completed');
        })
            ->sum('admin_commission');

        $fee_amounts = $this->transaction->where('trx_type', TRX_TYPE['received_extra_fee'])->sum('credit');

        $data = [];
        $data[] = [
            'top_cards' => [
                'total_commission_earning' => $commission_earning ?? 0,
                'total_fee_earning' => $fee_amounts ?? 0,
                'total_system_earning' => $this->account->sum('received_balance') + $this->account->sum('total_withdrawn'),
                'total_customer' => $this->user->where(['user_type' => 'customer'])->count(),
                'total_provider' => $this->provider->where(['is_approved' => 1])->count(),
                'total_services' => $this->service->count()
            ]
        ];

        $total_earning = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request) {
                $query->ofBookingStatus('completed');
            })->get()->sum('admin_commission');

        $data[] = ['admin_total_earning' => $total_earning];

        $recent_transactions = $this->transaction
            ->with(['booking'])
            ->whereMonth('created_at', now()->month)
            ->latest()
            ->take(5)
            ->get();
        $data[] = [
            'recent_transactions' => $recent_transactions,
            'this_month_trx_count' => $transaction->count()
        ];

        $bookings = $this->booking->with([
            'detail.service' => function ($query) {
                $query->select('id', 'name', 'thumbnail');
            }
        ])
            ->where('booking_status', 'pending')
            ->take(5)->latest()->get();
        $data[] = ['bookings' => $bookings];

        $top_providers = $this->provider
            ->withCount(['reviews'])
            ->with(['owner', 'reviews'])
            ->ofApproval(1)
            ->take(5)->orderBy('avg_rating', 'DESC')->get();
        $data[] = ['top_providers' => $top_providers];

        $zone_wise_bookings = $this->booking
            ->with([
                'zone' => function ($query) {
                    $query->withoutGlobalScope('translate');
                }
            ])
            ->whereHas('zone', function ($query) {
                $query->ofStatus(1)->withoutGlobalScope('translate');
            })
            ->whereMonth('created_at', now()->month)
            ->select('zone_id', DB::raw('count(*) as total'))
            ->groupBy('zone_id')
            ->get();
        $data[] = ['zone_wise_bookings' => $zone_wise_bookings, 'total_count' => $this->booking->count()];
        $pending = $this->booking
            ->where('booking_status', 'pending')
            ->count();
        $ongoing = $this->booking
            ->whereIn('booking_status', ['ongoing', 'online', 'accepted'])
            ->count();
        $completed = $this->booking
            ->where('booking_status', 'completed')
            ->count();
        $custom = $this->customRequest
            ->count();
        // dd($custom);
        $data[] = ['total_pending' => $pending, 'total_ongoing' => $ongoing, 'total_completed' => $completed, 'total_custom' => $custom];

        $recent_pending_sparepart = $this->booking
            // ->with()
            ->where('booking_status', 'pendingSparepart')
            ->latest()
            ->take(5)
            ->get();
        $data[] = ['recent_pending_sparepart' => $recent_pending_sparepart];

        $top_categories = $this->booking
            ->with('subCategory')
            ->select(
                'sub_category_id',
                DB::raw('count(sub_category_id) as category_count')
            )
            ->groupBy('sub_category_id')
            ->orderBy('category_count', 'desc')
            ->limit(5)
            ->get()
            ->toArray();

        // dd($top_categories[0]['sub_category']['name']);
        $data[] = ['top_category' => $top_categories];

        $year = session()->has('dashboard_earning_graph_year') ? session('dashboard_earning_graph_year') : date('Y');
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->where('trx_type', TRX_TYPE['received_extra_fee'])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();



        $all_earnings = [];
        foreach ($amounts as $amount) {
            foreach ($fee_amounts as $key => $fee) {
                if ($amount['month'] == $fee['month']) {
                    $all_earnings[$key] = array_merge($amount, $fee);
                }
                if (!isset($all_earnings[$key])) {
                    $all_earnings[$key] = $amount;
                }
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        }

        $all_booking = $this->booking
            ->select(

                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(id) as booking'),
                DB::raw('count(case when booking_status = "completed" then 1 end) as completed_booking')
            )
            ->groupby('month')->get()->toArray();
        // dd($all_booking);

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {


            $found = 0;
            foreach ($all_booking as $key => $item) {
                // dd($item['booking']);
                if ($item['month'] == $month) {
                    // $chart_data['total_earning'][] = with_decimal_point($item['admin_commission'] + $item['fee']);
                    // $chart_data['commission_earning'][] = with_decimal_point($item['admin_commission']);
                    $chart_data['booking'][] = $item['booking'];
                    $chart_data['completed_booking'][] = $item['completed_booking'];
                    $found = 1;
                }
            }
            if (!$found) {
                // $chart_data['total_earning'][] = with_decimal_point(0);
                // $chart_data['commission_earning'][] = with_decimal_point(0);
                $chart_data['booking'][] = 0;
                $chart_data['completed_booking'][] = 0;
            }
        }
        $servicemenList = [];
        $servicemen = $this->booking
                        ->with('serviceman')
                        ->whereIn('booking_status',['accepted','ongoing','customerAgrees',])
                        ->whereNotNull('serviceman_id')
                        ->orderBy('updated_at', 'asc')
                        ->get()
                        ->unique('serviceman_id'); 
        foreach($servicemen as $serviceman){
            $servicemenList['id'][] = $serviceman->serviceman->user->id;
            $servicemenList['photo'][] = $serviceman->serviceman->user->profile_image;
            $servicemenList['name'][] = $serviceman->serviceman->user->first_name .' '. $serviceman->serviceman->user->last_name;
            $servicemenList['status'][] = ucfirst($serviceman->booking_status);           
        };
        $userServiceman = $this->user
                            ->where('user_type','provider-serviceman');
                            if(count($servicemenList)>0){
                                $userServiceman = $userServiceman->whereNotIn('id', $servicemenList['id']);
                            }
                            $userServiceman = $userServiceman->get();
        
        foreach ($userServiceman as $item) {
            $servicemenList['id'][] = $item->id;
            $servicemenList['photo'][] = $item->serviceman->user->profile_image;
            $servicemenList['name'][] = $item->first_name .' '. $item->last_name;
            $servicemenList['status'][] = 'Free'; 
        }

        // dd($chart_data);
        return view('adminmodule::dashboard', compact('data', 'chart_data','servicemenList'));
    }


    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDashboardEarningGraph(Request $request): JsonResponse
    {
        $year = $request['year'];
        $amounts = $this->booking_details_amount
            ->whereHas('booking', function ($query) use ($request, $year) {
                $query->whereYear('created_at', '=', $year)->ofBookingStatus('completed');
            })
            ->select(
                DB::raw('sum(admin_commission) as admin_commission'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $fee_amounts = $this->transaction
            ->whereYear('created_at', '=', $year)
            ->where('trx_type', TRX_TYPE['received_extra_fee'])
            ->select(
                DB::raw('sum(credit) as fee'),

                DB::raw('MONTH(created_at) month')
            )
            ->groupby('month')->get()->toArray();

        $all_earnings = [];
        foreach ($amounts as $amount) {
            foreach ($fee_amounts as $key => $fee) {
                if ($amount['month'] == $fee['month']) {
                    $all_earnings[$key] = array_merge($amount, $fee);
                }
                if (!isset($all_earnings[$key])) {
                    $all_earnings[$key] = $amount;
                }
                if (!array_key_exists('fee', $all_earnings[$key])) {
                    $all_earnings[$key]['fee'] = 0;
                }
            }
        }

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        foreach ($months as $month) {
            $found = 0;
            foreach ($all_earnings as $key => $item) {
                if ($item['month'] == $month) {
                    $chart_data['total_earning'][] = with_decimal_point($item['admin_commission'] + $item['fee']);
                    $chart_data['commission_earning'][] = with_decimal_point($item['admin_commission']);
                    $found = 1;
                }
            }
            if (!$found) {
                $chart_data['total_earning'][] = with_decimal_point(0);
                $chart_data['commission_earning'][] = with_decimal_point(0);
            }
        }

        session()->put('dashboard_earning_graph_year', $request['year']);

        return response()->json($chart_data);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            $user = $this->user->where(['id' => auth('api')->id()])->with(['roles'])->first();
            return response()->json(response_formatter(DEFAULT_200, $user), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request): JsonResponse
    {
        if (in_array($request->user()->user_type, ADMIN_USER_TYPES)) {
            return response()->json(response_formatter(DEFAULT_200, auth('api')->user()), 200);
        }
        return response()->json(response_formatter(DEFAULT_403), 401);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function profileInfo(Request $request): Renderable
    {
        return view('adminmodule::admin.profile-update');
    }

    /**
     * Modify provider information
     * @param Request $request
     * @return RedirectResponse
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'profile_image' => 'image|mimes:jpeg,jpg,png,gif|max:10240',
            'password' => '',
            'confirm_password' => !is_null($request->password) ? 'required|same:password' : '',
        ]);

        $user = $this->user->find($request->user()->id);
        $user->first_name = $request->first_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->last_name = $request->last_name;
        if ($request->has('profile_image')) {
            $user->profile_image = file_uploader('user/profile_image/', 'png', $request->profile_image, $user->profile_image);
        }
        if (!is_null($request->password)) {
            $user->password = bcrypt($request->confirm_password);
        }
        $user->save();

        Toastr::success(translate(DEFAULT_UPDATE_200['message']));
        return back();
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getUpdatedData(Request $request): JsonResponse
    {
        $message = $this->channelList->wherehas('channelUsers', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id)->where('is_read', 0);
        })->count();

        return response()->json([
            'status' => 1,
            'data' => [
                'message' => $message
            ]
        ]);
    }
}
