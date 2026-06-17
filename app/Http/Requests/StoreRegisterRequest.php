<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|unique:table_atg_members,username',
            'email' => 'required|string|email|unique:table_atg_members,email',
            'password' => 'required|string|min:6',
            'ten' => 'required|string',
            'dienthoai' => 'required|string|unique:table_atg_members,dienthoai',
            'diachi' => 'required|string',
            'gioitinh' => 'required|in:Nam,Nữ',
            'ngaysinh' => 'required|date_format:Y-m-d|before:today',
            'hotengiamho' => 'nullable|string|required_with:dienthoai_giamho',
            'dienthoai_giamho' => 'nullable|string|required_with:hotengiamho',
            'chieucao' => 'required|numeric',
            'cannang' => 'required|numeric'
        ];
    }
}
