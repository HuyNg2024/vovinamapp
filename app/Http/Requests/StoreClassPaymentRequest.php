<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|integer|exists:table_class,id',
            'member_id' => 'required|integer|exists:table_atg_members,id',
            'name_member' => 'required|string|max:255',
            'id_classpayment' => 'nullable|integer',
            'amount' => 'required|numeric|min:1000',
            'language' => 'required|string|in:vn,en',
            'bankCode' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'class_id.required' => 'Mã lớp học là bắt buộc.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'member_id.required' => 'Mã thành viên là bắt buộc.',
            'member_id.exists' => 'Thành viên không tồn tại.',
            'name_member.required' => 'Tên thành viên là bắt buộc.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền tối thiểu là 1000 VNĐ.',
            'language.required' => 'Ngôn ngữ là bắt buộc.',
        ];
    }
}
