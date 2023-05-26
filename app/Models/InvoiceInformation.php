<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceInformation extends Model
{
    use HasFactory;

    public function getFruitDetail(){
		return $this->belongsTo('App\Models\Fruit', 'fruit_id', 'id');
	}

}
