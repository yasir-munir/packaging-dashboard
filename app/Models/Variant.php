<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $table = 'variantsp';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'v_name', 'v_cost', 'v_price',
    ];

}
