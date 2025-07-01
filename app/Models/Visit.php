<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = ['count'];

    public static function incrementCounter()
    {
        // Cada visita es un registro nuevo
        self::create();
    }

    public static function getCount()
    {
        // El contador es el total de registros
        return self::count();
    }
}
