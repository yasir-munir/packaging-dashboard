<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostingDetail extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'date',
        'costing_id',
        'ply_no',
        'paper_type',
        'paper_id',
        'paper_bf',
        'paper_rate',
        'paper_grams',
        'paper_flute_factor',
        'paper_weight',
        'paper_approx',
        'paper_cost',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function costing()
    {
        return $this->belongsTo(Costing::class);
    }
    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
