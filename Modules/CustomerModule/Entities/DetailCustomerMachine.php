<?php

namespace Modules\CustomerModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ServiceManagement\Entities\RecentSearch;
use Modules\UserManagement\Entities\User;
use Modules\CategoryManagement\Entities\Category;
use Modules\CustomerModule\Entities\CustomerMachine;



class DetailCustomerMachine extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];

    public function customer_machine(): BelongsTo
    {
        return $this->belongsTo(CustomerMachine::class, 'user_machine_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id')->withoutGlobalScopes();
    }
    
    protected static function newFactory()
    {
        return \Modules\CustomerModule\Database\factories\DetailCustomerMachineFactory::new();
    }
    
}
