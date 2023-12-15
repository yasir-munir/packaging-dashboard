<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'order_number',
        'customer_id',
        'export_order',
        'po_number',
        'order_date',
        'delivery_date',
        'notes',
        'created_at',
        'updated_at',
        'user_id',
        'Type_barcode',
        'code'
    ];

    public function details()
    {
        return $this->hasMany('App\Models\PurchaseOrderDetails');
    }
    public function clients()
        {
         return $this->belongsTo(Client::class);
     }
}
