<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetails extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'purchase_order_id',
        'category_id',
        'costing_id',
        'carton_size',
        'cost',
        'ply',
        'quantity',
        'unit_price',
        'paper_type',
        'status',
        'created_at',
        'updated_at',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo('App\Models\PurchaseOrder');
    }

}
