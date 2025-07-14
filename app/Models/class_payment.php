<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class class_payment extends Model
{
    use HasFactory;
    // Tên bảng tương ứng trong database
    protected $table = 'class_payment';

    // Các cột có thể được gán giá trị một cách hàng loạt
    protected $fillable = [

        'id',
        'member_id',
        'hocphi',
        'id_class',
        'created_at',
        'status',
        'name_member',
        'end_date',
    ];

    // Chỉ định các cột kiểu JSON

    // Bỏ qua các cột timestamps do đã có cột created_at mặc định
    public $timestamps = false;

    // Nếu cần sử dụng các cột timestamps với tên khác hoặc thêm updated_at:
    // protected $dates = ['created_at'];

    // Trong model class_payment

    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class', 'id');
    }


   
}
