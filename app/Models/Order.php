<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Belongs to User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Belongs to Address
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }
}
