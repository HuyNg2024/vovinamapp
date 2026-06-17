<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Tymon\JWTAuth\Facades\JWTAuth;

class StoreUpdateInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = JWTAuth::user();
        $userId = $user ? $user->id : 'NULL';
        
        return [
            'username' => 'sometimes|required|string|unique:table_atg_members,username,'. $userId,
            'email' => 'sometimes|required|string|email|unique:table_atg_members,email,' . $userId,
            'ten' => 'sometimes|required|string',
            'dienthoai' => 'sometimes|required|string|unique:table_atg_members,dienthoai,' . $userId,
            'diachi' => 'sometimes|required|string',
            'gioitinh' => 'sometimes|required|in:Nam,Nữ',
            'ngaysinh' => 'sometimes|required|date_format:Y-m-d|before:today',
            'hotengiamho' => 'sometimes|nullable|string|required_with:dienthoai_giamho',
            'dienthoai_giamho' => 'sometimes|nullable|string|required_with:hotengiamho',
            'chieucao' => 'sometimes|required|numeric',
            'cannang' => 'sometimes|required|numeric',
        ];
    }
}
