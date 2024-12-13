<?php

namespace Modules\CustomerModule\Http\Controllers\Api\V1\Customer;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\UserManagement\Entities\User;
use Modules\CustomerModule\Entities\DetailCustomerMachine;
use Modules\CustomerModule\Entities\CustomerMachine;
use Modules\CategoryManagement\Entities\Category;
use Modules\ServiceManagement\Entities\Service;


class CustomerMachineController extends Controller
{



    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user(); // Mendapatkan pengguna yang sedang login

        if ($user) {
            $mesinUser = CustomerMachine::withWhereHas('detail_customer_machine.category.services', function ($q) {
                $q->withoutGlobalScope('zone_wise_data');
            })->where('user_id', $user->id)->get();

            if ($mesinUser) {
                $mesin = $mesinUser->flatMap(function ($item) {
                    return $item->detail_customer_machine->map(function ($detail) {
                        // return $detail->category;
                        return [
                            // $detail->category,
                            $detail,
                            Storage::url('app/public/category/'.$detail->category->image)
                        ];
                        // return Storage::disk('public')->url('category/'.$detail->category->image);
                    });
                });
                // $machine_image = $mesinUser->flatMap(function ($item) {
                //     return $item->detail_customer_machine->map(function ($detail) {
                //         return is_null($detail->category) ? null : Storage::disk('public')->url('storage/category'.$detail->category->image);
                //     });
                // });
                return response()->json(response_formatter(DEFAULT_200, $mesin), 200);
            }
            return response()->json(response_formatter(DEFAULT_404), 404);
        }
        return response()->json(response_formatter(AUTH_LOGIN_404), 404);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('customermodule::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'uuid | nullable',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'keterangan' => 'string | nullable',
            'serial_numbers' => 'array',
            'serial_numbers.*' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }
        $user_cek = Customermachine::all();
        $auth = auth('api')->user()->id;
        $found = $user_cek->contains('user_id', $auth); //mengecek apakah user tersebut sudah pernah membuat daftar mesin

        if (!$found) {
            $create_user_machine = CustomerMachine::create(['user_id' => $auth]);
            foreach ($request->category_ids as $key => $item) {
                DetailCustomerMachine::create([
                    'user_machine_id' => $create_user_machine->id,
                    'category_id' => $item,
                    'serial_number' => $request->serial_numbers[$key]
                ]);
            }

        } else {
            $user = Customermachine::where('user_id', auth('api')->user()->id)->pluck('id')->first();
            foreach ($request->category_ids as $key => $item) {
                DetailCustomerMachine::create([
                    'user_machine_id' => $user,
                    'category_id' => $item,
                    'serial_number' => $request->serial_numbers[$key]
                ]);
                // $cust_machine = DetailCustomerMachine::where('user_machine_id', $user)->get();
                // $found_machine = $cust_machine->contains('category_id', $item);
                // if ($found_machine) {
                //     return response()->json(response_formatter(DEFAULT_400, 'Anda sudah mendaftarkan mesin yang sama sebelumnya'), 400);

                // } else {
                // }
            }
        }
        return response()->json(response_formatter(DEFAULT_200), 200);

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('customermodule::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id): jsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keterangan' => 'string'
        ]);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $user_machine = CustomerMachine::create(['user_id' => $request->user_id]);

        foreach ($request->category_ids as $key => $item) {
            DetailCustomerMachine::create([
                'user_machine_id' => $user_machine->id,
                'category_id' => $item
            ]);
        }



        return response()->json(response_formatter(DEFAULT_200, $request->all()), 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        DetailCustomerMachine::destroy($id);

        return response()->json(response_formatter(DEFAULT_200, ), 200);

    }
}
