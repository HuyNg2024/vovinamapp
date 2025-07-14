<?php //file dangkydai.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class dangkydai extends Model
{
    use HasFactory;
    protected $table = 'dangkydai';
    public $timestamps = false; 

    protected $fillable = [
        'id',
        'id_atg_members',  // Adjust to match the actual column name in the table
        'id_dai',
        'chi_phi',
        'ngay_tao',
        'ngay_thi',
        'trang_thai',
        'trang_thai_thanh_toan',
    ];

    protected $casts = [
        'ngay_tao' => 'datetime',
        'ngay_thi' => 'date',
    ];

    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members');  // Adjusted foreign key
    }

    public function educationGrade()
    {
        return $this->belongsTo(EducationGrades::class, 'id_dai');
    }
}
