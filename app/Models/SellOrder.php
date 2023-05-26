<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellOrder extends Model
{
    use HasFactory;

    public function getMerchantDetail(){
		return $this->belongsTo('App\Models\Merchant', 'merchant_id', 'id');
	}
}
