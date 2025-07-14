<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveClubRequest extends Model
{
    use HasFactory;
    protected $table = 'leave_club_requests';
    public $timestamps = true;
    protected $fillable = [
        'id_atg_members',
        'id_club',
        'id_class',
        'status',
    ];

     // Quan hệ với table_atg_members
     public function member()
     {
         return $this->belongsTo(table_atg_members::class, 'id_atg_members');
     }
 
     // Quan hệ với Club
     public function club()
     {
         return $this->belongsTo(Club::class, 'id_club');
     }

     // Quan hệ với Classes (lớp học)
    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class'); 
    }


}
