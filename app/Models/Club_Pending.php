<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club_Pending extends Model
{
    use HasFactory;

    protected $table = 'club_pending';

    protected $fillable = [
        'id_member',
        'id_club',
        'created_at', 
        'updated_at',
    ];
    public $incrementing = false;
    protected $primaryKey = ['id_member', 'id_club'];

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club'); 
    }

   
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'id_member'); 
    }

}
