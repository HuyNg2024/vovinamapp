<?php

// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
protected $table='carts';
    protected $fillable = [
        'id',
        'member_id',
        'product_id',
        'quantity',
        'total_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id','ProductID');
    }
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'member_id', 'id');
    }
}

