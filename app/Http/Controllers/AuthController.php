<?php

namespace App\Http\Controllers;

//use App\Models\User;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Classes;
use Illuminate\Support\Str;
//---add recently
use Illuminate\Http\Request;
use App\Mail\ResetPasswordOTP;
use App\Mail\AccountCreatedMail;
use App\Mail\AccountDeletedMail;
use App\Models\table_atg_members;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\KetQuaThi;
use App\Models\RegisterClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','quickRegister']]);
    }

    // register
    public function register(Request $request){
        $validator = validator($request->all(), [
            'username' => 'required|string|unique:table_atg_members,username',
            'email' => 'required|string|email|unique:table_atg_members,email',
            'password' => 'required|string|min:6',
            'ten' => 'required|string',
            'dienthoai' => 'required|string|unique:table_atg_members,dienthoai',
            'diachi' => 'required|string',
            'gioitinh' => 'required|in:Nam,Nữ',
            'ngaysinh' => 'required|date_format:Y-m-d|before:today',
            'hotengiamho' => 'string|required_with:dienthoai_giamho',
            'dienthoai_giamho' => 'string|required_with:hotengiamho',
            'chieucao' => 'required|numeric',
            'cannang' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $gioitinhValue = $request->gioitinh === 'Nam' ? 1 : 0;

        try{
            $member  = new table_atg_members([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'ten' => $request->ten,
                'dienthoai' => $request->dienthoai,
                'diachi' => $request->diachi,
                'gioitinh' => $gioitinhValue,
                'ngaysinh' => $request->ngaysinh,
                'hotengiamho' => $request->hotengiamho,
                'dienthoai_giamho' => $request->dienthoai_giamho,
                'chieucao' => $request->chieucao,
                'cannang' => $request->cannang,
            ]);
            $member ->save();

            return response()->json(['success' => 'Đăng ký tài khoản thành công'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $login_type = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) 
            ? 'email' 
            : (is_numeric($request->input('login')) ? 'dienthoai' : 'username');

        $request->merge([
            $login_type => $request->input('login')
        ]);

        $credentials = $request->only($login_type, 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Sai thông tin đăng nhập hoặc mật khẩu'], 401);
        }

        $member = JWTAuth::user();
        // Invalidate the old token if exists
        if ($member->active_token) {
            try {
                JWTAuth::setToken($member->active_token)->invalidate();
            } catch (\Exception $e) {
                // Ignore token invalidation errors
            }
        }
        $memberId = $member->id;
        $memberRole = $member->hlv;

        $customClaims = [
            'member_id' => $memberId,
            'role' => $memberRole
        ];
        $newToken = JWTAuth::claims($customClaims)->fromUser($member);

        //if not exist, allowing to generate new
        $member->active_token = $newToken;
        $member->lastlogin = Carbon::now();
        $member->save();
        return $this->respondWithToken($newToken, $memberId);
    }
    //get all profile in table_atg_members
    public function profileAll(Request $request)
    {   
        $lang = $request->input('lang', 'vi');

        $atg_membersAll = table_atg_members::select(
            'id','username','avatar','ten','dienthoai','email','diachi','gioitinh','ngaysinh','hotengiamho','dienthoai_giamho','lastlogin','chieucao','cannang','id_club', 'id_capdai')
        ->with('educationGrade')
        ->get()
        ->map(function($member) use ($lang) {
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
                'id_club' => $member->id_club !== 0 ? $member->id_club : ($lang=='en' ? "You haven't joined any club" : "Bạn chưa tham gia clb nào"),
                'id_capdai' => $member->id_capdai !==0 ? $member->id_capdai : ($lang=='en' ? "No information": "không có thông tin"),
                'tencapdai' => $member->id_capdai !==0
                    ? ($lang=='en' ? $member->educationGrade->tenen : $member->educationGrade->ten)
                    : ($lang=='en' ? "No information": "Không có thông tin"),
            ];
        });
        
        return response()->json($atg_membersAll, 200);
    }

    //get profile via id
    public function profileviaID(Request $request)
    {   
        $lang = $request->input('lang', 'vi');
        $validator = validator($request->all(),[
            'id_atg_members' => 'required|integer|exists:table_atg_members,id',
        ]);
        if($validator->fails()){
            return response()->json(['error'=>$validator->errors()],400);
        }
        
        $member = table_atg_members::select(
            'id','username','avatar','ten','dienthoai','email','diachi','gioitinh','ngaysinh',
            'hotengiamho','dienthoai_giamho','lastlogin','chieucao','cannang', 'id_club', 'id_capdai')
        ->with('educationGrade')
        ->where('id',$request->id_atg_members)
        ->first();
        if(!$member){
            return response()->json(['error'=>'Không tìm thấy người dùng']);
        }
        $memberInfo=[
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
                'id_club' => $member->id_club !== 0 ? $member->id_club : ($lang=='en' ? "You haven't joined any club" : "Bạn chưa tham gia clb nào"),
                'id_capdai' => $member->id_capdai !==0 ? $member->id_capdai : ($lang=='en' ? "No information": "không có thông tin"),
                'tencapdai' => $member->id_capdai !==0
                    ? ($lang=='en' ? $member->educationGrade->tenen : $member->educationGrade->ten)
                    : ($lang=='en' ? "No information": "Không có thông tin"),
            ];
        
        return response()->json($memberInfo, 200);
    }

    //get detail profile myself
    public function me(Request $request)
    {
        $lang = $request->input('lang', 'vi');
        $member = JWTAuth::user();
        $memberInfo = [
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
            'lastlogin' => $member->lastlogin,
            'chieucao' => $member->chieucao,
            'cannang' => $member->cannang,
            'id_club' => $member->id_club !== 0 ? $member->id_club : ($lang=='en' ? "You haven't joined any club" : "Bạn chưa tham gia clb nào"),
            'ten_club'=> $member->id_club!==0
                ? ($lang=='en' ?$member->club->tenen : $member->club->ten)
                : ($lang=='en' ? "You haven't joined any club!" : "Bạn chưa tham gia câu lạc bộ nào!"),
            'id_capdai' => $member->id_capdai !==0 ? $member->id_capdai : ($lang=='en' ? "No information": "không có thông tin"),
            'tencapdai' => $member->id_capdai !==0
                    ? ($lang=='en' ? $member->educationGrade->tenen : $member->educationGrade->ten)
                    : ($lang=='en' ? "No information": "Không có thông tin"),
            'ten_lop' => $member->registeredClasses->isnotEmpty()
                ? ($lang== 'en'
                    ? $member->registeredClasses->first()->class->tenen 
                    : $member->registeredClasses->first()->class->ten)
                : ($lang== 'en' ? "You haven't joined any class" : "Bạn chưa tham gia lớp nào"),
        ];
        return response()->json($memberInfo);
    }
    
    public function getMemberclub(Request $request)
    {
        $lang = $request->query('lang','vi');
        $user = JWTAuth::user();
        // Kiểm tra xem người dùng có thuộc câu lạc bộ nào không
        if ($user->id_club == 0 || is_null($user->id_club)) {
            return response()->json(['error' => 'Bạn chưa tham gia câu lạc bộ nào'], 404);
        }
        // Lấy thông tin thành viên và câu lạc bộ
        $clubMember = table_atg_members::with('club.coach')->find($user->id);

        if (is_null($clubMember) || is_null($clubMember->club)) {

            return response()->json(['error' => 'Không tìm thấy câu lạc bộ'], 404);

        }

        $clubsInfo = [
                'id_club' => $clubMember->club->id,
                'image' => $clubMember->club->image,
                'ten' =>$lang ==='en' ? $clubMember->club->tenen : $clubMember->club->ten,
                'mota' => $clubMember->club->tenkhongdau,
                'diachi' =>$lang ==='en'? $clubMember->club->diachien : $clubMember->club->diachi,
                'dienthoai' => $clubMember->club->dienthoai ? $clubMember->club->dienthoai : ($clubMember->dienthoai ?? 'Không có thông tin'),
                'nguoiquanly' => $clubMember->club->coach->ten ?? 'Không có thông tin',
                'id_district' => $clubMember->club->id_district,
            ];
        return response()->json($clubsInfo);
    }

    public function updateInfo(Request $request)
    {   
        $user = JWTAuth::user();
        $validator = validator($request->all(), [
            'username' => 'sometimes|required|string|unique:table_atg_members,username,'. $user->id,
            'email' => 'sometimes|required|string|email|unique:table_atg_members,email,' . $user->id,
            'ten' => 'sometimes|required|string',
            'dienthoai' => 'sometimes|required|string|unique:table_atg_members,dienthoai,' . $user->id,
            'diachi' => 'sometimes|required|string',
            'gioitinh' => 'sometimes|required|in:Nam,Nữ',
            'ngaysinh' => 'sometimes|required|date_format:Y-m-d|before:today',
            'hotengiamho' => 'sometimes|string|required_with:dienthoai_giamho',
            'dienthoai_giamho' => 'sometimes|string|required_with:hotengiamho',
            'chieucao' => 'sometimes|required|numeric',
            'cannang' => 'sometimes|required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        try {
            $member = JWTAuth::user();
            
            $updateFields = $request->only([
                'username',
                'email',
                'ten',
                'dienthoai',
                'diachi',
                'gioitinh',
                'ngaysinh',
                'hotengiamho',
                'dienthoai_giamho',
                'chieucao',
                'cannang',
            ]);
    
            if (isset($updateFields['gioitinh'])) {
                $updateFields['gioitinh'] = $updateFields['gioitinh'] === 'Nam' ? 1 : 0;
            }
    
            if (isset($updateFields['ngaysinh'])) {
                try {
                    $ngaysinh = trim($updateFields['ngaysinh']);
                    $ngaysinhTimestamp = Carbon::createFromFormat('Y-m-d', $ngaysinh)->startOfDay()->timestamp;
                    $age = Carbon::now()->diffInYears(Carbon::createFromTimestamp($ngaysinhTimestamp));
    
                    if ($age < 18) {
                        if (empty($updateFields['hotengiamho']) || empty($updateFields['dienthoai_giamho'])) {
                            return response()->json(['error' => 'Yêu cầu họ tên và số điện thoại phụ huynh cho trẻ dưới 18.'], 400);
                        }
                    }
    
                    $updateFields['ngaysinh'] = $ngaysinhTimestamp;
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Ngày sinh không hợp lệ.'], 400);
                }
            }
    
            $member->update($updateFields);
            $member->refresh();

            return response()->json(['success' => 'Cập nhật thông tin thành công.','data' => $member], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request){
        $validator = validator($request->all(), [
            'current_pass' => 'required|string',
            'new_pass' => 'required|string|min:6|different:current_pass',
            'new_pass_confirmation' => 'required|same:new_pass'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $member = JWTAuth::user();
        if(!Hash::check($request->current_pass, $member->password)){
            return response()->json(['error' => 'Mật khẩu hiện tại không đúng'], 400);
        }
        $member->password = Hash::make($request->new_pass);
        $member->save();

        return response()->json(['success' => 'Mật khẩu cập nhật thành công.']);
    }   

    public function logout(){
        try{
            $user= JWTAuth::user();
            $user->active_token = null;
            $user->save();

            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['success'=> 'Đăng xuất thành công!!']);
        }catch(\Exception $e){
            return response()->json(['error'=>'Đăng xuất thất bại'], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            return $this->respondWithToken($newToken);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Refresh token không được, vui lòng thử lại.'], 500);
        }
    }

    protected function respondWithToken($token, $memberId= null)
    {
        $user = JWTAuth::user();
        $roles = $user->roles;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'member_id' => $memberId,
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $validator = validator($request->all(), ['email' => 'required|email']);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT ? response()->json(['success' => 'Reset password OTP sent to your email'], 200) : response()->json(['error' => 'Unable to send reset otp'], 400);
    }

    public function resetPassword(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'otp' => 'required|string',
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $response == Password::PASSWORD_RESET
                ? response()->json(['success' => 'Password reset successfully'], 200)
                : response()->json(['error' => 'Unable to reset password'], 400);
    }

    //đăng ký 3 in 1
    public function quickRegister(Request $request)
    {
        $validator = validator($request->all(),[
            'id_class' => 'required|exists:table_class,id',
            'email' => 'required|string|email|unique:table_atg_members',
            'dienthoai' => 'required|string|unique:table_atg_members',
            'chieucao' => 'required|numeric',
            'cannang' => 'required|numeric',
        ]);
        if($validator->fails()){
            return response()->json(['error' => $validator->errors()],400);
        }

        //Tạo mật khẩu ngẫu nhiên 
        $password = Str::random(6);

        try{
            $member = new table_atg_members([
                'email' => $request->email,
                'dienthoai' =>$request->dienthoai,
                'password' => Hash::make($password),
                'chieucao' => $request->chieucao,
                'cannang' => $request->cannang,
                'username' => $request->dienthoai, //sd sđt làm username
                'ten' => $request->dienthoai,

                'diachi' => 'TP HCM',
                'gioitinh' => 1,
                'ngaysinh' => '2000-01-01',
                'hotengiamho' => 'Nguyen Van A',
                'dienthoai_giamho' => '0999999999',
            ]);
            $member->save();

            //Gửi mail vs thông tin đăng nhập
            Mail::to($request->email)->send(new AccountCreatedMail($request->dienthoai,$password));

            $class = Classes::find($request->id_class);
            //Thêm yêu cầu tham gia lớp học
            DB::table('class_pending')->insert([
                'id_member' => $member->id,
                'id_club' => $class->id_club,
                'id_class' => $request->id_class,
            ]);

            return response()->json([
                'success' => 'Đăng ký tài khoản thành công. Vui lòng kiểm tra email để nhận thông tin đăng nhập.',
                'data' => [
                    'password'=>$password,
                    'email' => $request->email,
                ],
            ], 201);
        }catch (\Exception $e){
            return response()->json(['error'=> $e->getMessage()], 400);
        }
    }

    public function deleteAccount(Request $request)
    {
        $validator = validator($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Mật khẩu không đúng'], 401);
        }
        
        try {
            // Xóa các bản ghi liên quan trong các bảng khác
            // DB::table('register_class')->where('id_atg_members', $user->id)->delete();
            // DB::table('table_checkin')->where('ma_nv', $user->id)->delete();
            // DB::table('table_ketquathi')->where('id_member', $user->id)->delete();

            $pendingOrders = Order::where('member_id', $user->id)
                        ->where('giao_hang', '!=', 'đã giao hàng')
                        ->exists();
            if($pendingOrders){
                return response()->json(['error'=> 'Còn đơn hàng đang đợi xử lý. Không thể xóa tài khoản'],400);
            }

            $activeClass= RegisterClass::where('id_atg_members', $user->id)->exists();
            if($activeClass){
                return response()->json(['error'=> 'Bạn đang tham gia lớp học. Không thể xóa tài khoản'], 400);
            }

            $activeExam = KetQuaThi::where('id_member', $user->id)->exists();
            if($activeExam){
                return response()->json(['error'=> 'Bạn đã có đăng ký thi lên đai. Không thể xóa tài khoản'], 400);
            }

            $userEmail = $user->email;
            $username = $user->username;
            if($user->id_cub ==0){
                DB::table('class_pending')->where('id_member', $user->id)->delete();
                //xóa account khách hoàn toàn
                $user->delete();
                $message= 'Tài khoản đã được xóa hoàn toàn'; 
            }else{
                // Cập nhật deleted=1 cho tài khoản thành viên hoặc HLV
                $user->deleted=1;
                $user->save();
                $message = 'Tài khoản đã được đánh dấu là đã xóa';
            }

            // Gửi email xác nhận
            Mail::to($userEmail)->send(new AccountDeletedMail($username));
            
            // Vô hiệu hóa token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['success' => $message], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể xóa tài khoản. ' . $e->getMessage()], 500);
        }
    }
}
