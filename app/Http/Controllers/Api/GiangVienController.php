<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\GiangVien;  // Import GiangVien model
use Illuminate\Support\Facades\Validator; // Import Validator

class GiangVienController extends Controller
{
    public function index()
    {
        $giangViens = GiangVien::all(); // Lấy tất cả giảng viên
        return response()->json($giangViens); // Trả về JSON
    }

   public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào dựa trên các trường trong ảnh
        $validator = Validator::make($request->all(), [
            'id_social' => 'required|unique:giangviens', 
            'username' => 'required|unique:giangviens',
            'password' => 'required',
            'maxacnhan' => 'required|same:password', // Xác nhận mật khẩu
            'avatar' => 'nullable|image', // Cho phép avatar là null hoặc ảnh
            'ten' => 'required',
            'dienthoai' => 'nullable', // Cho phép số điện thoại là null
            'email' => 'nullable|email|unique:giangviens', // Cho phép email là null
            'diachi' => 'nullable',
            'gioitinh' => 'nullable|in:nam,nu,khac', // Giới hạn giá trị giới tính
            'login_session' => null, // Giá trị mặc định cho login_session
            'lastlogin' => null,     // Giá trị mặc định cho lastlogin
            'hienthi' => 1,         // Giá trị mặc định cho hienthi (giả sử 1 là hiển thị)
            'ngaysinh' => 'nullable|date',
            'stt' => GiangVien::max('stt') + 1, // Tự động tăng stt
            // Các trường khác... (stt, loai, id_club) có thể cần xác thực tùy yêu cầu
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Xử lý avatar (nếu có)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public'); // Lưu avatar vào storage/app/public/avatars
            $request->merge(['avatar' => $avatarPath]); // Cập nhật đường dẫn avatar trong request
        }

        // Tạo giảng viên mới
        $giangVien = GiangVien::create($request->all());

        return response()->json($giangVien, 201); 
    }

    public function show(string $id)
    {
        $giangVien = GiangVien::find($id); 
        if (!$giangVien) { // Nếu không tìm thấy giảng viên
            return response()->json(['message' => 'Giảng viên không tồn tại'], 404);
        }
        return response()->json($giangVien); // Trả về giảng viên
    }

    public function update(Request $request, string $id)
{
    $giangVien = GiangVien::find($id);
    if (!$giangVien) {
        return response()->json(['message' => 'Giảng viên không tồn tại'], 404);
    }

    // Xác thực dữ liệu đầu vào (giống store nhưng xử lý unique)
    $validator = Validator::make($request->all(), [
        'id_social' => 'required|unique:giangviens,id_social,' . $id, // Unique ngoại trừ id hiện tại
        'username' => 'required|unique:giangviens,username,' . $id, // Unique ngoại trừ id hiện tại
        'password' => 'sometimes', // Cho phép password là null khi cập nhật
        'maxacnhan' => 'sometimes|same:password', // Chỉ xác nhận nếu có nhập password
        'avatar' => 'nullable|image',
        'ten' => 'required',
        'dienthoai' => 'nullable',
        'email' => 'nullable|email|unique:giangviens,email,' . $id, // Unique ngoại trừ id hiện tại
        'diachi' => 'nullable',
        'gioitinh' => 'nullable|in:nam,nu,khac', // Giới hạn giá trị giới tính
        'login_session' => null, // Giá trị mặc định cho login_session
        'lastlogin' => null,     // Giá trị mặc định cho lastlogin
        'hienthi' => 1,         // Giá trị mặc định cho hienthi (giả sử 1 là hiển thị)
        'ngaysinh' => 'nullable|date',
        'stt' => GiangVien::max('stt') + 1, // Tự động tăng stt
            // Các trường khác... (stt, loai, id_club) có thể cần xác thực tùy yêu cầu
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Xử lý avatar (nếu có)
    if ($request->hasFile('avatar')) {
        // Xóa avatar cũ nếu có
        if ($giangVien->avatar) {
            Storage::disk('public')->delete($giangVien->avatar);
        }
        $avatarPath = $request->file('avatar')->store('avatars', 'public');
        $request->merge(['avatar' => $avatarPath]);
    }

    // Cập nhật giảng viên
    $giangVien->update($request->all());

    return response()->json($giangVien);
}


    public function update2(Request $request, string $id)
    {
        $giangVien = GiangVien::find($id);
        if (!$giangVien) { // Nếu không tìm thấy giảng viên
            return response()->json(['message' => 'Giảng viên không tồn tại'], 404);
        }

        // Xác thực dữ liệu đầu vào (tương tự như store nhưng cần xử lý unique)
        // ...

        $giangVien->update($request->all());
        return response()->json($giangVien); // Trả về giảng viên đã được cập nhật
    }

    public function destroy(string $id)
    {
        $giangVien = GiangVien::find($id);
        if (!$giangVien) { // Nếu không tìm thấy giảng viên
            return response()->json(['message' => 'Giảng viên không tồn tại'], 404);
        }
        $giangVien->delete(); 
        return response()->json(['message' => 'Giảng viên đã được xóa'], 200);
    }
}
