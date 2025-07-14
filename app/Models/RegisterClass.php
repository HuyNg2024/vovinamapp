<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterClass extends Model
{
    use HasFactory;
    protected $table = 'register_class';
   
    protected $fillable = ['id_atg_members', 'id_class', 'begin_date', 'end_date']; //chỉ ~ giá trị đc thêm vào table
    public $timestamps = false; //tắt chế độ created_at & updated_at

    // Quan hệ với table_atg_members
    public function member()
    {
        return $this->belongsTo(table_atg_members::class, 'id_atg_members');
    }

    // Quan hệ với table_class
    public function class()
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }
    


}
