<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerOrder extends Model
{
    use HasFactory;

    public function getVarietyDetail(){
		return $this->belongsTo('App\Models\Variety', 'variety_id', 'id');
	}

    public function getFarmerDetail(){
		return $this->belongsTo('App\Models\Farmer', 'farmer_id', 'id');
	}
}
