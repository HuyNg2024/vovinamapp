<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $table = 'table_district';
    public $timestamps = false;
    protected $fillable = [
        'id_city',
        'ten',
        'tenkhongdau',
        'maqh',
        'stt',
        'hienthi',
        'ngaytao',
        'ngaysua',
        'gia',
        'map_lat',
        'map_long',
    ];



    public function city()
    {
        return $this->belongsTo(City::class, 'id_city'); 
    }
}
