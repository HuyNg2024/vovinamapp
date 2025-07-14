<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationGrade extends Model
{
    use HasFactory;

    protected $table = 'table_educationgrades';

    protected $fillable = [
        'code', 'ten', 'order', 'type', 'level', 'column1', 'column2', 'column3', 'date'
    ];
}
