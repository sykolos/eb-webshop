<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Products;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_status',
        'admin_note',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:array',
        'two_factor_confirmed_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'is_admin' => 'boolean',
        'account_status' => 'string',
    ];

    public function technicalRole(): string
    {
        $role = $this->role ?: ($this->is_admin ? 'administrator' : 'partner');

        return config("permissions.aliases.$role", $role);
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = config('permissions.roles.'.$this->technicalRole(), []);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->is_admin
            && filled($this->two_factor_secret)
            && filled($this->two_factor_confirmed_at);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }

    public function lastOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }
    public function user_invoice(){
        return $this->hasOne(User_invoce::class,'user_id','id');
    }
    public function user_shipping(){
        return $this->hasMany(User_shipping::class,'user_id','id');
    }

    public function special_prices(){
        return $this->hasMany(Special_prices::class,'user_id','id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function favoriteProducts()
    {
        return $this->belongsToMany(Products::class, 'user_favorites', 'user_id', 'product_id')
            ->withTimestamps();
    }

    public function preference()
    {
        return $this->hasOne(UserPreference::class);
    }

    public function registrationRequests()
    {
        return $this->hasMany(PartnerRegistrationRequest::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    
    
    
}
