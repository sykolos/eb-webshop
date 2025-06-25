<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_unit extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function products(){
        return $this->belongTo(Products::class,'unit_id');
    }
}
