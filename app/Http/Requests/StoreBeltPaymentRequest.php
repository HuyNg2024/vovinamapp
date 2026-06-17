<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBeltPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => 'nullable|string',
            'id_dai' => 'required|integer', // might not have a table to check exists against directly if it's dynamic
            'member_id' => 'required|integer|exists:table_atg_members,id',
            'amount' => 'required|numeric|min:1000',
            'language' => 'required|string|in:vn,en',
            'bankCode' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_dai.required' => 'Mã cấp đai là bắt buộc.',
            'member_id.required' => 'Mã thành viên là bắt buộc.',
            'member_id.exists' => 'Thành viên không tồn tại.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền tối thiểu là 1000 VNĐ.',
            'language.required' => 'Ngôn ngữ là bắt buộc.',
        ];
    }
}
