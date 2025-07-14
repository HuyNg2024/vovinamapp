<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class GiangVien extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'giang_vien';
    protected $fillable = ['username', 'password', 'email', 'ten', 'dienthoai', 'diachi', 'gioitinh', 'ngaysinh'];
    public $timestamps = false;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // Accessors to format the date and gender fields
    public function getFormattedNgaysinhAttribute()
    {
        return $this->ngaysinh ? \Carbon\Carbon::createFromTimestamp($this->ngaysinh)->format('Y-m-d') : null;
    }

    public function getFormattedGioitinhAttribute()
    {
        return $this->gioitinh == 1 ? 'Nam' : 'Nữ';
    }
}
