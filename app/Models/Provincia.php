<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    protected $table = 'provincia';
    protected $primaryKey = 'id_provincia';
    public $timestamps = false;

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    public function distritos()
    {
        return $this->hasMany(Distrito::class, 'id_provincia', 'id_provincia');
    }
}