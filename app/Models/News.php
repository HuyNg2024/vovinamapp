<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $table = 'table_news';
    public $timestamps = false;

    protected $fillable = [
        'id_list', 'id_item', 'id_cat', 'id_sub', 'id_tags', 'noibat', 'tb', 'photo', 'icon', 'options',
        'tenkhongdauvi', 'tenkhongdauen', 'noidungen', 'noidungvi', 'motaen', 'motavi', 'tenen', 'tenvi', 
        'tenko', 'tenkhongdauko', 'motako', 'noidungko', 'taptin', 'link', 'link_video', 'thoigian', 'stt', 
        'hienthi', 'type', 'ngaytao', 'ngaysua', 'luotxem', 'start', 'end', 'event_location', 'start_time', 
        'event_lecturer', 'end_time', 'color', 'id_club','id_dai','id_district','lephithi', 'id'

    ];

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'id_district');
    }

    protected $casts = [
        'thoigian' => 'datetime', // Giả sử 'thoigian' được lưu dưới dạng datetime
        'start' => 'datetime',
        'end' => 'datetime',
    ];


}