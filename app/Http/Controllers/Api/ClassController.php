<?php
namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use App\Models\Club;
use App\Models\Classes; 
use Illuminate\Http\Request;
use App\Models\class_payment;
use App\Models\Class_Pending;
use App\Models\RegisterClass;
use App\Models\EducationGrades;
use App\Models\table_atg_members;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Models\KetQuaThi;
use App\Models\News;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException; 

class ClassController extends Controller
{
    //Lấy tất cả class trong bảng table_class
    public function index()
    {

        $classes = Classes::with(['club', 'coach'])->get();
    
        $classesInfo = $classes->map(function($class) {
            return [
                'id' => $class->id,
                'ten' => $class->ten,
                'thoigian' => $class->thoigian,
                'giatien' => $class->gia,
                'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
                'club' => $class->club->ten ?? 'Không có thông tin',
                'giangvien' => $class->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($classesInfo);
    }

    public function store(Request $request)
    {
        $validator = validator($request->all(),[
            'ten' => 'required|string|max:255',
            'thogian' => 'required|integer', 
            'id_city' => 'required|integer',
            'id_club' => 'required|integer',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $class = Classes::create($validator);
        return response()->json($class, 201); 
    }
    
    //Lấy tất cả lớp trong clb của ng dùng đó
    public function getClassesinclub(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();
        $member = table_atg_members::find($user->id);
        
        if (!$member || !$member->id_club) {
            return response()->json(['error' => 'Bạn chưa tham gia CLB nào'], 400);
        }
        
        $classes = Classes::with(['club', 'coach'])
                      ->where('id_club', $member->id_club)
                      ->get();
    
        if ($classes->isEmpty()) {
            return response()->json(['error' => 'Không tìm thấy lớp học nào trong CLB của bạn'], 404);
        }

        $classesInfo = $classes->map(function($class)use($lang) {
            return [

                'id' => $class->id,
                'ten' =>$lang === 'en' ?  $class->tenen : $class->ten,
                'thoigian' => $lang === 'en' ?  $class->thoigianen : $class->thoigian,
                'giatien' => $class->gia,
                'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
                //'diachi' => $class->diachi,
                'club' =>$lang === 'en' ? $class->club->tenen ?? 'Không có thông tin' : $class->club->ten ?? 'Không có thông tin',
                'giangvien' => $class->coach->ten ?? 'Không có thông tin',
            ];

        });
    
        return response()->json($classesInfo);
    }

    //Lấy chi tiết thông tin lớp học trong clb ng dùng theo id_class
    public function getClassinclub(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $validator = validator($request->all(),[
            'id_class' => 'required|integer|exists:table_class,id',
        ]);
        if($validator->fails()){
            return response()->json(['error'=> $validator->errors()],400);
        }

        $user = JWTAuth::user();
        $member = table_atg_members::find($user->id);
        if(!$member || !$member->id_club){
            return response()->json(['error'=> 'Bạn chưa tham gia clb']);
        }

        $class = Classes::with(['club', 'coach'])
                        ->where('id_club', $member->id_club)
                        ->where('id', $request->id_class)
                        ->first();
        if(!$class){
            return response()->json(['error'=> 'Không tìm thấy lớp học này trong CLB của bạn'], 404);
        }

        $classInfo = [
            'id' => $class->id,
            'ten' =>$lang === 'en' ?  $class->tenen : $class->ten,
            'thoigian' => $lang === 'en' ?  $class->thoigianen : $class->thoigian,
            'giatien' => $class->gia,
            'dienthoai'=> $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
            //'diachi' => $class->diachi,
            'club' =>$lang === 'en' ? $class->club->tenen ?? 'Không có thông tin' : $class->club->ten ?? 'Không có thông tin',
            'giangvien' => $class->coach->ten ?? 'Không có thông tin',
        ];
        return response()->json($classInfo);

    }
    // ... Yêu cầu tham gia lớp học, tham gia (clb, lớp) cùng lúc và chọn phương thức thanh toán trực tiếp

    public function joinClassPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();
        $member_id = $user->id;
        
        //Kiểm tra xem thành viên đã đăng ký clb chưa
        // Kiểm tra xem lớp học có thuộc CLB của thành viên không
        $class = Classes::find($request->id_class);
        //$club = $class->id_club;

        // if ($user->id_club === null || $class->id_club != $user->id_club) {
        //     return response()->json(['error' => 'Bạn chưa đăng ký câu lạc bộ hoặc lớp học không thuộc câu lạc bộ của bạn. Vui lòng đăng ký clb trước và kiểm tra lại thông tin'], 400);
        // }
        // Kiểm tra xem thành viên đã đăng ký lớp học nào chưa
        $existingClass = RegisterClass::where('id_atg_members', $member_id)->exists();

        if ($existingClass) {
            return response()->json(['error' => 'Bạn đã đăng ký một lớp học. Không thể gửi yêu cầu tham gia lớp học khác.'], 400);
        }

        $existingRequest = Class_Pending::where('id_member', $member_id)
            ->where('id_class', $request->id_class)
            ->exists();

        if ($existingRequest) {
            return response()->json(['error' => 'Yêu cầu tham gia lớp học này đã tồn tại'], 400);
        }

        try {
            Class_Pending::create([
                'id_member' => $member_id,
                'id_club' => $class->id_club,
                'id_class' => $request->id_class
            ]);

            return response()->json([
                'success' => 'Yêu cầu tham gia lớp học đã được gửi',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //Lấy danh sách các lớp học đã yêu cầu mà thành viên chưa đóng tiền trên hệ thống(tức sẽ vào class_pending)
    public function getPendingClasses(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();
        $member_id = $user->id;

        $pendingClasses = Class_Pending::with(['class', 'class.coach', 'class.club'])
            ->where('id_member', $member_id)
            ->get();

        $classesInfo = $pendingClasses->map(function ($pendingClass)use($lang) {
            return [
                'id_class' => $pendingClass->id_class,
                'ten' =>$lang === 'en' ? $pendingClass->class->tenen : $pendingClass->class->ten,
                'thoigian' => $pendingClass->class->thoigian,
                'giatien' => $pendingClass->class->gia,
                'dienthoai' => $pendingClass->class->dienthoai ?: ($pendingClass->class->coach->dienthoai ?? 'Không có thông tin'),
                'id_club' => $pendingClass->id_club,
                'club' => $lang === 'en' ? $pendingClass->class->club->tenen ?? 'Không có thông tin': $pendingClass->class->club->ten ?? 'Không có thông tin',
                'giangvien' => $pendingClass->class->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($classesInfo);
    }

    //Hủy yêu cầu tham gia lớp theo id_class
    public function leaveClassPending(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = JWTAuth::user();
        $member_id = $user->id;

        try {
           
            $deleted = Class_Pending::where('id_member', $member_id)
                                ->where('id_class', $request->id_class)
                                ->delete();
            $deleted_payment = Class_payment::where('member_id',$member_id)
                                            ->where('id_class',$request->id_class)
                                            ->delete();

            if ($deleted || $deleted_payment) {
                return response()->json([
                    'success' => 'Yêu cầu tham gia lớp đã bị hủy',
                ], 200);
            } else {
                return response()->json(['error' => 'Không tìm thấy yêu cầu tham gia câu lớp này'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //Lấy ds các yêu cầu đk tới lớp của hlv đó
    public function getPendingClassesForCoach(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền xem thông tin này'], 403);
        }

        // Lấy các lớp học mà HLV này quản lý
        $classes = Classes::where('id_atg_members', $user->id)->pluck('id');

        if ($classes->isEmpty()) {
            return response()->json(['error' => 'Bạn chưa được giao quản lý lớp học nào'], 403);
        }

        $pendingClasses = Class_Pending::with('member', 'class')
            ->whereIn('id_class', $classes)
            ->get();
        $membersInfo = $pendingClasses->map(function ($pendingClass)use($lang){
            try{
                return [
                    'id_class' => $pendingClass->id_class,
                    'id_club' => $pendingClass->id_club,
                    'id_member' => $pendingClass->id_member,
                    'ten_member' => $pendingClass->member->ten,
                    'ten_class' =>$lang === 'en' ? $pendingClass->class->tenen : $pendingClass->class->ten,
                    'created_at' => $pendingClass->created_at
                ];
            }catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        return response()->json($membersInfo);
    }

    public function approveJoinClassRequest(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
        }

        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Kiểm tra xem HLV có quyền quản lý lớp học này không
        $class = Classes::where('id', $request->id_class)
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Bạn không có quyền duyệt yêu cầu tham gia lớp học này'], 403);
        }

        try {
            DB::beginTransaction();

            $pendingRequest = Class_Pending::where('id_member',$request->id_member)
                                        ->where('id_class', $request->id_class)
                                        ->first();
            if(!$pendingRequest){
                DB::rollBack();
                return response()->json(['error'=> 'Không tìm thấy yêu cầu tham gia của thành viên này'], 404);
            }

            // Kiểm tra xem thành viên đã đăng ký lớp học nào chưa
            $existingClass = RegisterClass::where('id_atg_members', $request->id_member)->first();
            if ($existingClass) {
                DB::rollBack();
                return response()->json(['error' => 'Thành viên đã đăng ký một lớp học khác'], 400);
            }

            $check_payment= class_payment::where('member_id', $request->id_member)
                                        ->where('id_class', $request->id_class)
                                    ->latest()
                                    ->first();
            if($check_payment){
                $check_payment->update(['status'=> 'thành công']);
                RegisterClass::create([
                    'id_atg_members' => $request->id_member,
                    'id_class' => $request->id_class,
                    'begin_date' => now(),
                    'end_date' => $check_payment->end_date, // lấy hạn từ class_payment
                ]);
            }else{
                // Tạo bản ghi mới trong class_payment nếu không tồn tại
                $class = Classes::findOrFail($request->id_class);
                $member = table_atg_members::findOrFail($request->id_member);
                $end_date = now()->addMonths(3);

                $check_payment = class_payment::create([
                    'member_id' => $request->id_member,
                    'hocphi' => $class->gia, // Giả sử giá của lớp học là học phí
                    'id_class' => $request->id_class,
                    'status' => 'thành công',
                    'name_member' => $member->ten,
                    'end_date' => $end_date,
                ]);

                RegisterClass::create([
                    'id_atg_members' => $request->id_member,
                    'id_class' => $request->id_class,
                    'begin_date' => now(),
                    'end_date' => $end_date, // lấy hạn từ class_payment
                ]);
            }

            
    
            // Cập nhật cấp đai "Tự vệ" cho thành viên nếu chưa có
            $member = table_atg_members::find($request->id_member);
            $tuVeGrade = EducationGrades::where('ten', 'Tự Vệ')->first();
            if (empty($member->id_capdai) && $tuVeGrade) {
                $member->update(['id_capdai' => $tuVeGrade->id]);
            }
            $member->update(['id_club' => $class->id_club]);
            Class_Pending::where('id_member', $request->id_member)->delete();

            DB::commit();
            
            return response()->json([
                'success'=> 'Đã duyệt thành viên tham gia lớp thành công',
                'member_id' => $request->id_member,
                'id_class' => $request->id_class,
            ]);
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectJoinClassRequest(Request $request)
    {
        $user = JWTAuth::user();
        if(!$user || $user->hlv ===0){
            return response()->json(['error'=> 'Chỉ Hlv mới có quyền thực hiện hành động này'], 403);
        }
        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'id_class' => 'required|integer|exists:table_class,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $class = Classes::where('id', $request->id_class)
                        ->where('id_atg_members', $user->id)
                        ->first();

        if(!$class){
            return response()->json(['error' => 'Bạn không có quyền từ chối yêu cầu tham gia lớp này'], 403);
        }

        try{
            DB::beginTransaction();
            $deleted = Class_Pending::where('id_member', $request->id_member)
                            ->where('id_class', $request->id_class)
                            ->delete();

            if ($deleted === 0) {
                DB::rollBack();
                return response()->json(['error'=>'Không tìm thấy yêu cầu tham gia của thành viên này'], 404);
            }

            //Xóa bản ghi thanh toán nếu có
            class_payment::where('member_id', $request->id_member)
                        ->where('id_class', $request->id_class)
                        ->delete();

            DB::commit();
            return response()->json([
                'success' => 'Đã từ chối yêu cầu tham gia lớp học',
                'member_id' => $request->id_member,
                'id_class' => $request->id_class,
            ]);
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error'=> $e->getMessage()], 500);
        }
    }
    
    public function updateMemberClassRequest(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user || $user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền thực hiện hành động này'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id_member' => 'required|integer|exists:table_atg_members,id',
            'old_class_id' => 'required|integer|exists:table_class,id',
            'new_class_id' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Kiểm tra xem HLV có quyền quản lý cả lớp cũ và lớp mới không
        $oldClass = Classes::where('id', $request->old_class_id)
        ->where('id_atg_members', $user->id)
        ->first();
        $newClass = Classes::where('id', $request->new_class_id)
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$oldClass || !$newClass) {
            return response()->json(['error' => 'Bạn không có quyền quản lý một trong hai lớp học này'], 403);
        }

        try{
            DB::beginTransaction();
            
            // Kiểm tra xem có yêu cầu tham gia lớp cũ không
            $oldPendingRequest = Class_Pending::where('id_member', $request->id_member)
            ->where('id_class', $request->old_class_id)
            ->first();

            if (!$oldPendingRequest) {
                DB::rollBack();
                return response()->json(['error' => 'Không tìm thấy yêu cầu tham gia lớp cũ'], 404);
            }

            $existingNewRequest = Class_Pending::where('id_member', $request->id_member)
            ->where('id_class', $request->new_class_id)
            ->first();

            if ($existingNewRequest) {
                DB::rollBack();
                return response()->json(['error' => 'Đã tồn tại yêu cầu tham gia lớp mới'], 400);
            }

            $oldPendingRequest = Class_Pending::where('id_member', $request->id_member)
                                             ->where('id_class', $request->old_class_id)
                                             ->update(['id_class' => $request->new_class_id]);
    

            DB::commit();
            
            return response()->json([
                'success'=> 'Đã cập nhật yêu cầu mới của thành viên',
                'member_id' => $request->id_member,
                'id_class_new' => $request->new_class_id,
            ]);
            
        } catch (\Exception $e){
            DB::rollBack();
            return response()->json(['error'=> $e->getMessage()], 500);
        }

    }
/*
    public function joinClass(Request $request)
    {
        $validator = validator($request->all(), [

            'id' => 'required|integer|exists:class_payment,id',
            'member_id' => 'required|integer|exists:class_payment,member_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        //$user = JWTAuth::user();
        //$member = table_atg_members::find($user->id);
        // if (!$member) {
        //     return response()->json(['error' => 'Không tìm thấy thông tin thành viên'], 404);
        // }
        
        $check_payment= class_payment::where('id', $request->id)
                                    ->where('member_id', $request->member_id)
                                    ->where('status','thành công')
                                    ->latest()
                                    ->first();
        if(!$check_payment){
            return response()->json(['error'=>'bạn chưa thanh toán lớp học !']);
        }

        // Lấy bản ghi "Tự vệ" từ bảng table_educationgrades
        $tuVeGrade = EducationGrades::where('ten', 'Tự Vệ')->first();

        // Kiểm tra nếu đã đăng ký lớp học nào chưa (tương tự như trên)
        $memberofClass = RegisterClass::where('id_atg_members', $request->member_id)->first();
        if ($memberofClass) {
            return response()->json(['error' => 'Bạn đã đăng ký lớp học rồi !'], 400);
        }

        // Kiểm tra xem lớp học có thuộc CLB của thành viên không
        $class = Classes::find($check_payment->id_class);
        $member = table_atg_members::find($check_payment->member_id);
        if ($member->id_club === null || $class->id_club != $member->id_club) {
            return response()->json(['error' => 'Bạn chưa đăng ký câu lạc bộ hoặc lớp học không thuộc câu lạc bộ của bạn'], 400);
        }

        try {
            // Kiểm tra xem thành viên đã có id_capdai chưa, nếu chưa và có bản ghi "Tự vệ" thì cập nhật
            if (empty($member->id_capdai) && $tuVeGrade) {
                $member->update(['id_capdai' => $tuVeGrade->id]);
            }

            // Tạo bản ghi mới trong bảng register_class
            RegisterClass::create([
                'id_atg_members' => $check_payment->member_id,
                'id_class' => $check_payment->id_class,
                'begin_date' => now(),
                'end_date' => $check_payment->end_date,
            ]);
            //thành công thì xóa luôn bản ghi trong bảng class_payment
            $check_payment->delete();
            return response()->json([
                'success' => 'Đăng ký lớp học thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }*/
      
    public function joinClass(Request $request)
{
    $validator = validator($request->all(), [

        'id' => 'required|integer|exists:class_payment,id',
        'member_id' => 'required|integer|exists:class_payment,member_id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $check_payment= class_payment::where('id', $request->id)
                                    ->where('member_id', $request->member_id)
                                    ->latest()
                                    ->first();

    $member = table_atg_members::find($request->member_id);
    $class = Classes::find($check_payment->id_class);

    // Kiểm tra xem lớp học có thuộc CLB của thành viên không
    if ($member->id_club === null || $class->id_club != $member->id_club) {
        return response()->json(['error' => 'Bạn chưa đăng ký câu lạc bộ hoặc lớp học không thuộc câu lạc bộ của bạn'], 400);
    }

    // Kiểm tra nếu đã đăng ký lớp học nào chưa
    $memberofClass = RegisterClass::where('id_atg_members', $request->member_id)->first();
    if ($memberofClass) {
        return response()->json(['error' => 'Bạn đã đăng ký lớp học rồi !'], 400);
    }

    if ($check_payment->status=='thành công') {
        // Đã thanh toán, xử lý tham gia lớp học
        try {
            // Lấy bản ghi "Tự vệ" từ bảng table_educationgrades
            $tuVeGrade = EducationGrades::where('ten', 'Tự Vệ')->first();

            // Kiểm tra xem thành viên đã có id_capdai chưa, nếu chưa và có bản ghi "Tự vệ" thì cập nhật
            if (empty($member->id_capdai) && $tuVeGrade) {
                $member->update(['id_capdai' => $tuVeGrade->id]);
            }

            // Tạo bản ghi mới trong bảng register_class
            RegisterClass::create([
                'id_atg_members' => $request->member_id,
                'id_class' => $check_payment->id_class,
                'id_club' => $class->id_club,
                'begin_date' => now(),
                'end_date' => $check_payment->end_date,
            ]);

            // Thành công thì xóa luôn bản ghi trong bảng class_payment
            $check_payment->delete();

            return response()->json([
                'success' => 'Đăng ký lớp học thành công',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } else {
        // Chưa thanh toán, tạo yêu cầu pending
        try {
            $existingRequest = Class_Pending::where('id_member', $request->member_id)
                ->where('id_class', $request->id_class)
                ->first();

            if ($existingRequest) {
                return response()->json(['error' => 'Yêu cầu tham gia lớp học này đã tồn tại'], 400);
            }

            Class_Pending::create([
                'id_member' => $request->member_id,
                'id_class' => $check_payment->id_class,
                'id_club' => $class->id_club,
            ]);

            return response()->json([
                'success' => 'Yêu cầu tham gia lớp học đã được gửi và đang chờ xét duyệt',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

    // public function update(Request $request, $id)
    // {
    //     $class = Classes::findOrFail($id);

    //     $validatedData = $request->validate([
    //         'ten' => 'sometimes|string|max:255',
    //         'thogian' => 'sometimes|integer',
    //         'id_city' => 'sometimes|integer',
    //         'id_club' => 'sometimes|integer',
    //     ]);

    //     $class->update($validatedData);
    //     return response()->json($class); 
    // }

    public function destroy($id)
    {
        $class = Classes::findOrFail($id);
        $class->delete();

        return response()->json(null, 204); 
    }

    public function getUserRegisteredClasses()
    {
        $user = JWTAuth::user();
        $member = table_atg_members::find($user->id);

        if (!$member) {
            return response()->json(['error' => 'Không tìm thấy thông tin thành viên'], 404);
        }

        $registeredClasses = RegisterClass::with('class.club', 'class.coach')
            ->where('id_atg_members', $member->id)
            ->get();

        if ($registeredClasses->isEmpty()) {
            return response()->json(['error' => 'Bạn chưa đăng ký lớp học nào'], 200);
        }

      
        $classesInfo = $registeredClasses->map(function($registration) {
            $class = $registration->class;
            return [
                'id' => $class->id,
                'ten' => $class->ten,
                'thoigian' => $class->thoigian,
                'giatien' => $class->gia,
                'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
                'club' => $class->club->ten ?? 'Không có thông tin',
                'giangvien' => $class->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($classesInfo);
    }

    public function leaveClass(Request $request)
    {
        // Xác thực người dùng bằng JWT
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }

        $memberId = $user->id; // Lấy id_atg_members trực tiếp từ người dùng đã xác thực

        // Xóa các bản ghi đăng ký lớp học của thành viên này
        $deleted = RegisterClass::where('id_atg_members', $memberId)->delete();

        if ($deleted > 0) {
            return response()->json(['success' => 'Bạn đã rời khỏi lớp học thành công'], 200);
        } else {
            return response()->json(['error' => 'Bạn chưa đăng ký lớp học nào'], 200);
        }
    }
   

    public function searchClasses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $keyword = $request->input('keyword');

        $classes = Classes::with(['club', 'coach'])
                         ->where('ten', 'LIKE', "%$keyword%") 
                         ->get();

        $classesInfo = $classes->map(function ($class) {
            return [
                'id' => $class->id,
                'ten' => $class->ten,
                'thoigian' => $class->thoigian,
                'giatien' => $class->gia,
                'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
                'club' => $class->club->ten ?? 'Không có thông tin',
                'giangvien' => $class->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($classesInfo);
    }

    public function getClassesByClubId(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $validator = Validator::make($request->all(), [
            'id_club' => 'required|integer|exists:table_club,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $classes = Classes::with(['club', 'coach'])
            ->where('id_club', $request->id_club)
            ->get();

        $classesInfo = $classes->map(function ($class)use($lang) {
            return [
                'id' => $class->id,
                'ten' =>$lang==='en' ? $class->tenen : $class->ten,
                'thoigian' =>$lang==='en' ? $class->thoigianen : $class->thoigian,
                'giatien' => $class->gia,
                'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
                'club' =>$lang==='en'? $class->club->tenen ?? 'Không có thông tin' : $class->club->ten ?? 'Không có thông tin',
                'giangvien' => $class->coach->ten ?? 'Không có thông tin',
            ];
        });

        return response()->json($classesInfo);
    }


    public function getClassById2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $class = Classes::with(['club', 'coach'])
            ->where('id', $request->id_class)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Không tìm thấy lớp học này'], 404);
        }

        $classInfo = [
            'id' => $class->id,
            'ten' => $class->ten,
            'thoigian' => $class->thoigian,
            'giatien' => $class->gia,
            'dienthoai' => $class->dienthoai ?: ($class->coach->dienthoai ?? 'Không có thông tin'),
            'club' => $class->club->ten ?? 'Không có thông tin',
            'giangvien' => $class->coach->ten ?? 'Không có thông tin',
        ];

        return response()->json($classInfo);
    }

    public function getClassById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $class = Classes::with(['club', 'coach'])
            ->where('id', $request->id_class)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Không tìm thấy lớp học này'], 404);
        }

        $lang = $request->get('lang', 'vi'); // Lấy giá trị lang từ request, mặc định là 'vi'

        $classInfo = [
            'id' => $class->id,
            'ten' => ($lang === 'en' && $class->tenen) ? $class->tenen : $class->ten,
            'thoigian' => ($lang === 'en' && $class->thoigianen) ? $class->thoigianen : $class->thoigian,
            'giatien' => $class->gia,
            'dienthoai' => $class->dienthoai ?: ($class->coach ? $class->coach->dienthoai : 'Không có thông tin'),
            'club' => ($lang === 'en' && $class->club && $class->club->tenen) ? $class->club->tenen : ($class->club ? $class->club->ten : 'Không có thông tin'),
            'giangvien' => ($lang === 'en' && $class->coach && $class->coach->tenen) ? $class->coach->tenen : ($class->coach ? $class->coach->ten : 'Không có thông tin'),
        ];

        return response()->json($classInfo);
    }


    //=====================================CURD===============================
    //CURD các lớp học trong clb của mình 
   
    // Create: Tạo lớp học mới trong câu lạc bộ của HLV
    public function create(Request $request)
    {
        $user = JWTAuth::user();
        
        $messages = [
            'thoigian.regex' => 'Định dạng thời gian không hợp lệ. Vui lòng sử dụng định dạng như "Thứ 7-CN: 18H30-20H" hoặc "Thứ 2-4-6: 17h30-18h45" hoặc "CN: 18h-20h".',
            'thoigianen.regex' => 'English Schedule invalid. Please use these formats such as: "Saturday-Sunday: 18H30-20H" or "Monday-Wednesday-Friday: 17h30-18h45" or "Sunday: 18h-20h".',
        ];
        
        $validator = Validator::make($request->all(), [
            'ten' => 'required|string|max:255',
            'tenen' => 'required|string|max:255',
            'thoigian' => ['required', 'string',
                            //'regex:/^((?:Thứ|CN)\s(?:\d+(?:-\d+)*|CN)(?:-(?:Thứ|CN)\s?(?:\d+(?:-\d+)*|CN))?:\s\d{1,2}[hH]\d{0,2}-\d{1,2}[hH]\d{0,2})$/'
                            'regex:/^(Thứ [\d-]+|CN)(?:-(Thứ [\d-]+|CN))*: (\d{1,2})h?(\d{2})?-(\d{1,2})h?(\d{2})?$/'
                        ],         
            'thoigianen' => ['required','string',
                            //'regex:/^((?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s(?:\d+(?:-\d+)*|Sun)(?:-(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun)\s?(?:\d+(?:-\d+)*|Sun))?:\s\d{1,2}[hH]\d{0,2}-\d{1,2}[hH]\d{0,2})$/',
                            'regex:/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)(?:-(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday))*: (\d{1,2})h?(\d{2})?-(\d{1,2})h?(\d{2})?$/'
                        ],  
            'gia' => 'required|numeric',
            'diachi' => 'required|string',
            'diachien' => 'required|string',
            'dienthoai' => 'required|string',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (!$user->id_club) {
            return response()->json(['error' => 'Bạn không thuộc câu lạc bộ nào'], 403);
        }

        $class = new Classes($request->all());
        $class->id_atg_members = $user->id;
        $class->id_club = $user->id_club;
        // Gán giá trị thời gian là timestamp Unix
        $class->ngaytao = time();
        $class->ngaysua = time();
        $club = Club::find($user->id_club);
        if ($club) {
            $class->ten_club = $club->ten;
            $class->cluben = $club->tenen;
        }

        $class->tenhlv = $user->ten;
        $class->hlven = $user->tenen ?? $user->ten;
        
        $class->save();

        return response()->json(['success' => 'Lớp học đã được tạo thành công', 'class' => $class], 201);
    }

    // Read: Lấy thông tin của một lớp học trong câu lạc bộ của HLV
    public function read($id)
    {
        $user = JWTAuth::user();
        $class = Classes::with(['club', 'coach'])
                        ->where('id', $id)
                        ->where('id_club', $user->id_club)
                        ->first();

        if (!$class) {
            return response()->json(['error' => 'Không tìm thấy lớp học hoặc lớp học không thuộc câu lạc bộ của bạn'], 404);
        }

        return response()->json($class);
    }

    // Update: Cập nhật thông tin lớp học trong câu lạc bộ của HLV
    public function update(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();

        $messages = [
            'thoigian.regex' => 'Định dạng thời gian không hợp lệ. Vui lòng sử dụng định dạng như "Thứ 7-CN: 18H30-20H" hoặc "Thứ 2-4-6: 17h30-18h45" hoặc "CN: 18h-20h".',
            'thoigianen.regex' => 'English Schedule invalid. Please use these formats such as: "Saturday-Sunday: 18H30-20H" or "Monday-Wednesday-Friday: 17h30-18h45" or "Sunday: 18h-20h".',
        ];

        $validationRules = [
            'id_class' => 'required|integer|exists:table_class,id',
        ];

        if ($lang == 'vi') {
            $validationRules += [
                'ten' => 'sometimes|string|max:255',
                'thoigian' => ['sometimes', 'string', 'regex:/^(Thứ [\d-]+|CN)(?:-(Thứ [\d-]+|CN))*: (\d{1,2})h?(\d{2})?-(\d{1,2})h?(\d{2})?$/'],
                'gia' => 'sometimes|numeric',
                'diachi' => 'sometimes|string',
                'dienthoai' => 'sometimes|string',
            ];
        } else {
            $validationRules += [
                'tenen' => 'sometimes|string|max:255',
                'thoigianen' => ['sometimes', 'string', 'regex:/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)(?:-(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday))*: (\d{1,2})h?(\d{2})?-(\d{1,2})h?(\d{2})?$/'],
                'gia' => 'sometimes|numeric',
                'diachien' => 'sometimes|string',
                'dienthoai' => 'sometimes|string',
            ];
        }

        $validator = Validator::make($request->all(), $validationRules, $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $class = Classes::where('id', $request->id_class)
                        ->where('id_club', $user->id_club)
                        ->first();

        if (!$class) {
            return response()->json(['error' => 'Không tìm thấy lớp học hoặc lớp học không thuộc câu lạc bộ của bạn'], 404);
        }

        $updateData = $validator->validated();
        unset($updateData['id_class']); // Loại bỏ id_class khỏi dữ liệu cập nhật

        $class->fill($updateData);
        $class->ngaysua = time();
        $class->save();

        $message = $lang == 'vi' ? 'Lớp học đã được cập nhật thành công' : 'Class has been updated successfully';
        return response()->json(['success' => $message, 'class' => $class]);
    }

    // Delete: Xóa lớp học trong câu lạc bộ của HLV
    public function delete(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $validator = Validator::make($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = JWTAuth::user();
        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ HLV mới có quyền xóa thông tin clb này'], 403);
        }

        $class = Classes::where('id', $request->id_class)
                        ->where('id_club', $user->id_club)
                        ->first();

        if (!$class) {
            $error_message = $lang == 'vi' 
                ? 'Không tìm thấy lớp học hoặc lớp học không thuộc câu lạc bộ của bạn'
                : 'Class not found or does not belong to your club';
            return response()->json(['error' => $error_message], 404);
        }

        // Kiểm tra xem có thành viên nào đăng ký lớp học này không
        $registeredMembersCount = RegisterClass::where('id_class', $request->id_class)->count();

        if ($registeredMembersCount > 0) {
            $error_message = $lang == 'vi'
                ? 'Không thể xóa lớp học vì có thành viên đã đăng ký'
                : 'Cannot delete the class because there are registered members';
            return response()->json(['error' => $error_message], 400);
        }

        $class->delete();

        $success_message = $lang == 'vi'
            ? 'Lớp học đã được xóa thành công'
            : 'Class has been successfully deleted';
        return response()->json(['success' => $success_message]);
    }

    // List: Lấy danh sách các lớp học trong câu lạc bộ của HLV
    public function list()
    {
        $user = JWTAuth::user();
        $classes = Classes::with(['club', 'coach'])
                          ->where('id_club', $user->id_club)
                          ->get();

        return response()->json($classes);
    }

}
