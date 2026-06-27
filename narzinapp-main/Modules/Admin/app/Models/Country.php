<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\CountryFactory;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */


    protected $fillable = [
        'name',
        'code',
        'flag'
    ];


    public function cities()
    {
        return $this->hasMany(City::class);
    }

    // protected static function newFactory(): CountryFactory
    // {
    //     // return CountryFactory::new();
    // }
}
