<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'table_country';
    protected $fillable = [
        'ten', 'map_lat', 'map_long', 'zoom','map_lat','map_long'
    ];
    public $timestamps = false; //tắt chế độ created_at & updated_at

    public function cities()
    {
        return $this->hasMany(City::class, 'id_country');
    }

}
