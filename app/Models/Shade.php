<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shade extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code', 'name',
    ];

}
