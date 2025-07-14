<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $primaryKey = 'ReviewID';
    protected $fillable = ['ProductID', 'RatingValue', 'RatingCount', 'id_atg_members', 'ReviewDate', 'ReviewContent'];
    public $timestamps = false; 
    public function product()
    {
        return $this->belongsTo(Product::class, 'ProductID');
    }
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members', 'id');
    }
}

