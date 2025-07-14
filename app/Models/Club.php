<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $table = 'table_club';

    public $timestamps = false;

    protected $fillable = [
        'id_city',
        'ten',
        'diachi',
        'dienthoai',
        'thoigianhoc',
        'map_lat', 
        'map_long',
        'id_class',
        'img',
        'bank_qrcode',
        'image',
        'tenen',
        'diachien',
        'id_district',
        'id_atg_members',
        'sn',
        'tenkhongdau',
        'email',
        'hienthi',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'id_city');
    }

    public function classes()
    {
        return $this->hasMany(Classes::class, 'id_club'); 
    }

    //Dũng 
    //protected $primaryKey = 'id_club';
    public function coach()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members', 'id')->where('hlv',1);
    }
    public function members()
    {
        return $this->hasMany(table_atg_members::class, 'id_club');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'id_district');
    }

}   