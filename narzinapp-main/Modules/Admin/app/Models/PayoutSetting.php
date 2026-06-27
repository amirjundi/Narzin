<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class PayoutSetting extends Model
{
    protected $table = 'payout_settings';

    protected $fillable = [
        'default_commission_percentage',
        'default_discount_absorption_percentage',
    ];

    public static function current(): self
    {
        return static::latest('id')->first()
            ?? static::create(['default_commission_percentage' => 0, 'default_discount_absorption_percentage' => 0]);
    }
}
