<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'discount_code',
        'percentage',
        'expire_date',
    ];
    public function checkout()
    {
        return $this->hasOne(Checkout::class, 'discount_id');
    }
}
