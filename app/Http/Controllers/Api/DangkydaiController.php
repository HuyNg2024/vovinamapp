<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\News;
use App\Models\dangkydai;
use App\Models\KetQuaThi;
use Illuminate\Http\Request;
use App\Models\EducationGrades;
use App\Models\table_atg_members;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DangkydaiController extends Controller
{
    public function dangKyDai(Request $request)
    {
        // Validate dữ liệu đầu vào (tùy chọn)
        $validator = Validator::make($request->all(), [
            'id_at_members' => 'required|exists:table_atg_members,id',
            'id_dai' => 'required|exists:table_educationgrades,id',
            'chi_phi' => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        // Lấy thông tin thành viên và đai
        $member = table_atg_members::find($request->id_at_members);
        $daiHoc = EducationGrades::find($request->id_dai);
        
        if (!$member || !$daiHoc) {
            return response()->json(['message' => 'Không tìm thấy thành viên hoặc đai học.'], 404);
        }
    
        // Kiểm tra điều kiện đăng ký
        if ($daiHoc->order <= $member->educationGrade->order) {
            return response()->json(['message' => 'Bạn không thể đăng ký đai thấp hơn hoặc bằng đai hiện tại.'], 400);
        }
    
        if ($daiHoc->order > $member->educationGrade->order + 1) {
            return response()->json(['message' => 'Bạn chỉ có thể đăng ký đai cao hơn một bậc so với đai hiện tại.'], 400);
        }
    
        // Tạo bản ghi đăng ký mới
        $dangKyDai = dangkydai::create([
            'id_at_members' => $request->id_at_members,
            'id_dai' => $request->id_dai,
            'chi_phi' => $request->chi_phi,
            'ngay_tao' => Carbon::now(),
            'ngay_thi' => Carbon::now()->addMonth(), // Tăng 1 tháng so với ngày tạo
            'trang_thai' => 'chưa thi',
            'trang_thai_thanh_toan' => 'chưa thanh toán', 
        ]);
    
        return response()->json(['message' => 'Đăng ký đai thành công.', 'data' => $dangKyDai], 201);
    }

    //0 Lấy ds đăng ký thi của các thành viên clb mình 
     public function ResultBeltTeacherView(Request $request)
     {
         $lang = $request->query('lang', 'vi');
         $user = JWTAuth::user();
 
         if (!$user) {
             return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
         }
 
         if ($user->hlv === 0) {
             return response()->json(['error' => 'Chỉ HLV mới có quyền xem thông tin này'], 403);
         }
         
         // Lấy danh sách thành viên trong clb hlv
         $members = table_atg_members::where('id_club', $user->id_club)
                                     ->where('id', '!=', $user->id)
                                     ->where('hlv', '!=', 1)
                                     ->whereIn('id', function($query) {
                                         $query->select('id_member')
                                               ->from('table_ketquathi')
                                               ->distinct();
                                     })
                                     ->with('club')
                                     ->get();
 
         $membersInfo = $members->map(function($member) use($lang) {
             $ketquathi = KetQuaThi::where('id_member',$member->id)->latest()->first();
             $khoathi = $ketquathi ? News::where('id', $ketquathi->id_exam)->where('type','khoa-thi')->first() : null;
 
             return [
                'id_member' => $member->id,
                'ten' => $member->ten,
                'chieucao' => $member->chieucao,
                'cannang' => $member->cannang,
                'id_club' => $member->id_club,
                'ten_club' => $lang === 'en' ? $member->club->tenen : $member->club->ten,
                'khoa_thi' => $khoathi ? ($lang === 'en' ? $khoathi->tenen : $khoathi->tenvi) : null,
                'ngay_thi' => $khoathi ? ($lang === 'en' 
                                            ? Carbon::parse($khoathi->start)->setTimezone('Asia/Ho_Chi_Minh')->format('Y-m-d H:i:s')
                                            : Carbon::parse($khoathi->start)->setTimezone('Asia/Ho_Chi_Minh')->format('d-m-Y H:i:s'))
                                        : ($lang === 'en' ? 'No information' : 'Chưa có thông tin'),
                'tinhtrang' => $ketquathi 
                ? ($ketquathi->tinhtrang == 1 
                    ? ($lang === 'en' ? 'Approved' : 'Đã duyệt') 
                    : ($lang === 'en' ? 'Not approved' : 'Chưa duyệt'))
                : ($lang === 'en' ? 'No information' : 'Chưa có thông tin'),
                'ketqua' => $ketquathi 
                    ? ($ketquathi->tinhtrang == 0
                        ? ($lang === 'en' ? 'No result due to pending application approval' : 'Không có kết quả vì hồ sơ chưa được duyệt')
                        : ($ketquathi->ngaycham
                            ? ($ketquathi->ketqua == 1
                                ? ($lang === 'en' ? 'Pass' : 'Đậu')
                                : ($lang === 'en' ? 'Fail' : 'Rớt'))
                            : ($lang === 'en' ? 'Not yet graded' : 'Chưa được chấm')
                            )
                      )
                    : ($lang === 'en' ? 'No information' : 'Chưa có thông tin'),
                'ngaycham' => $ketquathi && $ketquathi->ngaycham 
                    ? ($lang === 'en' 
                        ? Carbon::parse($ketquathi->ngaycham)->format('Y-m-d H:i:s') 
                        : Carbon::parse($ketquathi->ngaycham)->format('d-m-Y H:i:s'))
                    : ($lang === 'en' ? 'No information' : 'Chưa có thông tin'),
            ];
        });
 
         return response()->json($membersInfo);
     }
    // 1. Cập nhật thông tin người đăng ký thi
    public function updateRegistrationInfo(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Không có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'username' => 'sometimes|string|unique:table_atg_members,username,' . $request->id_member,
            'email' => 'sometimes|string|email|unique:table_atg_members,email,' . $request->id_member,
            'dienthoai' => 'sometimes|string|unique:table_atg_members,dienthoai,' . $request->id_member,
            'ten' => 'sometimes|string',
            'diachi' => 'sometimes|string',
            'gioitinh' => 'sometimes|in:Nam,Nữ',
            'ngaysinh' => 'sometimes|date_format:Y-m-d|before:today',
            'hotengiamho' => 'sometimes|string|required_with:dienthoai_giamho',
            'dienthoai_giamho' => 'sometimes|string|required_with:hotengiamho',
            'chieucao' => 'sometimes|numeric',
            'cannang' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $member = table_atg_members::find($request->id_member);
        if (!$member) {
            return response()->json(['error' => 'Không tìm thấy thông tin thành viên'], 404);
        }

        $updateData = $validator->validated();

        if (isset($updateData['gioitinh'])) {
            $updateData['gioitinh'] = $updateData['gioitinh'] === 'Nam' ? 1 : 0;
        }

        if (isset($updateData['ngaysinh'])) {
            try {
                $ngaysinh = trim($updateData['ngaysinh']);
                $ngaysinhTimestamp = Carbon::createFromFormat('Y-m-d', $ngaysinh)->startOfDay()->timestamp;
                $age = Carbon::now()->diffInYears(Carbon::createFromTimestamp($ngaysinhTimestamp));

                if ($age < 18) {
                    if (empty($updateData['hotengiamho']) || empty($updateData['dienthoai_giamho'])) {
                        return response()->json(['error' => 'Yêu cầu họ tên và số điện thoại phụ huynh cho trẻ dưới 18.'], 400);
                    }
                }

                $updateData['ngaysinh'] = $ngaysinhTimestamp;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Ngày sinh không hợp lệ.'], 400);
            }
        }

        try {
            $member->update($updateData);

            // Cập nhật thông tin trong bảng table_ketquathi nếu cần
            if (isset($updateData['chieucao']) || isset($updateData['cannang'])) {
                $ketQuaThi = KetQuaThi::where('id_member', $request->id_member)->latest()->first();
                if ($ketQuaThi) {
                    $ketQuaThi->update([
                        'chieucao' => $updateData['chieucao'] ?? $ketQuaThi->chieucao,
                        'cannang' => $updateData['cannang'] ?? $ketQuaThi->cannang,
                    ]);
                }
            }

            return response()->json(['success' => 'Cập nhật thông tin thành công', 'data' => $member]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Duyệt hồ sơ
    public function approveRegistration(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Không có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $ketQuaThi = KetQuaThi::where('id_member', $request->id_member)->latest()->first();
            if (!$ketQuaThi) {
                return response()->json(['error' => 'Không tìm thấy thông tin đăng ký'], 404);
            }

            $ketQuaThi->update(['tinhtrang' => 1]);

            return response()->json(['success' => 'Duyệt hồ sơ thành công', 'data' => $ketQuaThi]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Không duyệt và xóa hồ sơ
    public function rejectAndDeleteRegistration(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Không có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $ketQuaThi = KetQuaThi::where('id_member', $request->id_member)->latest()->first();
            if (!$ketQuaThi) {
                return response()->json(['error' => 'Không tìm thấy thông tin đăng ký'], 404);
            }

            $ketQuaThi->delete();

            return response()->json(['success' => 'Đã xóa hồ sơ đăng ký']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 4. Cập nhật kết quả thi
    public function updateExamResult(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Không có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'ketqua' => 'required|in:Đậu,Rớt',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $ketquaValue = $request->ketqua === 'Đậu' ? 1 : 0;

        try {
            $ketQuaThi = KetQuaThi::where('id_member', $request->id_member)->latest()->first();
            if (!$ketQuaThi) {
                return response()->json(['error' => 'Không tìm thấy thông tin đăng ký'], 404);
            }

            if ($ketQuaThi->tinhtrang === 0) {
                return response()->json(['error' => 'Hồ sơ chưa được duyệt'], 400);
            }

            $ketQuaThi->update([
                'ketqua' => $ketquaValue,
                'ngaycham' => now(),
            ]);

            return response()->json(['success' => 'Cập nhật kết quả thi thành công', 'data' => $ketQuaThi]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
