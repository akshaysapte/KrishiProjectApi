<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public function getFarmerDetail(){
		return $this->belongsTo('App\Models\Farmer', 'farmer_merchant_id', 'id');
	}

    public function getMerchantDetail(){
		return $this->belongsTo('App\Models\Merchant', 'farmer_merchant_id', 'id');
	}

}
