<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return str_starts_with($this->image, 'images/')
                ? asset($this->image)
                : asset('storage/'.$this->image);
        }

        $defaultImage = match ($this->category) {
            'Cuci' => 'images/laundry-wash.svg',
            'Setrika' => 'images/laundry-iron.svg',
            default => 'images/laundry-package.svg',
        };

        return asset($defaultImage);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }
}
