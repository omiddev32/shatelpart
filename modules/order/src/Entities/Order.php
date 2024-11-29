<?php

namespace App\Order\Entities;

use App\Core\BasicEntity;
use App\Payment\Entities\Transaction;
use App\Product\Entities\Product;
use App\User\Entities\User;
use App\Vendor\Entities\Vendor;

class Order extends BasicEntity
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['variant'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'paid_at' => 'datetime',
        'delivery_date' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
    /**
     * The ordr connected to certain user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The ordr connected to certain product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The ordr connected to certain variant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant()
    {
        return $this->belongsTo(\App\Product\Entities\ProductVariant::class)->with('currency');
    }

    /**
     * The ordr connected to certain vendor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get transaction for the order.
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }
}
