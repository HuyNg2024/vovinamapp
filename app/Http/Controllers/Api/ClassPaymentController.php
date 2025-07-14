<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\class_payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator; // Thêm vào để hỗ trợ validation
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Models\Classes; 
class ClassPaymentController extends Controller
{
    /**
     * Lấy danh sách các khoản thanh toán học phí.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $classPayments = class_payment::all(); // Lấy tất cả các bản ghi

        return response()->json($classPayments);

        // return response()->json([
        //     'success' => true,
        //     'data' => $classPayments,
        // ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'id_giaovien' => 'required|integer',
            'member_id' => 'required|integer',
            'hocphi' => 'required|numeric',
            'id_class' => 'required|integer',
            'status' => 'required|string',
            'created_at' => 'sometimes|required|date', 
        ]);

        
        $classPaymentData = $request->all();
        $classPaymentData['created_at'] = $request->input('created_at', Carbon::now());

        $classPayment = class_payment::create($classPaymentData);

        return response()->json($classPayment, 201);
    }

  

    public function show($id)
    {
        
        $classPayment = class_payment::find($id);

        if (is_null($classPayment)) {
            return response()->json(['message' => 'Không tìm thấy lớp payment'], 404);
        }

        return response()->json($classPayment);
    }

    


    public function update(Request $request, $id)
    {
        
        $request->validate([
            'id_giaovien' => 'sometimes|required|integer',
            'member_id' => 'sometimes|required|integer',
            'hocphi' => 'sometimes|required|numeric',
            'id_class' => 'sometimes|required|integer',
            'status' => 'sometimes|required|string',
            'created_at' => 'sometimes|required|date',
        ]);

        
        $classPayment = class_payment::find($id);

        if (is_null($classPayment)) {
            return response()->json(['message' => 'Không tìm thấy lớp thanh toán'], 404);
        }

        
        $classPayment->update($request->all());

        return response()->json($classPayment);
    }

    
   
    public function destroy($id)
    {
        
        $classPayment = class_payment::find($id);

        if (is_null($classPayment)) {
            return response()->json(['message' => 'Không tìm thấy lớp thanh toán'], 404);
        }

        
        $classPayment->delete();

        return response()->json(['message' => 'Đã xóa lớp thanh toán thành công']);
    }

    public function getPaymentHistory(Request $request)
    {
        
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401); 
        }

        
        $paymentHistory = DB::table('class_payment')
            ->join('table_class', 'class_payment.id_class', '=', 'table_class.id')
            ->select('class_payment.*', 'table_class.ten as ten_lop')
            ->where('class_payment.member_id', $user->id) 
            ->get();

        if ($paymentHistory->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy lịch sử thanh toán cho người dùng này.',
            ], 404);
        } else {
            return response()->json($paymentHistory);
        }
    }

    public function getTotalPayment(Request $request)
    {
        
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        
        $totalPayment = class_payment::where('member_id', $user->id)->sum('hocphi');

        return response()->json([
            'success' => true,
            'total_payment' => $totalPayment,
        ]);
    }

  


    public function getTotalPaymentOfMembers(Request $request)
    {
       
        $user = JWTAuth::user();

        if (!$user) { 
            return response()->json(['error' => 'Unauthorized'], 401);
        }

       
        $totalPayment = DB::table('class_payment')
            ->where('member_id', $user->id) 
            ->sum('hocphi');

        
        return response()->json(['total_payment' => $totalPayment]);
    }


    
}