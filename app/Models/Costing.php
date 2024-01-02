<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Costing extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'user_id',
        'date',
        'client_id',
        'box_size',
        'measurement',
        'quantity',
        'box_type',
        'shade',
        'paper_type',
        'ply',
        'length_cm',
        'width_cm',
        'height_cm',
        'length_inch',
        'width_inch',
        'height_inch',
        'sheet_length',
        'sheet_width',
        'sheet_count',
        'roll_one_side',
        'roll_two_side',
        'total_craft',
        'total_folding_nali',
        'total_folding',
        'total_craft_q',
        'total_folding_nali_q',
        'total_folding_q',
        'total_grams',
        'total_bs',
        'total_weight',
        'carrogation_cost',
        'waste',
        'total_cost',
        'conversion_per_kg',
        'printing',
        'lamination',
        'profit',
        'transport',
        'final_box_price',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function clients()
    {
        return $this->belongsTo(Client::class);
    }

    public function details()
    {
        return $this->hasMany(CostingDetail::Class);
    }
}
