<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalles';
    protected $primaryKey = 'id_venta_detalle';

    public $timestamps = true;

    protected $fillable = [
        'id_venta',
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

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta', 'id_venta');
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'id_variante', 'id_variante');
    }
}
