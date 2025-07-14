<?php

// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class detail_order extends Model
{
    use HasFactory;
    protected $table='detail_order';
    public $timestamps = false;
    protected $fillable = [

        'id',
        'id_order',
        'id_product',
        'quantity',

    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product', 'ProductID');
    }
}