<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubRegisterTracker extends Model
{
    use HasFactory;
    protected $table = 'table_club_register_tracker';
    public $timestamps = false;
    protected $fillable = [
        'owner',
        'note',
        'name',
        'desc',
        'date',
        'date_approved',
        'map_lat',
        'map_long',
        'id_club',
        'type',
        'id_country',
        'id_city',
        'id_district',
        'id_ward',
        'phone',
        'timeopen',
        'address',
        'contact',
        'email',
        'id_user',
        'date_update',
        'update_note',
        'photo',
        'status'
    ];

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club'); 
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'id_country'); 
 
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'id_city'); 
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'id_district'); 
    }

    public function user()
    {
        return $this->belongsTo(table_atg_members::class, 'id_user'); 
    }
}
