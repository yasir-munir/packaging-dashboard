<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReelSize extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'code', 'name',
    ];

}
