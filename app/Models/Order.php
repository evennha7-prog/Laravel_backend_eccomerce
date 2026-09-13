<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'cart_id',
        'status',
        'total',
    ];

    // Relationship to OrderItem
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
