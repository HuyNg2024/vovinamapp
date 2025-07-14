<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $table = 'table_banners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url_logo',
        'url_background',
        'titlevi',
        'titleen',
        'descvi',
        'descen',
        'startdate',
        'enddate',
        'note',
    ];

    protected $casts = [
        'startdate' => 'datetime',
        'enddate' => 'datetime',
        'created_date' => 'datetime',
    ];

    public $timestamps = false;

    const CREATED_AT = 'created_date';
}
