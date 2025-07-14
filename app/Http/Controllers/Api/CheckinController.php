<?php

namespace App\Http\Controllers\Api;


use Carbon\Carbon;
use App\Models\Checkin;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CheckinController extends Controller
{
    public function getTeacherClasses(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();
        
        if($user->hlv === 0){
            return response()->json(['error'=> 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $classes = Classes::where('id_atg_members', $user->id)
                        ->withCount('registeredMembers') // count members in class
                        ->select('id', 'ten', 'tenen', 'thoigian', 'thoigianen', 'gia') // Chọn các trường cần thiết
                        ->get()
                        ->map(function ($class) use ($lang) {
                            return [
                                'id' => $class->id,
                                'ten' => $lang === 'en' ? $class->tenen : $class->ten,
                                'thoigian' => $lang === 'en' ? $class->thoigianen : $class->thoigian,
                                'giatien' => $class->gia,
                                'so_luong_hoc_sinh' => $class->registeredMembers()->count(), // bổ sung count members t's class
                            ];
                        });

        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }

    public function getClassMembers(Request $request){
        $user = JWTAuth::user();
        if($user->hlv===0){
            return response()->json(['error'=>'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $validator = validator($request->all(),[
            'id_class' => 'required|integer|exists:table_class,id',
        ]);

        if($validator->fails()){
            return response()->json(['error'=> $validator->errors()],400);
        }

        //Get info and time in class
        $class = Classes::findorFail($request->id_class);
        $thoigianInfo = $this->parseThoigian($class->thoigian);
        if(!$thoigianInfo){
            return response()->json(['error'=>'Không thể phân tích thời gian lớp học. Vui lòng chỉnh lại dữ liệu thành dạng như Thứ 2-4-6: 18h-19h30'],400);
        }

        $now = Carbon::now('Asia/Ho_Chi_Minh');
        $currentDayOfWeek = $now->dayOfWeekIso+1;
        $currentTime = $now->format('H:i');

        $isClassTime = in_array($currentDayOfWeek, $thoigianInfo['days']) && 
                        $currentTime >= $thoigianInfo['startTime'] &&
                        $currentTime <= $thoigianInfo['endTime'];
        $members = Db::table('register_class')
            ->join('table_atg_members','register_class.id_atg_members', '=', 'table_atg_members.id')
            ->where('register_class.id_class',$request->id_class)
            ->select('table_atg_members.id', 'table_atg_members.ten', 'register_class.begin_date')
            ->get();
        
        return response()->json([
            'in_class_time' => $isClassTime,
            'data' => $members
        ]);

    }

    public function teacherCheckin(Request $request){
        $memberRole = JWTAuth::user()->hlv;
        
        if($memberRole === 0){
            return response()->json(['error'=> 'Chỉ HLV mới có quyền điểm danh'], 403);
        }
        $validator = validator($request->all(),[
            'id_class' => 'required|integer|exists:table_class,id',
            'date' => 'required|date_format:Y-m-d',
            'attendees' => 'required|array',
            'attendees.*.id_atg_member' => 'required|integer|exists:table_atg_members,id',
            //'attendees.*.status' => 'required|in:present,absent,late'
            'attendees.*.in' => 'required|date_format:H:i',
            'attendees.*.out' => 'required|date_format:H:i|after:attendees.*.in'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        DB::beginTransaction();

        try{
            // $existingCheckins = Checkin::where([
            //     'date' => $request->date,
            //     'id_class' => $request->id_class,
            // ])->exists();

            // if ($existingCheckins) {
            //     DB::rollBack();
            //     return response()->json(['error' => 'Đã có điểm danh cho lớp học này trong ngày này'], 400);
            // }


            //Get date and time from thoigian in tabel_class
            $class = Classes::findOrFail($request->id_class);
            $thoigianInfo = $this->parseThoigian($class->thoigian);
            
            if (!$thoigianInfo) {
                return response()->json(['error' => 'Không thể phân tích thời gian lớp học'], 400);
            }

            $checkDate = Carbon::parse($request->date);
            $dayOfWeek = $checkDate->dayOfWeekIso + 1;
            

            if (!in_array($dayOfWeek, $thoigianInfo['days'])) {
                return response()->json(['error' => 'Ngày điểm danh không phải là ngày học của lớp'], 400);
            }
            // Lấy ds các thành viên trong lớp
            $registeredStudents = DB::table('register_class')
            ->where('id_class', $request->id_class)
            ->pluck('id_atg_members')
            ->toArray();
            $checkins = [];
            $checkedMembers = [];

            foreach ($request->attendees as $attendee) {
                 // Kiểm tra xem học viên có đăng ký lớp học không
                if (!in_array($attendee['id_atg_member'], $registeredStudents)) {
                    return response()->json([
                        'error' => 'Học viên không đăng ký lớp học này',
                        'member_id' => $attendee['id_atg_member']
                    ], 400);
                }

                //Check if the member has already been checked in for that day
                $existingCheckin = Checkin::where('ma_nv',$attendee['id_atg_member'])
                                        ->where('date',$request->date)
                                        ->first();
                if($existingCheckin){
                    return response()->json([
                        'error' => 'Học viên đã được điểm danh rồi',
                        'member_id' => $attendee['id_atg_member']
                    ], 400);
                }
                $inTime = Carbon::parse($attendee['in']);
                $classStartTime = Carbon::parse($thoigianInfo['startTime']);
    
                if ($inTime->lt($classStartTime)) {
                    return response()->json([
                        'error' => 'Thời gian vào lớp sớm hơn giờ bắt đầu của lớp học',
                        'member_id' => $attendee['id_atg_member']
                    ], 400);
                }


                $checkins[] = [
                    'ma_nv' => $attendee['id_atg_member'],
                    'id_class' => $request->id_class,
                    'date' => $request->date,
                    'in' => $attendee['in'],
                    'out' => $attendee['out'],
                    'updated_note' => '',
                    'options' => '',
                ];

                $checkedMembers[] = $attendee['id_atg_member'];
            }

            // Check for duplicate check-ins within the request
            if (count($checkedMembers) !== count(array_unique($checkedMembers))) {
                return response()->json([
                    'error' => 'Có học viên được điểm danh nhiều lần trong cùng một yêu cầu'
                ], 400);
            }
            

            Checkin::insert($checkins);

            DB::commit();
            return response()->json([
                    'success' => 'Thành công điểm danh lớp. =',
                ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


     //thành viên xem thông tin điểm danh của mình
    public function memberViewCheckin(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();
        
        $validator = validator($request->all(), [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Lấy thông tin lớp học mà thành viên đã đăng ký
        $registeredClass = DB::table('register_class')
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$registeredClass) {
            return response()->json(['error' => 'Bạn chưa đăng ký lớp học nào'], 404);
        }

        $class = Classes::find($registeredClass->id_class);

        $checkins = Checkin::where('ma_nv', $user->id)
            ->where('id_class', $registeredClass->id_class)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        $attendanceData = [];
        foreach ($checkins as $checkin) {
            $checkDate = Carbon::parse($checkin->date);
            $dayOfWeek = $checkDate->dayOfWeekIso;
            $dayName = $this->getDayName($dayOfWeek);

            $attendanceData[] = [
                'date' => $checkin->date,
                'day_of_week' => $dayName,
                'in' => $checkin->in,
                'out' => $checkin->out,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'class_name' =>$lang ==='en' ? $class->tenen : $class->ten,
                'begin_date' => $registeredClass->begin_date,
                'attendance' => $attendanceData,
            ],
        ]);
    }

 
     // Cập nhật phương thức cho giáo viên xem thông tin điểm danh của các lớp trong CLB
     public function teacherViewCheckinAll(Request $request)
     {
         $user = JWTAuth::user();
         
         if($user->hlv === 0){
             return response()->json(['error'=> 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
         }
 
         $validator = validator($request->all(),[

            //'id_club' => 'required|integer|exists:table_club,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',

         ]);
 
         if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 400);
         }
 
         // Lấy danh sách các lớp trong CLB mà giáo viên này phụ trách

        $classes = Classes::where('id_club', $user->id_club)
            ->where('id_atg_members', $user->id)
            ->get();

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $attendanceData = [];
        foreach ($classes as $class) {
            $checkins = Checkin::where('id_class', $class->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->with(['member:id,ten'])
                ->orderBy('date', 'asc')
                ->get();

            $classAttendance = [];
            foreach ($checkins as $checkin) {
                $checkDate = Carbon::parse($checkin->date);
                $dayOfWeek = $checkDate->dayOfWeekIso;
                $dayName = $this->getDayName($dayOfWeek);

                $classAttendance[$checkin->date][] = [
                    'member_id' => $checkin->member->id,
                    'member_name' => $checkin->member->ten,
                    'in' => $checkin->in,
                    'out' => $checkin->out,
                    'day_of_week' => $dayName,
                ];
            }

            $attendanceData[] = [
                'class_id' => $class->id,
                'class_name' => $class->ten,
                'attendance' => $classAttendance,
            ];
        }

        return response()->json([
            'success' => 'Thành công',
            'data' => $attendanceData,
         ]);
    }

    public function teacherViewCheckin(Request $request)
     {
        $lang = $request->query('lang', 'vi');
         $user = JWTAuth::user();
         
         if($user->hlv === 0){
             return response()->json(['error'=> 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
         }
 
         $validator = validator($request->all(),[

            'id_class' => 'required|integer|exists:table_class,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',

         ]);
 
         if ($validator->fails()) {
             return response()->json(['error' => $validator->errors()], 400);
         }
 
         // Lấy lớp trong CLB mà giáo viên này phụ trách

        $class = Classes::where('id',$request->id_class)
                        ->where('id_atg_members',$user->id)                
        ->first();
        if(!$class){
            return response()->json(['error'=>'Bạn không quản lý lớp này']);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        
        $checkins = Checkin::where('id_class', $class->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['member:id,ten'])
            ->orderBy('date', 'asc')
            ->get();

        $classAttendance = [];
        foreach ($checkins as $checkin) {
                $checkDate = Carbon::parse($checkin->date);
                $dayOfWeek = $checkDate->dayOfWeekIso;
                $dayName = $this->getDayName($dayOfWeek);

                $classAttendance[$checkin->date][] = [
                    'member_id' => $checkin->member->id,
                    'member_name' => $checkin->member->ten,
                    'in' => $checkin->in,
                    'out' => $checkin->out,
                    'day_of_week' => $dayName,
                ];
        }

        $attendanceData[] = [
            'class_id' => $class->id,
            'class_name' =>$lang === 'en' ? $class->tenen : $class->ten,
            'attendance' => $classAttendance,
        ];

        return response()->json([
            'success' => 'Thành công',
            'data' => $attendanceData,
         ]);
    }

    //Lấy các lớp học theo t/g và thời điểm lúc đó
    public function getClassofDayForCheckin(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $user = JWTAuth::user();
        
        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $validator = validator($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $date = Carbon::parse($request->date);
        $time = Carbon::parse($request->time);
        $dayOfWeek = $date->dayOfWeek+1;  // 0 (Sunday) to 6 (Saturday)
        //$dayOfWeek = $dayOfWeek === 0 ? 7 : $dayOfWeek; // Convert Sunday from 0 to 7
        $dayName = $this->getDayName($date->dayOfWeek);

        $classes = Classes::where('id_atg_members', $user->id)->get();

        $classesInfo = [];

        foreach ($classes as $class) {
            $thoigianInfo = $this->parseThoigian($class->thoigian);
            
            if (!$thoigianInfo) {
                continue;
            }

            if (in_array($dayOfWeek, $thoigianInfo['days'])) {
                $classStartTime = Carbon::parse($thoigianInfo['startTime']);
                $classEndTime = Carbon::parse($thoigianInfo['endTime']);

                if ($time->between($classStartTime, $classEndTime)) {
                    $members = DB::table('register_class')
                        ->join('table_atg_members', 'register_class.id_atg_members', '=', 'table_atg_members.id')
                        ->where('register_class.id_class', $class->id)
                        ->select('table_atg_members.id', 'table_atg_members.ten')
                        ->get();

                    $classesInfo[] = [
                        'id_class' => $class->id,
                        'ten_lop' =>$lang ==='en' ? $class->tenen : $class->ten,
                        'thoigian' =>$lang ==='en'? $class->thoigianen : $class->thoigian,
                        'thu' => $dayName,
                        //'danh_sach_hoc_vien' => $members
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $classesInfo
        ]);
    }

    public function parseThoigian($thoigian)
    {
        $pattern = '/^((?:Thứ [\d-]+|CN)(?:-(?:Thứ [\d-]+|CN))*): (\d{1,2})h?(\d{2})?-(\d{1,2})h?(\d{2})?$/';
        if (!preg_match($pattern, $thoigian, $matches)) {
            return null; // Không khớp với định dạng
        }
        
        $dayString = $matches[1];
        $dayNumbers = [];
        $days = [];
        
        $dayParts = explode('-', $dayString);
        foreach ($dayParts as $part) {
            $part = trim($part);
            if ($part === 'CN') {
                $dayNumbers[] = 7; // Sử dụng 7 cho Chủ Nhật
                $days[] = 'Chủ nhật';
            } elseif (preg_match('/Thứ (\d+)/', $part, $dayMatch)) {
                $nums = explode('-', $dayMatch[1]);
                foreach ($nums as $num) {
                    $num = intval($num);
                    if ($num >= 2 && $num <= 7) {
                        $dayNumbers[] = $num - 1; // Điều chỉnh để phù hợp với getDayName
                        $days[] = $this->getDayName($num - 1);
                    }
                }
            }
        }
        
        $startHour = intval($matches[2]);
        $startMinute = isset($matches[3]) ? intval($matches[3]) : 0;
        $endHour = intval($matches[4]);
        $endMinute = isset($matches[5]) ? intval($matches[5]) : 0;
        
        // Kiểm tra tính hợp lệ của giờ và phút
        if ($startHour > 23 || $endHour > 23 || $startMinute > 59 || $endMinute > 59) {
            return null; // Giờ hoặc phút không hợp lệ
        }
        
        $startTime = sprintf('%02d:%02d', $startHour, $startMinute);
        $endTime = sprintf('%02d:%02d', $endHour, $endMinute);
        
        return [
            'days' => array_unique($days),
            'dayNumbers' => array_unique($dayNumbers),
            'startTime' => $startTime,
            'endTime' => $endTime
        ];
    }
    
    private function getDayName($dayOfWeek)
    {
        $days = [
            1 => 'Thứ 2',
            2 => 'Thứ 3',
            3 => 'Thứ 4',
            4 => 'Thứ 5',
            5 => 'Thứ 6',
            6 => 'Thứ 7',
            7 => 'Chủ nhật',
        ];

        return $days[$dayOfWeek] ?? '';
    }

    public function teacherViewCheckinByName(Request $request)
    {
        $user = JWTAuth::user();

        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $validator = validator($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'member_name' => 'required|string', 
        ], [
            'start_date.required' => 'Ngày bắt đầu không được để trống.',
            'end_date.required' => 'Ngày kết thúc không được để trống.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Lấy lớp trong CLB mà giáo viên này phụ trách
        $class = Classes::where('id', $request->id_class)
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Bạn không quản lý lớp này']);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        
        $query = Checkin::where('id_class', $class->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['member:id,ten'])
            ->orderBy('date', 'asc');

        
        if ($request->has('member_name') && !empty($request->member_name)) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('ten', 'like', '%' . $request->member_name . '%');
            });
        }

        $checkins = $query->get();

        $classAttendance = [];
        foreach ($checkins as $checkin) {
            $checkDate = Carbon::parse($checkin->date);
            $dayOfWeek = $checkDate->dayOfWeekIso;
            $dayName = $this->getDayName($dayOfWeek);

            $classAttendance[$checkin->date][] = [
                'member_id' => $checkin->member->id,
                'member_name' => $checkin->member->ten,
                'in' => $checkin->in,
                'out' => $checkin->out,
                'day_of_week' => $dayName,
            ];
        }

        $attendanceData[] = [
            'class_id' => $class->id,
            'class_name' => $class->ten,
            'attendance' => $classAttendance,
        ];

        return response()->json([
            'success' => 'Thành công',
            'data' => $attendanceData,
        ]);
    }

    public function teacherViewCheckinById(Request $request)
    {
        $user = JWTAuth::user();

        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $validator = validator($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'member_id' => 'required|integer|exists:table_atg_members,id', 
        ], [
            'start_date.required' => 'Ngày bắt đầu không được để trống.',
            'end_date.required' => 'Ngày kết thúc không được để trống.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Lấy lớp trong CLB mà giáo viên này phụ trách
        $class = Classes::where('id', $request->id_class)
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Bạn không quản lý lớp này']);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        
        $query = Checkin::where('id_class', $class->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['member:id,ten'])
            ->orderBy('date', 'asc');

        
        if ($request->has('member_id') && !empty($request->member_id)) {
            $query->where('ma_nv', $request->member_id); 
        }

        $checkins = $query->get();

        $classAttendance = [];
        foreach ($checkins as $checkin) {
            $checkDate = Carbon::parse($checkin->date);
            $dayOfWeek = $checkDate->dayOfWeekIso;
            $dayName = $this->getDayName($dayOfWeek);

            $classAttendance[$checkin->date][] = [
                'member_id' => $checkin->member->id,
                'member_name' => $checkin->member->ten,
                'in' => $checkin->in,
                'out' => $checkin->out,
                'day_of_week' => $dayName,
            ];
        }

        $attendanceData[] = [
            'class_id' => $class->id,
            'class_name' => $class->ten,
            'attendance' => $classAttendance,
        ];

        return response()->json([
            'success' => 'Thành công',
            'data' => $attendanceData,
        ]);
    }

    public function teacherViewCheckinByPhone(Request $request)
    {
        $user = JWTAuth::user();

        if ($user->hlv === 0) {
            return response()->json(['error' => 'Chỉ giảng viên mới có quyền xem thông tin này'], 403);
        }

        $validator = validator($request->all(), [
            'id_class' => 'required|integer|exists:table_class,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'member_phone' => 'required|string', 
        ], [
            'start_date.required' => 'Ngày bắt đầu không được để trống.',
            'end_date.required' => 'Ngày kết thúc không được để trống.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Lấy lớp trong CLB mà giáo viên này phụ trách
        $class = Classes::where('id', $request->id_class)
            ->where('id_atg_members', $user->id)
            ->first();

        if (!$class) {
            return response()->json(['error' => 'Bạn không quản lý lớp này']);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

       
        $query = Checkin::where('id_class', $class->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['member:id,ten,dienthoai']) 
            ->orderBy('date', 'asc');

       
        if ($request->has('member_phone') && !empty($request->member_phone)) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('dienthoai', 'like', '%' . $request->member_phone . '%');
            });
        }

        $checkins = $query->get();

        $classAttendance = [];
        foreach ($checkins as $checkin) {
            $checkDate = Carbon::parse($checkin->date);
            $dayOfWeek = $checkDate->dayOfWeekIso;
            $dayName = $this->getDayName($dayOfWeek);

            $classAttendance[$checkin->date][] = [
                'member_id' => $checkin->member->id,
                'member_name' => $checkin->member->ten,
                'member_phone' => $checkin->member->dienthoai, 
                'in' => $checkin->in,
                'out' => $checkin->out,
                'day_of_week' => $dayName,
            ];
        }

        $attendanceData[] = [
            'class_id' => $class->id,
            'class_name' => $class->ten,
            'attendance' => $classAttendance,
        ];

        return response()->json([
            'success' => 'Thành công',
            'data' => $attendanceData,
        ]);
    }

    public function searchClassesOfTeacher(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'keyword' => 'required|string|max:255',
            'lang' => 'sometimes|in:vi,en',
        ]);
        if($validator->fails()){
            return response()->json(['error'=> $validator->errors()],400);
        }

        $keyword = $request->input('keyword');
        $lang = $request->input('lang', 'vi');
        $user = JWTAuth::user();
        
        $classesOfTeacher = Classes::with(['club', 'coach'])
                                ->where('id_atg_members', $user->id)
                                ->where(function($query) use ($keyword, $lang){
                                    $query->where('ten', 'LIKE', "%$keyword%")
                                        ->orwhere('tenen', 'LIKE', "%$keyword%");
                                })
                                ->get();
        $classesInfo = $classesOfTeacher->map(function($class) use($lang){
            return [
                'id' => $class->id,
                'id_club' => $class->id_club,
                'ten_class' => $lang==='en' ? $class->tenen : $class->ten,
                'thoigian' => $lang === 'en' ? $class->thoigianen : $class->thoigian,
                'giatien' => $class->gia,
                'diachi' => $lang === 'en' ? $class->diachien : $class->diachi,
                'dienthoai' => $class->dienthoai,
                'ten_club' => $lang === 'en' ? ($class->cluben ?? 'No information') : ($class->ten_club ?? 'Không có thông tin'),
                'ten_hlv' => $lang === 'en' ? ($class->hlven ?? 'No information') : ($class->tenhlv ?? 'Không có thông tin'),
            ];
        });

        return response()->json($classesInfo);
    }

   
}
