<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Admin\Database\Factories\StatusFactory;

class Status extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'type',
    ];


    /**
     * The table associated with the model.
     */

    protected $table = 'status';


    





    // protected static function newFactory(): StatusFactory
    // {
    //     // return StatusFactory::new();
    // }
}
