<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'name',
        'width',
        'weight',
        'rct',
        'top',
        'flute',
        'back',
        'dimension',
        'ply',
        'crafting',
        'paper_grams',
        'paper_type',
        'paper_shade',
        'qty',
        'cost',
        'price',
        'code',
        'image'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'qty' => 'double',
        'cost' => 'double',
        'price' => 'double',
    ];

}
