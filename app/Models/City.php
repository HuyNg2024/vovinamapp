<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $table = 'table_city';
    public $timestamps = false;

    protected $fillable = [
        'ten', 
        'id_country', 
        'map_lat',
        'map_long', 
        'id_district',
        'zoom',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country');
    }

    public function clubs()
    {
        return $this->hasMany(Club::class, 'id_city');
    }

    public function districts()
    {
        return $this->hasMany(District::class, 'id_city'); 
    }
}
