<?php

namespace Modules\BidModule\Entities;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\UserManagement\Entities\User;
use Modules\UserManagement\Entities\UserAddress;

class CustomRequest extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = ['id','user_id','user_address_id','machine_name', 'description','no_seri'];

    /**
     * Get the user that owns the CustomRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the address that owns the CustomRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'user_address_id');
    }

    protected static function newFactory()
    {
        return \Modules\BidModule\Database\factories\CustomRequestFactory::new();
    }
}
