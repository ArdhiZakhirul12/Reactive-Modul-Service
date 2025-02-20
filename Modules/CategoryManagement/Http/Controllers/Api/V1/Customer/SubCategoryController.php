<?php

namespace Modules\CategoryManagement\Http\Controllers\Api\V1\Customer;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\Provider;
use Modules\UserManagement\Entities\User as EntitiesUser;

class SubCategoryController extends Controller
{
    private Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {    
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:100000',
            'offset' => 'required|numeric|min:1|max:100000'
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $subCategories = $this->category->withoutGlobalScopes(['zone_wise_data'])->withCount('services')->with(['parent'])
            ->ofStatus(1)->ofType('sub')->latest()->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $subCategories), 200);
    }

    // irul -  untuk test get subcategory
    public function getSubCategories() : JsonResponse {
        $subCategories = $this->category->children();
        // $subCategories = DB::table('categories')->get();
        // dd($subCategories);
        return response()->json(response_formatter(DEFAULT_200, $subCategories), 200);
    }
}
