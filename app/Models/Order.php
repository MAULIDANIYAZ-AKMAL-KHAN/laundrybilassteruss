<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        if (! $this->phone_number) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $this->phone_number);

        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        } elseif (str_starts_with($number, '8')) {
            $number = '62'.$number;
        }

        return 'https://wa.me/'.$number;
    }
}
