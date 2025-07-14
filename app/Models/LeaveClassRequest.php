<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveClassRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_atg_members',
        'id_class',
        'status',
        'note',
    ];

     // Relationship với table_atg_members
     public function member()
     {
         return $this->belongsTo(table_atg_members::class, 'id_atg_members', 'id'); 
     }
 
     
     public function class()
     {
         return $this->belongsTo(Classes::class, 'id_class', 'id'); 
     }
}
