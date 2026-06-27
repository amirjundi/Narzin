<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\CityFactory;

class City extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'cites';

    protected $fillable = [
        'name',
        'price',
        'fast_price'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    // protected static function newFactory(): CityFactory
    // {
    //     // return CityFactory::new();
    // }
}
