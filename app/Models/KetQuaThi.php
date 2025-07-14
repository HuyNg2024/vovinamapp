<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetQuaThi extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong database
     *
     * @var string
     */
    protected $table = 'table_ketquathi'; // Thay bằng tên bảng thật của bạn

    /**
     * Các trường có thể gán hàng loạt (Mass Assignment)
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'id_exam',
        'id_member',
        'id_capdaiduthi',
        'cannang',
        'chieucao',
        'ketqua',
        'tinhtrang',
        'id_giamkhao',
        'ngaycham',
    ];

    /**
     * Các trường cần chuyển đổi kiểu dữ liệu
     *
     * @var array
     */
    protected $casts = [
        'ketqua' => 'boolean',
        'tinhtrang' => 'boolean',
    ];



    /**
     * Loại bỏ timestamps (created_at, updated_at)
     *
     * @var bool
     */
    public $timestamps = false;

    // Relationships (Quan hệ với các model khác)
    // public function exam()
    // {
    //     return $this->belongsTo(KhoaThi::class, 'id_exam', 'id_khoathi'); 
    // }


    public function member()
    {
        return $this->belongsTo(table_atg_members::class,'id_member', 'id_atg_members');
    }

    public function capDaiDuThi()
    {
        return $this->belongsTo(EducationGrades::class, 'id_capdaiduthi','id_educationgrades');
    }

    public function giamKhao()
    {
        return $this->belongsTo(GiamKhao::class, 'id_giamkhao');
    }

    public function news()
    {
        return $this->belongsTo(News::class, 'id_exam', 'id_new'); 
    }
}
