<?php

namespace Modules\Telemetry\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ProductManagement\Models\Product;
use App\Models\User;

class UserProductView extends Model
{
    protected $table = 'user_product_views';

    protected $fillable = [
        'product_id',
        'user_id',
        'session_id',
        'dwell_time_seconds',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
