<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'code',
        'adresse',
        'email',
        'phone',
        'country',
        'city',
        'tax_number',
        'person1',
        'phone2_p1',
        'person2',
        'email_p2',
        'phone1_p2',
        'phone2_p2',
        'person3',
        'email_p3',
        'phone1_p3',
        'phone2_p3',
        'sales_tax_number',
        'state',
        'remarks',
        'status',
        'gl_acc_no',
    ];

    protected $casts = [
        'code' => 'integer',
    ];
}
