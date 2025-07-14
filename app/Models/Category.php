<?php





namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'CategoryID'; // Đặt khóa chính là CategoryID
    protected $keyType = 'string'; // Đặt kiểu khóa chính là chuỗi
    public $incrementing = false; // Không tự động tăng

    // Định nghĩa mối quan hệ một-nhiều với Product
    public function products()
    {
        return $this->hasMany(Product::class, 'CategoryName', 'CategoryName');
    }
}

