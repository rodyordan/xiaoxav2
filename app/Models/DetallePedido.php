<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalle_pedido';
    protected $primaryKey = 'id_detalle_pedido';

    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_variante',
        'nombre_producto',
        'talla',
        'color',
        'sku',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_variante', 'id_variante');
    }
}
