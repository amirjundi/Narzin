<?php

namespace Modules\Vendor\Models;

use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    protected $table = 'vendor_transactions';

    protected $fillable = ['vendor_id', 'type', 'amount', 'order_item_id', 'payout_id', 'description', 'created_by'];

    protected $casts = ['amount' => 'decimal:2'];
}
