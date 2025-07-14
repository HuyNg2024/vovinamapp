<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'table_class';
    public $timestamps = false; 
    protected $fillable = [
        'id',
        'id_city',
        'id_club',
        'id_atg_members',
        'ten',
        'tenen',
        'thoigian',
        'thoigianen',
        'gia',
        'diachi',
        'diachien',
        'dienthoai',
        'ten_club',
        'cluben',
        'tenhlv',
        'hlven',
        'ngaytao',
        'ngaysua'
    ];

    // const CREATED_AT = 'ngaytao';
    // const UPDATED_AT = 'ngaysua';

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (!$model->isDirty('ngaytao')) {
    //             $model->ngaytao = time(); // Trả về timestamp Unix hiện tại
    //         }
    //     });

    //     static::updating(function ($model) {
    //         if (!$model->isDirty('ngaysua')) {
    //             $model->ngaysua = time(); // Trả về timestamp Unix hiện tại
    //         }
    //     });
    // }

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club', 'id'); 
    }

    
    public function coach()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members', 'id')->where('hlv',1);
    }
    public function checkins()
    {
        return $this->hasMany(Checkin::class, 'id_class');
    }

    public function members()
    {
        return $this->hasMany(table_atg_members::class, 'id_class', 'id');
    }

     // Quan hệ với register_class (thành viên đã đăng ký lớp học này)
    public function registeredMembers()
    {
         return $this->hasMany(RegisterClass::class, 'id_class', 'id');
    }

    public function payments()
    {
        return $this->hasMany(class_payment::class, 'id_class', 'id'); 
    }



}
