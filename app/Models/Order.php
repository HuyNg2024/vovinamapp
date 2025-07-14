<?php

// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Order extends Model
{
    use HasFactory;

    protected $table = 'order'; // Đặt tên bảng tương ứng
    protected $fillable = [
        'giao_hang',
        'ten',
        'id_cart',
        'member_id',
        'txn_ref',
        'amount',
        'order_info',
        'response_code',
        'transaction_no',
        'bank_code',
        'pay_date',
        'status',
        // 'total'  // Giữ lại từ version cũ nếu vẫn cần
        'ten',
        'giao_hang',
        'qr_link',
    ];    public $timestamps = false;


    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function detailCarts()
    {
        return $this->hasMany(detail_order::class, 'id_order', 'id'); 
    }

       
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'member_id', 'id'); 
    }


}
