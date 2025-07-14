<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Tên bảng
    protected $table = 'order_details';

    // Các cột có thể điền giá trị
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    // Định nghĩa quan hệ với model Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Định nghĩa quan hệ với model Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

