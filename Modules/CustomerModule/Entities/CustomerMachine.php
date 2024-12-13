<?php

namespace Modules\CustomerModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\ServiceManagement\Entities\RecentSearch;
use Modules\UserManagement\Entities\User;
use Modules\CategoryManagement\Entities\Category;
use Modules\CustomerModule\Entities\DetailCustomerMachine;


class CustomerMachine extends Model
{
    use HasFactory, HasUuid;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detail_customer_machine(): HasMany
    {
        return $this->HasMany(DetailCustomerMachine::class,'user_machine_id');
    }
    
    protected static function newFactory()
    {
        return \Modules\CustomerModule\Database\factories\CustomerMachineFactory::new();
    }
}
