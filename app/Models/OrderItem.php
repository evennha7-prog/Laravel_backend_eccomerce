<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',      // 🔴 MUST be fillable
        'product_id',
        'quantity',
        'price',
    ];

    // Belongs to an Order
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Belongs to a Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
