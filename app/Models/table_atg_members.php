<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; //thư viện mở rộng của lớp DateTime()
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class table_atg_members extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'table_atg_members';

    protected $fillable = [
        'ten', 'dienthoai', 'diachi', 'ngaysinh', 'gioitinh', 
        'hotengiamho', 'dienthoai_giamho', 'username', 'password', 
        'email', 'lastlogin', 'id_club', 'chieucao', 'cannang','id_capdai', 'id','tenen',
        'deleted','active_token','thietbi'

    ]; //chỉ ~ giá trị đc thêm vào table

    protected $hidden = [
        'password',
        'active_token',
    ];

    public $timestamps = false; //tắt chế độ created_at & updated_at

    protected static function boot() //immediately execute before inserting any records into table
    {
        parent::boot();
        static::creating(function ($member) {
            if (!empty($member->ngaysinh)) {
                // Chuyển đổi ngày tháng thành timestamp
                try {
                    $ngaysinh = trim($member->ngaysinh);
                    $ngaysinhTimestamp = Carbon::createFromFormat('Y-m-d', $ngaysinh)->startOfDay()->timestamp;
                    $member->ngaysinh = $ngaysinhTimestamp;
                } catch (\Exception $e) {
                    dd($e->getMessage());
                }
                //Tính toán tuổi từ timestamp
                $age = Carbon::now()->diffInYears(Carbon::createFromTimestamp($ngaysinhTimestamp));

                if ($age < 18) {
                    if (empty($member->hotengiamho) || empty($member->dienthoai_giamho)) {
                        throw new \Exception("Yêu cầu họ tên và số điện thoại phụ huynh cho trẻ dưới 18.");
                    }
                }
            }
        });
    }

    // Định nghĩa phương thức truy vấn ngược từ timestamp sang định dạng ngày tháng
    public function getFormattedNgaysinhAttribute()
    {
        return $this->ngaysinh ? Carbon::createFromTimestamp($this->ngaysinh)->format('Y-m-d') : null;
    }

    public function getFormattedGioitinhAttribute()
    {
        return $this->gioitinh == 1 ? 'Nam' : 'Nữ';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    //add extra
    //protected $primaryKey = 'id_atg_members';

    public function club()
    {
        return $this->belongsTo(Club::class, 'id_club', 'id');
    }

    public function managedClub()
    {
        return $this->hasOne(Club::class, 'id_atg_members');
    }

    public function managedClass()
    {
        return $this->hasMany(Classes::class, 'id_atg_members');
    }

    public function checkins()
    {
        return $this->hasMany(Checkin::class, 'ma_nv', 'id');
    }

    public function isCoach()
    {
        return $this->hlv == 1;
    }
    //-------------------Hạu
    public function reviews()
    {
        return $this->hasMany(Review::class, 'id_atg_members','id');
    }

    //Huy
    public function registeredClasses()
    {
        return $this->hasMany(RegisterClass::class, 'id_atg_members');
    }

    public function educationGrade() 
    {
        return $this->belongsTo(EducationGrades::class, 'id_capdai'); 
    }
 
    public function registrations()
    {
        return $this->hasMany(dangkydai::class, 'id_atg_members');
    }


}
