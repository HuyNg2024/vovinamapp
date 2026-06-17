<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Middleware auth:api already handles authentication
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'member_id' => 'required|integer|exists:table_atg_members,id',
            'amount' => 'required|numeric|min:1000',
            'language' => 'required|string|in:vn,en',
            'bankCode' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'member_id.required' => 'Mã thành viên là bắt buộc.',
            'member_id.exists' => 'Mã thành viên không tồn tại.',
            'amount.required' => 'Số tiền là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là số.',
            'amount.min' => 'Số tiền thanh toán tối thiểu là 1000 VNĐ.',
            'language.required' => 'Ngôn ngữ là bắt buộc.',
        ];
    }
}
