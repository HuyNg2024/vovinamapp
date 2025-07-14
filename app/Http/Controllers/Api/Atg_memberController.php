<?php 
namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth; // Thêm khai báo JWTAuth
use App\Http\Controllers\Controller;
use App\Models\table_atg_members;
use App\Models\Classes;
use App\Models\Club;
use Illuminate\Http\Request;
use App\Models\EducationGrades;
use App\Models\News; 
use App\Models\dangkydai; 
use App\Models\RegisterClass;
use App\Models\KetQuaThi;
use App\Models\class_payment;
use App\Models\LeaveClassRequest;
use App\Models\LeaveClubRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


 
class Atg_memberController extends Controller
{
        public function index()
        {   
            $atg_membersAll = table_atg_members::select(
                'id','username','avatar','ten','dienthoai','email','diachi','gioitinh','ngaysinh','hotengiamho','dienthoai_giamho','lastlogin','chieucao','cannang')
            ->get()
            ->map(function($member){
                return [
                    'id' => $member->id,
                    'username' => $member->username,
                    'avatar' => $member->avatar,
                    'ten' => $member->ten,
                    'email' => $member->email,
                    'dienthoai' => $member->dienthoai,
                    'diachi' => $member->diachi,
                    'gioitinh' => $member->formatted_gioitinh,
                    'ngaysinh' => $member->formatted_ngaysinh,
                    'hotengiamho' => $member->hotengiamho,
                    'dienthoai_giamho' => $member->dienthoai_giamho,
                    'lastlogin' =>$member->lastlogin,
                    'chieucao' => $member->chieucao,
                    'cannang' => $member->cannang,
                ];
            });
            
            return response()->json($atg_membersAll, 200);
        }

        public function show($id)
        {
            $post = table_atg_members::find($id);
            if ($post) {
                return response()->json(['message'=>'Found information',$post], 200);
            }
            return response()->json(['message' => 'Post not found'], 404);
        }

        public function store(Request $request)
        {
            //  $post = table_atg_members::create($request->all());
            //  return response()->json($post, 201);
            
                // Validate request data if needed
                $validatedData = $request->validate([
                    'ten' => 'required|string',
                    'dienthoai' => 'required|string',
                    'diachi' => 'required|string',
                    //'ngaysinh' => 'required|date_format:Y-m-d',
                ]);
        
                // Create new member record
                $member = table_atg_members::create($validatedData);
        
                return response()->json(['message' => 'Member created successfully', 'data' => $member], 201);

        }

        public function update(Request $request, $id) 
        {
            $post = table_atg_members::find($id);
            if ($post) {
                $post->update($request->all());
                return response()->json($post, 200);
            }
            return response()->json(['message' => 'Post not found'], 404);
        }

        public function destroy($id)
        {
            $post = table_atg_members::find($id);
            if ($post) {
                $post->delete();
                return response()->json(['message' => 'Post deleted'], 200);
            }
            return response()->json(['message' => 'Post not found'], 404);
        }

        public function update2(Request $request, $id)
        {
            $post = table_atg_members::find($id);
            if ($post) {
                $validatedData = $request->validate([
                    'id_khoathi' => 'required|integer',
                ]);

                $post->update($validatedData);
                return response()->json($post, 200);
            }
            return response()->json(['message' => 'Post not found'], 404);
        }

        public function getCapdaiById()
        {
            // Xác thực người dùng bằng JWT
            $user = JWTAuth::user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Truy vấn để lấy id_capdai và ten từ bảng table_atg_members theo user đã xác thực
            $member = table_atg_members::select('id_capdai', 'ten')->where('id', $user->id)->first();

            if ($member) {
                return response()->json(['id_capdai' => $member->id_capdai, 'ten' => $member->ten], 200);
            }
            
            return response()->json(['message' => 'Member not found'], 404);
        }

