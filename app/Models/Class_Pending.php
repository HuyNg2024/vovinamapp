<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Class_Pending extends Model
{
    use HasFactory;

    protected $table = 'class_pending';

    protected $fillable = [
        'id_member',
        'id_club',
        'id_class',
    ];
    public $incrementing = false;
    protected $primaryKey = ['id_member', 'id_class'];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class', 'id'); 
    }

   
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'id_member', 'id'); 
    }

}
