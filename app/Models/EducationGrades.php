<?php // file EducationGrades.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationGrades extends Model
{
    use HasFactory;
    protected $table = 'table_educationgrades'; 
    public $timestamps = false;
   
    protected $fillable = [
        'code',
        'ten',
        'order',
        'type',
        'level',
        'column1',
        'column2',
        'column3',
        'date',
        'mau_dai',
        'danh_xung',
        'hinhanh',
        'mo_ta_chi_tiet',
        'tenen',
        'mo_ta_chi_tiet_en',
        'mau_dai_en',
        "danh_xung_en",
        'thoigianhoc'
    ];

    public function registrations()
    {
        return $this->hasMany(dangkydai::class, 'id_dai');
    }
}
