<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    use HasFactory;
    protected $table = 'table_lang'; // Match the table name in your database
   
   
       protected $fillable = [
           'giatri',
           'langvi',
           'langen',
           'langzh',
           'langko',
           'stt', 
       ]; 
}
