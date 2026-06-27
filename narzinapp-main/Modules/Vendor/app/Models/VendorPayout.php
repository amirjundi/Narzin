<?php

namespace Modules\Vendor\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayout extends Model
{
    protected $table = 'vendor_payouts';

    protected $fillable = ['vendor_id', 'amount', 'method', 'reference', 'notes', 'paid_at', 'created_by'];

    protected $casts = ['paid_at' => 'datetime', 'amount' => 'decimal:2'];
}
