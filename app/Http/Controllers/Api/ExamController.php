<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Return_;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExamController extends Controller
{
    public function getAllExam(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $examSessions = News::where('type', 'khoa-thi')
                            ->with(['club', 'district'])
                            ->orderBy('start', 'desc')
                            ->get()
                            ->map(function ($session) use ($lang) {
                                return [
                                    'id' => $session->id,
                                    'ten' => $lang === 'en' ? $session->tenen : $session->tenvi,
                                    'mota' => $lang === 'en' ? $session->motaen : $session->motavi,
                                    'noidung' => $lang === 'en' ? $session->noidungen : $session->noidungvi,
                                    'thoigian' => $session->thoigian ? $session->thoigian : null,
                                    'start' => $session->start ? ($lang === 'en' ? $session->start->format('Y-m-d H:i:s') : $session->start->format('d-m-Y H:i:s')) : null,
                                    'end' => $session->end ? ($lang === 'en' ? $session->end->format('Y-m-d H:i:s') : $session->end->format('d-m-Y H:i:s')) : null,
                                    'event_location' => $session->event_location,
                                    'event_lecturer' => $session->event_lecturer,
                                    'lephithi' => $session->lephithi,
                                    'club' => $session->club ? ($lang === 'en' ? $session->club->tenen : $session->club->ten) : null,
                                    'district' => $session->district ? ($lang === 'en' ? $session->district->tenkhongdau : $session->district->ten) : null,
                                ];
                            });

        return response()->json($examSessions);
    }

    public function createExam(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Chỉ có Huấn luyện viên của câu lạc bộ mới được thực hiện'], 403);
        }
        $clubInfo = Club::where('id', $user->id_club)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'tenvi' => 'required|string|max:255',
            'tenen' => 'required|string|max:255',
            'motavi' => 'required|string',
            'motaen' => 'required|string',
            'noidungvi' => 'required|string',
            'noidungen' => 'required|string',
            'thoigian' => 'required|string',
            'start' => 'required|date_format:Y-m-d H:i:s|after:today',
            'end' => 'required|date_format:Y-m-d H:i:s|after:today',
            'event_location' => 'required|string|max:255',
            'event_lecturer' => 'required|string|max:255',
            'lephithi' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $examSession = News::create(array_merge($validator->validated(), [
            'type' => 'khoa-thi',
            'hienthi' => 1,
            'hlv' => 1,
            'hocvien' => 1,
            'ngaytao' => time(),
            'id_club' => $clubInfo->id,
            'id_district' => $clubInfo->id_district,
        ]));

        return response()->json(['success' => 'Exam session created successfully', 'data' => $examSession], 201);
    }

    public function getExam(Request $request)
    {
        $lang = $request->query('lang', 'vi');
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:table_news,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $examSession = News::where('id', $request->id)
                            ->where('type', 'khoa-thi')
                            ->with(['club', 'district'])
                            ->first();
        if (!$examSession) {
            return response()->json(['error' => 'Exam not found'], 404);
        }
        $formattedExamSession = [
            'id' => $examSession->id,
            'ten' => $lang === 'en' ? $examSession->tenen : $examSession->tenvi,
            'mota' => $lang === 'en' ? $examSession->motaen : $examSession->motavi,
            'noidung' => $lang === 'en' ? $examSession->noidungen : $examSession->noidungvi,
            'thoigian' => $examSession->thoigian ? $examSession->thoigian : null,
            'start' => $examSession->start ? ($lang === 'en' ? $examSession->start->format('Y-m-d H:i:s') : $examSession->start->format('d-m-Y H:i:s')) : null,
            'end' => $examSession->end ? ($lang === 'en' ? $examSession->end->format('Y-m-d H:i:s') : $examSession->end->format('d-m-Y H:i:s')) : null,
            'event_location' => $examSession->event_location,
            'event_lecturer' => $examSession->event_lecturer,
            'lephithi' => $examSession->lephithi,
            'club' => $examSession->club ? ($lang === 'en' ? $examSession->club->tenen : $examSession->club->ten) : null,
            'district' => $examSession->district ? ($lang === 'en' ? $examSession->district->tenkhongdau : $examSession->district->ten) : null,   
                                ];
        
        return response()->json($formattedExamSession);
    }

    public function updateExam(Request $request)
    {
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Chỉ có Huấn luyện viên của câu lạc bộ mới được thực hiện'], 403);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:table_news,id',
            'tenvi' => 'sometimes|required|string',
            'tenen' => 'sometimes|required|string',
            'motavi' => 'sometimes|required|string',
            'motaen' => 'sometimes|required|string',
            'noidungvi' => 'sometimes|required|string',
            'noidungen' => 'sometimes|required|string',
            'thoigian' => 'sometimes|required|string',
            'start' => 'sometimes|required|date_format:Y-m-d H:i:s|after:today',
            'end' => 'sometimes|required|date_format:Y-m-d H:i:s|after:today',
            'event_location' => 'sometimes|required|string|max:255',
            'event_lecturer' => 'sometimes|required|string|max:255',
            'lephithi' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $examSession = News::where('type', 'khoa-thi')->findOrFail($request->id);
         // Check if the user has permission to update this exam session
         if ($examSession->id_club !== $user->id_club) {
            return response()->json(['error' => 'Bạn không có quyền cập nhật khóa thi này'], 403);
        }

        $examSession->update(array_merge($validator->validated(), [
            'ngaysua' => time(),
        ]));

        return response()->json(['success' => 'updated successfully', 'data' => $examSession]);
    }

    public function deleteExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:table_news,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = JWTAuth::user();
        if (!$user || $user->hlv !== 1) {
            return response()->json(['error' => 'Chỉ có Huấn luyện viên của câu lạc bộ mới được thực hiện'], 403);
        }

        $examSession = News::where('type', 'khoa-thi')->findOrFail($request->id);
        $club = Club::findOrFail($user->id_club);
        try{
            if($club->id === $examSession->id_club){
                $examSession->delete();
            }
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()], 500);
        }
        
        return response()->json(['success' => 'deleted successfully']);
    }
}