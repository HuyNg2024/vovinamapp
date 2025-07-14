<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function Laravel\Prompts\table;

class Checkin extends Model
{
    use HasFactory;

    protected $table = 'table_checkin';
    public $timestamps = false;

    protected $fillable = [
        'ma_nv',
        'date',
        'created_date',
        'created_userid',
        'updated_date', 
        'updated_userid',
        'in',
        'out',
        'loaiphep',
        'id_class'
    ];

    //protected $primaryKey = 'id_checkin';
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'ma_nv', 'id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class', 'id');
    }

}
