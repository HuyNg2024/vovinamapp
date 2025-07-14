<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DangKyThi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_atg_members', 
        'id_khoathi',
        'id_educationgrades', // Đai dự kiến thi
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ngay_dang_ky' => 'datetime', // Chuyển đổi kiểu dữ liệu ngày tháng
        'trang_thai' => 'string' // Giữ nguyên kiểu enum
    ];

    public function atgMember()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members');
    }

    // Mối quan hệ với model KhoaThi
    public function khoaThi()
    {
        return $this->belongsTo(KhoaThi::class, 'id_khoathi');
    }

    public function educationGrades()
    {
        return $this->belongsTo(EducationGrades::class, 'id_educationgrades');
    }

}

