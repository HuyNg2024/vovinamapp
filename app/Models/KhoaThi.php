<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhoaThi extends Model
{
    use HasFactory;

    protected $table = 'table_khoathi';  // Tên bảng trong cơ sở dữ liệu
    public $timestamps = false;   // Nếu bảng không có cột timestamps (created_at, updated_at)

    protected $fillable = [
        'title',       // varchar(255)
        'ngaythi',     // date
        'giobatdau',        // time (or datetime)
        'gioketthuc',       // time (or datetime)
        'diadiem',     // varchar(255)
        'mota',        // text
        'trangthai',   // varchar(50)
        'isactive',    // tinyint(1)
        'hinhanh',     // varchar(255)
        'seotitle',    // varchar(255)
        'seodescription', // text
        'createdby',    // varchar(50)
        'modifiedby',   // varchar(50)
        'noidungthi',   // text
        'duongdan',     // varchar(255)
        'id_club',
        'id_educationgrades',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club');
    }

    public function educationGrades()
    {
        return $this->belongsTo(EducationGrades::class, 'id_educationgrades');
    }


}
