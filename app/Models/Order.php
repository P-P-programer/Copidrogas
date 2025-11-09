<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id','status','subtotal','shipping_cost','total',
        'ship_name','ship_phone','ship_address','ship_city','ship_notes'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function recetas(): HasMany
    {
        return $this->hasMany(Receta::class);
    }

    /**
     * Traduce el estado a español con badge color
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'placed' => 'En espera',
            'processing' => 'Procesando',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'completed' => 'Completado',
            'canceled' => 'Cancelado',
            default => ucfirst($this->status),
        };
    }

    /**
     * Clase CSS para badge de estado
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'placed' => 'badge-warning',
            'processing' => 'badge-info',
            'shipped' => 'badge-primary',
            'delivered', 'completed' => 'badge-success',
            'canceled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
