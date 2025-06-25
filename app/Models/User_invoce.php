<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_invoce extends Model
{
    use HasFactory;

    protected $guarded=[];
    protected $table = 'user_invoces';

    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'city',
        'state',
        'zipcode',
        'country',
        'vatnumber',
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setGuarded($guarded): self {
		$this->guarded = $guarded;
		return $this;
	}
}
