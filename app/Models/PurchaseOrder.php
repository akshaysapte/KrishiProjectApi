<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;


    public function getFarmerOrderDetaill(){
		return $this->belongsTo('App\Models\FarmerOrder', 'farmer_order_id', 'id');
	}

    public function getFarmerDetail(){
		return $this->belongsTo('App\Models\Farmer', 'farmer_id', 'id');
	}


}
