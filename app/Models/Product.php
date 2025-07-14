<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'products';
    protected $primaryKey = 'ProductID';
     // Nếu khóa chính không phải là số nguyên, bạn cũng cần phải chỉ định
     protected $keyType = 'integer'; // Thay đổi thành 'integer' nếu khóa chính là số nguyên

     // Nếu bạn không muốn Eloquent tự động tự tăng khóa chính
     public $incrementing = false;
 
    protected $fillable = ['ProductName', 'SupplierID', 'UnitPrice', 'UnitsInStock','CategoryID','link_image','SupplierName','created_at', 'updated_at'
    , 'sale','noibat','CategoryNameEng','SupplierNameEng'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'SupplierID');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'ProductID');
    }
    // Định nghĩa mối quan hệ nhiều-một với Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'CategoryName', 'CategoryName');
    }
    public function getNameByLang($lang)
    {
        return $lang === 'en' ? $this->tenenglish : $this->ProductName;
    }

    public function getCategoryByLang($lang)
    {
        return $lang === 'en' ? $this->CategoryNameEng : $this->CategoryNameVi;
    }

    public function getSupplierByLang($lang)
    {
        return $lang === 'en' ? $this->SupplierNameEng : $this->SupplierNameVi;
    }
}
