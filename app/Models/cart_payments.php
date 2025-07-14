<?php

// app/Models/Cart.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cart_payments extends Model
{
    use HasFactory;
protected $table='cart_payments';
protected $fillable = [

    'id',
    'id_cart',
    'id_member',
    'trang_thai_thanh_toan',

];

}