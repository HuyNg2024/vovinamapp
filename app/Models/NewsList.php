<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsList extends Model
{
    use HasFactory;

    protected $table = 'table_news_list';

    protected $fillable = [
        'noibat', 'tenkhongdauvi', 'tenkhongdauen', 'noidungen', 'noidungvi', 'motaen', 'motavi', 'tenen', 
        'tenvi', 'photo', 'options', 'stt', 'hienthi', 'type', 'ngaytao', 'ngaysua'
    ];
}