        public function getCapdaiById2($id)
        {
            // Truy vấn để lấy id_capdai và ten từ bảng table_atg_members theo id
            $member = table_atg_members::select('id_capdai', 'ten')->where('id', $id)->first();

            if ($member) {
                return response()->json(['id_capdai' => $member->id_capdai, 'ten' => $member->ten], 200);
            }
            
            return response()->json(['message' => 'Member not found'], 404);
        }


        public function dangKyDai(Request $request)
        {
            // Xác thực người dùng bằng JWT
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate các trường bắt buộc
            $validator = Validator::make($request->all(), [
                'id_dai' => 'required|exists:table_educationgrades,id',
                'chi_phi' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Lấy thông tin thành viên (hiện tại là người dùng đã xác thực) và đai đã đăng ký
            $member = table_atg_members::findOrFail($user->id); // Sử dụng ID của người dùng đã xác thực
            $daiDangKy = EducationGrades::findOrFail($request->id_dai);
            $capDoHienTai = $member->educationGrade;

            // Kiểm tra điều kiện đăng ký đai (giống như trước)
            if (!$capDoHienTai || $daiDangKy->order <= $capDoHienTai->order) {
                return response()->json(['error' => 'Cấp đai đăng ký không hợp lệ.'], 400);
            }

            if ($daiDangKy->order - $capDoHienTai->order >= 2) {
                return response()->json(['error' => 'Chỉ được đăng ký lên đai cao hơn 1 bậc.'], 400);
            }

            // Tạo bản ghi đăng ký đai mới
            $ngayThi = Carbon::now()->addMonth(); 
            $order = dangkydai::create([
                'id_atg_members' => $member->id,  // Sử dụng ID của người dùng đã xác thực
                'id_dai' => $request->id_dai,
                'chi_phi' => $request->chi_phi,
                'ngay_tao' => now(),
                'ngay_thi' => $ngayThi,
                'trang_thai' => 'chưa thi',
                'trang_thai_thanh_toan' => 'chưa thanh toán', 
            ]);
            
            $id_dangkydai = $order->id;
            $link = 'https://app.giasuthethao.com/pay_dai/getlink?id=' . $id_dangkydai;

            return $link;
        }
        
        /*------------------------Yêu cầu rời lớp, Xem ds môn sinh yêu cầu rời, và duyệt rời lớp học(clb)(Hlv)------------------*/

        public function leaveClassRequest(Request $request)
        {
            $user = JWTAuth::user();
        
            // Lấy lớp học mà người dùng đang đăng ký và nằm trong khoảng thời gian hợp lệ (dựa trên class_payment)
            $registration = RegisterClass::where('id_atg_members', $user->id)
            ->whereHas('class.payments', function ($query) {
                $query->whereNotNull('created_at') // Kiểm tra created_at không null
                    ->whereNotNull('end_date')   // Kiểm tra end_date không null
                    ->where('created_at', '<=', Carbon::now())
                    ->where('end_date', '>=', Carbon::now());
            })
            ->first();

            if (!$registration) {
                return response()->json(['error' => 'Bạn hiện không đăng ký lớp học nào hoặc thông tin thanh toán không hợp lệ.'], 400); 
            }

        
            $id_class = $registration->id_class;
        
            // Kiểm tra xem đã có yêu cầu rời lớp chưa
            $existingRequest = LeaveClassRequest::where('id_atg_members', $user->id)
                ->where('id_class', $id_class)
                ->where('status', 'đang chờ duyệt')
                ->exists();
        
            if ($existingRequest) {
                return response()->json(['error' => 'Bạn đã có yêu cầu rời lớp rồi, đang chờ xử lý.'], 400);
            }
        
            // // Tạo yêu cầu rời lớp mới
            // $leaveRequest = LeaveClassRequest::create([
            //     'id_atg_members' => $user->id,
            //     'id_class' => $id_class,
            //     'status' => 'đang chờ duyệt',
            // ]);
        
            // return response()->json(['message' => 'Yêu cầu rời lớp đã được gửi thành công.', 'data' => $leaveRequest], 201);
            // Tạo yêu cầu rời lớp mới
            LeaveClassRequest::create([
                'id_atg_members' => $user->id,
                'id_class' => $id_class,
                'status' => 'đang chờ duyệt',
            ]);

            return response()->json(['message' => 'Yêu cầu rời lớp đã được gửi thành công.'], 201); 
        }

        public function approveLeaveClassRequest(Request $request)
        {
            $user = JWTAuth::user();

            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền duyệt yêu cầu.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_member' => 'required|integer|exists:table_atg_members,id',
                'id_class' => 'required|integer|exists:table_class,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            try {
                // Tìm yêu cầu rời lớp dựa trên id_member và id_class
                $leaveRequest = LeaveClassRequest::where('id_atg_members', $request->id_member)
                    ->where('id_class', $request->id_class)
                    ->where('status', 'đang chờ duyệt')
                    ->firstOrFail(); // Sử dụng firstOrFail để ném ngoại lệ nếu không tìm thấy

                // Kiểm tra xem HLV có phụ trách lớp học này không
                $class = Classes::where('id', $request->id_class)
                    ->where('id_atg_members', $user->id)
                    ->firstOrFail(); // Sử dụng firstOrFail để ném ngoại lệ nếu không tìm thấy

                // Xóa đăng ký lớp học
                RegisterClass::where('id_atg_members', $request->id_member)
                    ->where('id_class', $request->id_class)
                    ->delete();

                // Cập nhật trạng thái yêu cầu thành "đã duyệt"
                $leaveRequest->update(['status' => 'đã duyệt']);

                // Lấy thông tin thành viên
                $member = table_atg_members::findOrFail($request->id_member);

                // Kiểm tra xem thành viên có đang ở trong câu lạc bộ không
                if ($member->id_club > 0) {
                    // Xóa id_club của thành viên (rời khỏi câu lạc bộ)
                    $member->update(['id_club' => 0]);

                    return response()->json(['message' => 'Yêu cầu rời lớp đã được duyệt và thành viên đã rời khỏi câu lạc bộ.'], 200);
                } else {
                    return response()->json(['message' => 'Yêu cầu rời lớp đã được duyệt.'], 200);
                }

            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Không tìm thấy yêu cầu rời lớp hoặc lớp học.'], 404);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Đã xảy ra lỗi trong quá trình xử lý yêu cầu.'], 500);
            }
        }
        public function getLeaveClassRequests(Request $request)
        {
            $user = JWTAuth::user();
        
            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền xem danh sách yêu cầu.'], 403);
            }
        
            // Lấy danh sách các lớp học mà HLV này phụ trách
            $classes = Classes::where('id_atg_members', $user->id)->pluck('id');
        
            // Lấy danh sách yêu cầu rời lớp của các lớp học này
            $leaveRequests = LeaveClassRequest::whereIn('id_class', $classes)
                ->where('status', 'đang chờ duyệt') 
                ->with('member', 'class') // Eager load 'class' để truy cập tenen
                ->get();
        
            $lang = $request->get('lang', 'vi'); // Lấy giá trị lang từ request, mặc định là 'vi'
        
            // Trả về kết quả trực tiếp
            return response()->json($leaveRequests->map(function ($request) use ($lang) {
                return [
                    'id' => $request->id,
                    'id_member' => $request->id_atg_members,
                    'ten' => $request->member->ten, 
                    'id_class' => $request->id_class,
                    'class_name' => ($lang === 'en' && $request->class->tenen) ? $request->class->tenen : $request->class->ten, 
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                ];
            }));
        }
        public function getLeaveClassRequests2()
        {
            $user = JWTAuth::user();

            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền xem danh sách yêu cầu.'], 403);
            }

            // Lấy danh sách các lớp học mà HLV này phụ trách
            $classes = Classes::where('id_atg_members', $user->id)->pluck('id');

            // Lấy danh sách yêu cầu rời lớp của các lớp học này
            $leaveRequests = LeaveClassRequest::whereIn('id_class', $classes)
                ->where('status', 'đang chờ duyệt') 
                ->with('member') 
                ->get();

            // Trả về kết quả trực tiếp
            return response()->json($leaveRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'id_member' => $request->id_atg_members,
                    'ten' => $request->member->ten, 
                    'id_class' => $request->id_class,
                    'class_name' => $request->class->ten, 
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                ];
            }));
        }

    

        /*------------------------Yêu cầu rời clb(môn sinh), Xem ds môn sinh yêu cầu rời, và duyệt rời clb(Hlv)------------------*/
        public function leaveClubRequest(Request $request)
        {
            $user = JWTAuth::user();

            // Kiểm tra xem người dùng đã tham gia câu lạc bộ nào chưa
            if ($user->id_club == 0) {
                return response()->json(['error' => 'Bạn chưa tham gia câu lạc bộ nào.'], 400);
            }

            // Kiểm tra xem đã có yêu cầu rời câu lạc bộ nào đang chờ xử lý hay chưa
            $existingRequest = LeaveClubRequest::where('id_atg_members', $user->id)
                ->where('status', 'đang chờ duyệt')
                ->exists();

            if ($existingRequest) {
                return response()->json(['error' => 'Bạn đã có yêu cầu rời câu lạc bộ đang chờ xử lý.'], 400);
            }

            // Lấy id_class (nếu có)
            $id_class = null; 
            $registration = RegisterClass::where('id_atg_members', $user->id)->first();
            if ($registration) {
                $id_class = $registration->id_class;
            } 

            // Tạo yêu cầu rời câu lạc bộ mới
            LeaveClubRequest::create([
                'id_atg_members' => $user->id,
                'id_club' => $user->id_club, 
                'id_class' => $id_class, // Có thể là null nếu người dùng chưa đăng ký lớp
                'status' => 'đang chờ duyệt',
            ]);

            return response()->json(['message' => 'Yêu cầu rời câu lạc bộ đã được gửi thành công.'], 201);
        }


        public function getLeaveClubRequestsForCoach(Request $request)
        {
            $user = JWTAuth::user();

            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền xem danh sách yêu cầu.'], 403);
            }

            $clubId = $user->id_club;

            if (!$clubId) {
                return response()->json(['error' => 'Bạn chưa được giao quản lý câu lạc bộ nào.'], 403);
            }

            $leaveRequests = LeaveClubRequest::where('id_club', $clubId)
                ->where('status', 'đang chờ duyệt')
                ->with('member', 'club')
                ->get();

            // Đánh số thứ tự lại từ 1
            $leaveRequests = $leaveRequests->values()->map(function ($request, $index) {
                $request->id = $index + 1;
                return $request;
            });

            $lang = $request->get('lang', 'vi'); // Lấy giá trị lang từ request, mặc định là 'vi'

            return response()->json($leaveRequests->map(function ($request) use ($lang) {
                return [
                    'id' => $request->id,
                    'id_member' => $request->id_atg_members,
                    'ten' => $request->member->ten, // Sử dụng tên tiếng Việt (bỏ tenen)
                    'id_club' => $request->id_club,
                    'ten_club' => ($lang === 'en' && $request->club->tenen) ? $request->club->tenen : $request->club->ten,
                    'id_class' => $request->id_class,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                ];
            }));
        }
        

        public function getLeaveClubRequestsAllStatus()
        {
            $user = JWTAuth::user();

            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền xem danh sách yêu cầu.'], 403);
            }

            $clubId = $user->id_club;

            if (!$clubId) {
                return response()->json(['error' => 'Bạn chưa được giao quản lý câu lạc bộ nào.'], 403);
            }

            // Lấy danh sách các lớp học mà HLV này phụ trách
            $classes = Classes::where('id_atg_members', $user->id)->pluck('id');

            // Lấy danh sách tất cả các yêu cầu rời CLB của các lớp học này hoặc có id_class là null
            $leaveRequests = LeaveClubRequest::where(function ($query) use ($classes) {
                $query->whereIn('id_class', $classes)
                    ->orWhereNull('id_class');
            })
            ->with('member')
            ->get();

            // Đánh số thứ tự lại từ 1
            $leaveRequests = $leaveRequests->values()->map(function ($request, $index) {
                $request->id = $index + 1; // Gán lại id bắt đầu từ 1
                return $request;
            });

            // Trả về kết quả trực tiếp
            return response()->json($leaveRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'id_member' => $request->id_atg_members,
                    'ten' => $request->member->ten,
                    'id_class' => $request->id_class,
                    'ten_lop' => $request->class ? $request->class->ten : null, // Đổi tên trường thành ten_lop
                    'id_club' => $request->id_club,
                    'status' => $request->status,
                    'created_at' => $request->created_at,
                ];
            }));
        }

        public function approveLeaveClubRequest(Request $request)
        {
            $user = JWTAuth::user(); 

            if ($user->hlv === 0) {
                return response()->json(['error' => 'Chỉ HLV mới có quyền duyệt yêu cầu.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'id_member' => 'required|integer|exists:table_atg_members,id',
                'id_club' => 'required|integer|exists:table_club,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $clubId = $user->id_club;

            if (!$clubId) {
                return response()->json(['error' => 'Bạn chưa được giao quản lý câu lạc bộ nào.'], 403);
            }

            if ($request->id_club != $clubId) {
                return response()->json(['error' => 'Bạn không có quyền duyệt yêu cầu tham gia câu lạc bộ này'], 403);
            }

            DB::beginTransaction();

            $leaveRequest = LeaveClubRequest::where('id_atg_members', $request->id_member)
                ->where('id_club', $clubId)
                ->where('status', 'đang chờ duyệt') 
                ->first();

            if (!$leaveRequest) {
                DB::rollBack();
                return response()->json(['error' => 'Không tìm thấy yêu cầu rời câu lạc bộ đang chờ duyệt của thành viên này.'], 404);
            }

            $member = table_atg_members::find($request->id_member);
            if (!$member) {
                DB::rollBack();
                return response()->json(['error' => 'Không tìm thấy thông tin thành viên.'], 404);
            }

            // Kiểm tra xem thành viên có đăng ký lớp học nào trong câu lạc bộ không
            $hasRegisteredClass = $member->registeredClasses()
                ->whereHas('class', function ($query) use ($clubId) {
                    $query->where('id_club', $clubId);
                })
                ->exists();

            // Nếu có lớp học đã đăng ký, xóa bản ghi trong bảng register_class
            if ($hasRegisteredClass) {
                RegisterClass::where('id_atg_members', $request->id_member)
                    ->whereHas('class', function ($query) use ($clubId) {
                        $query->where('id_club', $clubId);
                    })
                    ->delete();
            }

            // Cập nhật id_club của thành viên thành 0 (rời khỏi câu lạc bộ)
            $member->id_club = 0;
            $member->save();

            // Cập nhật trạng thái yêu cầu thành "đã duyệt"
            $leaveRequest->update(['status' => 'đã duyệt']);

            DB::commit();

            // Hiển thị thông báo khác nhau tùy thuộc vào việc có lớp học đã đăng ký hay không
            $message = $hasRegisteredClass 
                ? 'Đã duyệt yêu cầu rời câu lạc bộ và rời lớp học cho môn sinh thành công.' 
                : 'Đã duyệt yêu cầu rời câu lạc bộ cho môn sinh thành công.';

            return response()->json(['success' => $message], 200);

        }

        public function rejectLeaveClassRequest(Request $request)
        {
            try {
               
                $user = JWTAuth::user();
               
                if (!$user) {
                    Log::warning('Cố gắng truy cập trái phép trong rejectLeaveClassRequest"', ['error' => 'User not authenticated']);
                    return response()->json(['error' => 'Người dùng chưa được xác thực.'], 401);
                }

               
                if ($user->hlv === 0) {
                    Log::warning('Cố gắng hủy yêu cầu của người dùng trái phép', ['user_id' => $user->id]);
                    return response()->json(['error' => 'Chỉ HLV mới có quyền hủy yêu cầu.'], 403);
                }

                
                $validator = Validator::make($request->all(), [
                    'id_member' => 'required|integer|exists:table_atg_members,id',
                    'id_class' => 'required|integer|exists:table_class,id',
                    'note' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    Log::warning('Xác thực không thành công trong rejectLeaveClassRequest', [
                        'user_id' => $user->id,
                        'errors' => $validator->errors()
                    ]);
                    return response()->json(['error' => $validator->errors()], 400);
                }

              
                $leaveRequest = LeaveClassRequest::where('id_atg_members', $request->id_member)
                    ->where('id_class', $request->id_class)
                    ->first();

                
                if (!$leaveRequest) {
                    Log::warning('Không tìm thấy yêu cầu rời lớp học', [
                        'user_id' => $user->id,
                        'id_member' => $request->id_member,
                        'id_class' => $request->id_class
                    ]);
                    return response()->json(['error' => 'Yêu cầu rời lớp học không tồn tại.'], 404);
                }

                
                $class = $leaveRequest->class;
                if ($class->id_atg_members != $user->id) {
                    Log::warning('"Cố gắng hủy yêu cầu rời lớp học', [
                        'user_id' => $user->id,
                        'leave_request_id' => $leaveRequest->id
                    ]);
                    return response()->json(['error' => 'Bạn không có quyền hủy yêu cầu này.'], 403);
                }

               
                if ($leaveRequest->id_atg_members != $request->id_member || $leaveRequest->id_class != $request->id_class) {
                    Log::warning('Yêu cầu rời lớp học không khớp', [
                        'user_id' => $user->id,
                        'leave_request_id' => $leaveRequest->id,
                        'provided_member_id' => $request->id_member,
                        'provided_class_id' => $request->id_class
                    ]);
                    return response()->json(['error' => 'Thông tin yêu cầu không khớp.'], 400);
                }

                
                $leaveRequest->update([
                    'status' => 'đã hủy',
                    'note' => $request->note
                ]);

                Log::info('Leave request canceled', [
                    'user_id' => $user->id,
                    'leave_request_id' => $leaveRequest->id
                ]);

                return response()->json(['message' => 'Yêu cầu rời lớp đã bị hủy.'], 200);

            } catch (\Exception $e) {
                Log::error('Đã xảy ra lỗi trong rejectLeaveClassRequest', [
                    'exception_message' => $e->getMessage(),
                    'request_data' => $request->all()
                ]);
                return response()->json(['error' => 'Đã xảy ra lỗi khi xử lý yêu cầu.'], 500);
            }
        }

        public function getLeaveClassRequestStatus(Request $request)
        {
            $user = JWTAuth::user();

            if (!$user) {
                return response()->json(['error' => 'Không được phép. Không tìm thấy người dùng hoặc token không hợp lệ.'], 401);
            }

            $validator = Validator::make($request->all(), [
                'id_class' => 'required|integer|exists:table_class,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Tìm yêu cầu rời lớp dựa trên id_member (từ user) và id_class từ request
            $leaveRequest = LeaveClassRequest::where('id_atg_members', $user->id)
                ->where('id_class', $request->id_class)
                ->first();

            if (!$leaveRequest) {
                return response()->json(['status' => 'không có yêu cầu'], 200); // Hoặc 404 nếu muốn báo không tìm thấy
            }

            return response()->json(['status' => $leaveRequest->status, 'note' => $leaveRequest->note], 200);
        }
    

}
