<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    public function getDistrictDetail(){
		return $this->belongsTo('App\Models\District', 'district_id', 'id');
	}

    public function getTalukaDetail(){
		return $this->belongsTo('App\Models\Taluka', 'taluka_id', 'id');
	}

}
