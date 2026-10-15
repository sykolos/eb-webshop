<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded =[];

    public static function statuses(): array
    {
        return ['függőben', 'feldolgozás', 'kiszállítva', 'törölve'];
    }

    public static function allowedStatuses(): array
    {
        // A migration régi alapértékét a meglévő rendelések miatt megtartjuk.
        return array_merge(self::statuses(), ['pending']);
    }

    public static function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::nextStatuses($from), true);
    }

    public static function nextStatuses(string $from): array
    {
        $from = $from === 'pending' ? 'függőben' : $from;
        $transitions = [
            'függőben' => ['feldolgozás', 'törölve'],
            'feldolgozás' => ['kiszállítva', 'törölve'],
            'kiszállítva' => [],
            'törölve' => [],
        ];

        return $transitions[$from] ?? [];
    }

    public function statusOptions(): array
    {
        return array_values(array_unique(array_merge(
            [$this->status],
            self::nextStatuses((string) $this->status)
        )));
    }

    public function user(){
        return $this->belongsTo(User::class,);
    }
    public function items(){
        return $this->hasMany(Item::class);
    }
    public function user_shipping()
    {
        return $this->belongsTo(User_shipping::class, 'shipping_address_id');
    }
    public function user_invoice()
    {
        return $this->hasOne(User_invoce::class, 'user_id', 'user_id');
    }

    public function displayNumber(): string
    {
        return $this->order_number ?: 'EBR-LEGACY-'.$this->id;
    }
}
