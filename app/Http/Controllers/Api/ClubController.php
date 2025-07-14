<?php 

namespace App\Http\Controllers\Api;


use App\Models\Club;
use Illuminate\Http\Request;
use App\Models\RegisterClass;
use App\Models\table_atg_members;
use App\Models\Club_Pending;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ClubController extends Controller
{
    public function getAll(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        // lấy all bản ghi từ table_club kèm theo thông tin của coach (HLV)
        $clubs = Club::with('coach')->get();

        $clubsInfo = $clubs->map(function($club) use($lang) {
            return [
                'id' => $club->id,
                'id_city' => $club->id_city,
                'ten' => $lang === 'en' ?  $club->tenen : $club->ten,
                'stt' => $club->stt,
                'ngaytao' =>$club->ngaytao,
                'ngaysua' =>$club->ngaysua,
                'gia' => $club->gia,
                'mota' => $club->tenkhongdau,
                'diachi' => $lang === 'en' ? $club->diachien : $club->diachi,
                'dienthoai' => $club->dienthoai ? $club->dienthoai : ($club->coach->dienthoai ?? 'Không có thông tin'),
                'id_chunhiemClub' => $club->id_atg_members,
                'nguoiquanly' => $club->coach->ten ?? 'Không có thông tin',
                'id_district' => $club->id_district,
                'sn' => $club->sn,
                'luotxem' => $club->luotxem,
                'bank_qrcode' =>$club->bank_qrcode,
                'softTitle' => $club->softTitle,
                'thoigianhoc' => $club->thoigianhoc,
                'image' => $club->image,
            ];
        });
    
        return response()->json($clubsInfo);
    }

    public function getDetailclub(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $validator = validator($request->all(),[
            'id_club' => 'required|integer|exists:table_club,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        // Lấy bản ghi cụ thể dựa trên ID
        $club = Club::with('coach')->find($request->id_club);

        if (is_null($club)) {

            return response()->json(['message' => 'Không tìm thấy câu lạc bộ'], 404);

        }

        $clubsInfo = [
                'id' => $club->id,
                'ten' => $lang === 'en' ?  $club->tenen : $club->ten,
                'mota' => $club->tenkhongdau,
                'diachi' => $lang === 'en' ? $club->diachien : $club->diachi,
                'dienthoai' => $club->dienthoai ? $club->dienthoai : ($club->coach->dienthoai ?? 'Không có thông tin'),
                'nguoiquanly' => $club->coach->ten ?? 'Không có thông tin',
                'img' => $club->image ? $club->image : null,
            ];
        return response()->json($clubsInfo);
    }

    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào từ request
        $validator = Validator::make($request->all(), [
            'id_city' => 'required|integer',
            'ten' => 'required|string|max:255',
            'diachi' => 'required|string|max:255',
            'thoigianhoc' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Tìm kiếm và lấy tọa độ bằng OpenCage API (có sử dụng cache)
        $cacheKey = 'geocoding_' . md5($request->input('diachi'));

        $coordinates = Cache::remember($cacheKey, now()->addDay(), function () use ($request) {
            $apiKey = env('OPENCAGE_API_KEY');
            $response = Http::get('https://api.opencagedata.com/geocode/v1/json', [
                'q' => $request->input('diachi'),
                'key' => $apiKey,
                'limit' => 1,
            ]);

            if ($response->failed()) {
                Log::error('Geocoding failed', ['error' => $response->body()]);
                return null;
            }

            $data = $response->json();
            if (empty($data['results'])) {
                return null;
            }

            return [
                'lat' => (float) $data['results'][0]['geometry']['lat'],
                'lon' => (float) $data['results'][0]['geometry']['lng'],
            ];
        });

        if (!$coordinates) {
            return response()->json(['error' => 'Không tìm thấy địa chỉ hoặc lỗi khi lấy tọa độ'], 400);
        }

        // Tạo mới bản ghi club (sử dụng mass assignment)
        $club = Club::create([
            'id_city' => $request->input('id_city'),
            'ten' => $request->input('ten'),
            'diachi' => $request->input('diachi'),
            'thoigianhoc' => $request->input('thoigianhoc'),
            'map_lat' => $coordinates['lat'],
            'map_long' => $coordinates['lon']
        ]);

        return response()->json($club, 201); 
    }

    public function store3(Request $request)
    {
        $apiKey = env('OPENCAGE_API_KEY');
        $response = Http::get('https://api.opencagedata.com/geocode/v1/json', [
            'q' => $request->input('diachi'),
            'key' => $apiKey,
            'limit' => 1,
        ]);

        $data = $response->json();
        if (!empty($data['results'])) {
            $lat = $data['results'][0]['geometry']['lat'];
            $lon = $data['results'][0]['geometry']['lng'];

            $club = new Club();
            $club->id_city = $request->input('id_city');
            $club->ten = $request->input('ten');
            $club->diachi = $request->input('diachi');
            $club->thoigianhoc = $request->input('thoigianhoc');
            $club->map_lat = $lat;
            $club->map_long = $lon;
            $club->save();

            return response()->json($club, 201);
        } else {
            return response()->json(['error' => 'Không tìm thấy địa chỉ'], 400);
        }
    }

    public function store2(Request $request)
    {
        $request->validate([
            'id_city' => 'required|integer',
            'ten' => 'required|string|max:255',
            'diachi' => 'required|string|max:255',
            'thoigianhoc' => 'required|string|max:255',
        ]);

        // Tạo mới bản ghi club
        $club = Club::create($request->all());

        return response()->json($club, 201);
    }


    public function update(Request $request, string $id)
    {
        $request->validate([
            'id_city' => 'sometimes|required|integer',
            'ten' => 'sometimes|required|string|max:255',
            'diachi' => 'sometimes|required|string|max:255',
            'thoigianhoc' => 'sometimes|required|string|max:255',
        ]);

        // Lấy bản ghi cụ thể dựa trên ID
        $club = Club::find($id);

        if (is_null($club)) {
            return response()->json(['message' => 'Không tìm thấy câu lạc bộ'], 404);
        }

        // Cập nhật bản ghi club
        $club->update($request->all()); 

        return response()->json($club);
    }

    public function destroy(string $id)
    {
        // Lấy bản ghi cụ thể dựa trên ID
        $club = Club::find($id);

        if (is_null($club)) {
            return response()->json(['message' => 'Không tìm thấy câu lạc bộ'], 404);
        }

        // Xóa bản ghi club
        $club->delete();

        return response()->json(['message' => 'Câu lạc bộ đã được xóa thành công']);
    }

    public function updateCoordinates($club_id, $lat, $long)
    {
        $club = Club::find($club_id);
        if ($club) {
            $club->map_lat = $lat;
            $club->map_long = $long;
            $club->save();
            return response()->json(['message' => 'Tọa độ đã được cập nhật thành công']);
        } else {
            return response()->json(['message' => 'Không tìm thấy câu lạc bộ'], 404);
        }
    }

    public function joinClub(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_club' => 'required|integer|exists:table_club,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $user = JWTAuth::user();
        $member_id = $user->id;
        $member = table_atg_members::find($user->id);
        
        if (!$member) {
            return response()->json(['error' => 'Không tìm thấy thông tin thành viên'], 404);
        }

        // Kiểm tra nếu người dùng đã ở trong một câu lạc bộ (id_club khác 0)
        if ($member->id_club != 0) {
            return response()->json(['error' => 'Bạn đã là thành viên của một CLB. Không thể tham gia CLB khác'], 400);
        }

        try {
            $member->id_club = $request->id_club;
            $member->save();
            // //thêm id_club vào token
            // $memberId_club = $request->id_club;
            // $customClaims = ['id_club' => $memberId_club];
            // $token = JWTAuth::claims($customClaims)->attempt($credentials);
            return response()->json([
                'success' => 'Cập nhật CLB thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function joinClubPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_club' => 'required|integer|exists:table_club,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();
        $member_id = $user->id;

        // Lấy thông tin thành viên
        $member = table_atg_members::find($member_id);

        // Kiểm tra xem thành viên đã có câu lạc bộ hay chưa
        if ($member && $member->id_club != 0) {
            return response()->json(['error' => 'Bạn đã là thành viên của một câu lạc bộ. Không thể gửi yêu cầu tham gia câu lạc bộ khác.'], 400);
        }

        
        $existingRequest = Club_Pending::where('id_member', $member_id)
            ->where('id_club', $request->id_club)
            ->exists();

        if ($existingRequest) {
            return response()->json(['error' => 'Yêu cầu tham gia câu lạc bộ này đã tồn tại'], 400);
        }

        try {
            
            Club_Pending::create([
                'id_member' => $member_id,
                'id_club' => $request->id_club
            ]);

            return response()->json([
                'success' => 'Yêu cầu tham gia câu lạc bộ đã được gửi',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getPendingClubs()
    {
        $user = JWTAuth::user();
        $member_id = $user->id;

        $pendingClubs = Club_Pending::with(['club', 'club.coach']) // Eager load cả club và coach
            ->where('id_member', $member_id)
            ->get();

        $clubsInfo = $pendingClubs->map(function ($pendingClub) {
            return [
                'id_club' => $pendingClub->id_club, 
                'id' => $pendingClub->club->id,
                'ten' => $pendingClub->club->ten,
                'mota' => $pendingClub->club->tenkhongdau,
                'diachi' => $pendingClub->club->diachi,
                'dienthoai' => $pendingClub->club->dienthoai ?: ($pendingClub->club->coach->dienthoai ?? 'Không có thông tin'),
                'nguoiquanly' => $pendingClub->club->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($clubsInfo);
    }

    public function getPendingClubs2()
    {
        $user = JWTAuth::user();
        $member_id = $user->id;

        
        $pendingClubs = Club_Pending::with('club') 
                                    ->where('id_member', $member_id)
                                    ->get();

        
        $clubsInfo = $pendingClubs->map(function ($pendingClub) {
            return [
                'id_club' => $pendingClub->id_club, 
                'id' => $pendingClub->club->id,
                'ten' => $pendingClub->club->ten,
                'mota' => $pendingClub->club->tenkhongdau,
                'diachi' => $pendingClub->club->diachi,
                'dienthoai' => $pendingClub->club->dienthoai ?: ($pendingClub->club->coach->dienthoai ?? 'Không có thông tin'),
                'nguoiquanly' => $pendingClub->club->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($clubsInfo);
    }

    public function leaveClubPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_club' => 'required|integer|exists:table_club,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();
        $member_id = $user->id;

        try {
           
            $deleted = Club_Pending::where('id_member', $member_id)
                                ->where('id_club', $request->id_club)
                                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => 'Yêu cầu tham gia câu lạc bộ đã bị hủy',
                ], 200);
            } else {
                return response()->json(['error' => 'Không tìm thấy yêu cầu tham gia câu lạc bộ này'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getCoachClubs()
    {
        $user = JWTAuth::user();

        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        // Kiểm tra nếu user không phải là HLV
        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền xem thông tin này'], 403);
        }

        // Lấy danh sách câu lạc bộ mà HLV này quản lý
        $clubs = Club::where('id_atg_members', $user->id)->get();

        // Nếu không tìm thấy câu lạc bộ nào
        if ($clubs->isEmpty()) {
            return response()->json(['message' => 'Bạn chưa quản lý câu lạc bộ nào'], 200);
        }

        // Trả về thông tin của các câu lạc bộ
        return response()->json([
            'message' => 'Thông tin các câu lạc bộ bạn quản lý:',
            'clubs' => $clubs->map(function ($club) {
                return [
                    'id' => $club->id,
                    'ten' => $club->ten,
                    'mota' => $club->tenkhongdau,
                    'diachi' => $club->diachi,
                    'dienthoai' => $club->dienthoai,
                ];
            }),
        ], 200);
    }

    public function getPendingClubsforCoach() 
    {
        $user = JWTAuth::user();

        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        // Kiểm tra nếu user không phải là HLV
        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền xem thông tin này'], 403);
        }

        // Lấy id_club của HLV (giả sử mỗi HLV chỉ có 1 id_club duy nhất)
        $clubId = $user->id_club; 

        if (!$clubId) {
            return response()->json(['error' => 'Bạn chưa được giao quản lý câu lạc bộ nào'], 403);
        }

        // Lấy danh sách các yêu cầu tham gia câu lạc bộ của HLV này
        $pendingClubs = Club_Pending::with('member')
            ->where('id_club', $clubId)
            ->get();

        
        $membersInfo = $pendingClubs->map(function ($pendingClub) {
            return [
                'id_club' => $pendingClub->id_club, 
                'ten_club' => $pendingClub->club->ten,
                'id_member' => $pendingClub->id_member,
                'ten' => $pendingClub->member->ten, 
                'created_at' => $pendingClub->created_at,
            ];
        });

        return response()->json($membersInfo);
    }


    //Rời clb (id_club trog atg_member = null, xóa bản ghi trong bảng register_class nếu có lớp)
    public function outClub()
    {
        $user = JWTAuth::user();
        $member = table_atg_members::find($user->id);

        if (!$member) {
            return response()->json(['error' => 'Không tìm thấy thông tin thành viên'], 404);
        }

        try {
            $currentClubId = $member->id_club;

            if ($currentClubId == 0) {
                return response()->json(['error' => 'Thành viên hiện không thuộc CLB nào'], 200);
            }

            DB::beginTransaction();
            // Rời khỏi CLB
            $member->id_club = 0;
            $member->save();
            // Cập nhật bảng register_class
            // $affected = RegisterClass::where('id_atg_members', $member->id)
            //                             ->whereNull('end_date')
            //                             ->update(['end_date' => now()]);
            // $message = $affected > 0 
            // ? 'Rời CLB và cập nhật thông tin lớp học thành công' 
            // : 'Rời CLB thành công, không có lớp học nào cần cập nhật';

            $deleted = RegisterClass::where('id_atg_members', $member->id)->delete();
            DB::commit();
            $message = $deleted > 0 
                    ? 'Rời clb và xóa thành công thông tin lớp học '
                    : 'Rời clb thành công, không có lớp học nào cần xóa';
            return response()->json([
                'success' => $message,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getClubRegisteredClasses()
    {
        $user = JWTAuth::user();

        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        // Lấy thông tin thành viên từ bảng table_atg_members
        $member = table_atg_members::find($user->id);
        
        // Kiểm tra xem member có tồn tại và đã đăng ký vào câu lạc bộ chưa
        if (!$member || !$member->id_club) {
            return response()->json(['message' => 'Bạn chưa tham gia câu lạc bộ'], 200);
        }

        // Lấy thông tin câu lạc bộ từ bảng table_club dựa trên id_club của thành viên
        $club = Club::find($member->id_club);
        
        // Kiểm tra xem club có tồn tại không
        if (!$club) {
            return response()->json(['error' => 'Không tìm thấy thông tin câu lạc bộ'], 404);
        }

        // Trả về thông tin của câu lạc bộ
        return response()->json([
            'message' => 'Thông tin câu lạc bộ:',
            'club' => [
                'id' => $club->id,
                'ten' => $club->ten,
                'mota' => $club->tenkhongdau,
                'diachi' => $club->diachi,
                'dienthoai' => $club->dienthoai,
            ],
        ], 200); 
    }


    public function approveJoinRequest(Request $request)
    {
        $user = JWTAuth::user();

        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        // Kiểm tra nếu user không phải là HLV
        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền xem thông tin này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'id_club' => 'required|integer|exists:table_club,id', // Thêm validation cho id_club
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Lấy id_club của HLV (giả sử mỗi HLV chỉ có 1 id_club duy nhất)
        $clubId = $user->id_club;

        if (!$clubId) {
            return response()->json(['error' => 'Bạn chưa được giao quản lý câu lạc bộ nào'], 403);
        }

        // Kiểm tra xem id_club trong request có khớp với id_club của HLV không
        if ($request->id_club != $clubId) {
            return response()->json(['error' => 'Bạn không có quyền duyệt yêu cầu tham gia câu lạc bộ này'], 403);
        }

        try {
            DB::beginTransaction();

            // Tìm yêu cầu tham gia của thành viên trong câu lạc bộ của HLV
            $pendingRequest = Club_Pending::where('id_member', $request->id_member)
                ->where('id_club', $clubId)
                ->first();

            if (!$pendingRequest) {
                DB::rollBack();
                return response()->json(['error' => 'Không tìm thấy yêu cầu tham gia của thành viên này'], 404);
            }

            // Cập nhật id_club của thành viên
            $member = table_atg_members::find($request->id_member);
            $member->id_club = $clubId;
            $member->save();

            // Xóa tất cả các yêu cầu tham gia của thành viên này (trong trường hợp có nhiều yêu cầu)
            Club_Pending::where('id_member', $request->id_member)->delete();

            DB::commit();

            return response()->json(['success' => 'Đã duyệt thành viên tham gia câu lạc bộ thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function searchClubs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $keyword = $request->input('keyword');

        $clubs = Club::with('coach')
                     ->where('ten', 'LIKE', "%$keyword%") 
                     ->orWhere('tenkhongdau', 'LIKE', "%$keyword%")
                     ->get();

       
        $clubsInfo = $clubs->map(function ($club) {
            return [
                'id' => $club->id,
                'ten' => $club->ten,
                'mota' => $club->tenkhongdau,
                'diachi' => $club->diachi,
                'dienthoai' => $club->dienthoai ?: ($club->coach->dienthoai ?? 'Không có thông tin'),
                'nguoiquanly' => $club->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($clubsInfo);
    }


}
