<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBeltPaymentRequest;

use App\Models\Order;
use App\Models\Cart;//lấy model database

use App\Models\Product;
use App\Models\Classes;
use App\Models\class_payment;
use App\Models\detail_order;

use App\Models\dangkydai;   

use App\Models\table_atg_members;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

use Tymon\JWTAuth\Facades\JWTAuth;

use DateTime;

use Carbon\Carbon;

use App\Models\EducationGrades;
use App\Models\KetQuaThi;
use App\Models\News;
use Illuminate\Support\Facades\Validator;
use Nette\Utils\Random;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function Laravel\Prompts\table;

class BeltPaymentController extends Controller
{
                public function create_dai(StoreBeltPaymentRequest $request)
    {
        
        
        $token = $request->input('token');//phần cache
        $data = Cache::get($token);


        $id_dangkydai_cache = $data['id_dangkydai'];
        

        $id_dai = $request->input('id_dai');
        $member_id = $request->input('member_id');


        $id_dangkydai = dangkydai::where('id',$id_dangkydai_cache)->first();

        if(!$id_dangkydai){
            return response()->json(['error','dữ liệu không hợp lệ']);
        }

        $token = Str::random(32);
        $data = [
            'id_dai' => $id_dai,
            'member_id' => $member_id,
            'id_dangkydai' => $id_dangkydai->id,
        ];
        
        Cache::put($token, $data, 600);

        $vnp_TxnRef = rand(1,100000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $request->input('amount'); // Số tiền thanh toán
        $vnp_Locale = $request->input('language'); //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = $request->input('bankCode'); //Mã phương thức thanh toán
        $vnp_IpAddr = $request->ip(); //IP Khách hàng thanh toán
        
        
        
        $vnp_Url="https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";//-----
    
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => "U8PAFQAE",
    "vnp_Amount" => $vnp_Amount* 100,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toan GD:" . $vnp_TxnRef,
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => route('vnpay.returndai', ['token' => $token]),//--trả về trang thông tin thanh toán vnpay_return
    "vnp_TxnRef" => $vnp_TxnRef,
    
);


if (isset($vnp_BankCode) && $vnp_BankCode != "") {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
$vnp_HashSecret = "LEPLLPTUOC472W4HBXI2ZIL9T3R1M6S4";
if (isset($vnp_HashSecret)) {
    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);//  
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}


header('Location: ' . $vnp_Url);
die();
}

public function vnpayreturn_dai(Request $request)
{
    $data = $request->all();
    $token = $request->query('token');//phần cache
    $data = Cache::get($token);
    

    if (!$data) { // **Kiểm tra nếu không tìm thấy ID**
        return response()->json([
            'error' => 'Cache hết hạn'
        ], 400);
    }

    $id_dangkydai = $data['id_dangkydai'];
    
    $member_id = $data['member_id'];

    $id_dai = $data['id_dai'];

    $vnp_SecureHash = $_GET['vnp_SecureHash'];
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        unset($inputData['vnp_SecureHash']);

    
    ksort($inputData);
    $hashData = '';
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }

    $secureHash = hash_hmac('sha512', $hashData, "LEPLLPTUOC472W4HBXI2ZIL9T3R1M6S4");


    if ($secureHash == $vnp_SecureHash) {
        $status = $request->get('vnp_ResponseCode') == '00' ? 'thành công' : 'thất bại';

        $order = dangkydai::where('id', $id_dangkydai)->update([
            'id_dai' => $id_dai,
            'id_atg_members' => $member_id,
            'chi_phi' => $request->get('vnp_Amount') / 100,
            'ngay_tao' => $request->get('vnp_PayDate'),
            'trang_thai_thanh_toan' => $status,
        ]);

        // $txn_ref = $request->get('vnp_TxnRef');

        // $existingOrder = class_payment::where('txn_ref', $txn_ref)->first();
        // if ($existingOrder) {
        //     // Giá trị txn_ref đã tồn tại, hiển thị lỗi
        //     return response()->json([
        //         'error' => 'undefined vnp_TxnRef',
        //     ], 409); // 409 Conflict
        // }

        // $order = class_payment::create([
        //     'name_member' => $name_member,
        //     'class_id' => $class_id,
        //     'member_id' => $member_id,
        //     'hocphi' => $request->get('vnp_Amount')/100,
        //     'create_at' => $request->get('vnp_PayDate'),
        //     'status' => $status,
        // ]);
        
        // if ($status === 'thành công') {
        //     class_payment::where('member_id', $member_id)
        //                  ->where('status', 'chua thanh toan')
        //                  ->delete();
        // }

        $order = dangkydai::find($id_dangkydai);

        // Kiểm tra nếu tìm thấy bản ghi
        if ($order) {
            return response()->json(['success' => 'Đăng ký thành công']);
            // Trả về link hoặc các thao tác tiếp theo
            
        } else {
            // Xử lý nếu không tìm thấy bản ghi
            return response()->json(['error' => 'Order not found'], 404);
        }
        
        // // Trả về JSON response
        // return $link;
    } else {
        return response()->json([
            'status' => 'Chữ ký không hợp lệ'
        ]);
    }
}

                public function showcart_dai(Request $request)
                {
                    
                    // Lấy giá trị id từ body của request với key là id
                    $id_dangkydai = $request->input('id');
                
                    $member_id = dangkydai::where('id',$id_dangkydai);

                    // Tra cứu đơn hàng theo id
                    $order = dangkydai::where('id',$id_dangkydai)->first();
                    if (!$order) {
                        return response()->json(['message' => 'Order not found'], 404);
                    }


                    $token = Str::random(32);
                    $data = [
                        'id_dangkydai' => $id_dangkydai,
                        
                    ];
                    
                    Cache::put($token, $data, 600);

                // Giả sử `$txn_ref` là một thuộc tính của đơn hàng
                    $price = $order->chi_phi;
                    $id_dai = $order->id_dai;
                    $member_id = $order->id_atg_members;

                    // $class = Classes::where('id', $id_class)->first();
                    // $name_class = $class ? $class->ten : 'Lớp không xác định';

                    
                    
                    // Trả về chuỗi JSON của đơn hàng
                    return view('vnpay_dai.vnpay_paydai', [
                        'giatien' => $price,
                        'id_dai' => $id_dai,
                        'member_id' => $member_id,
                        'token' => $token,
                    ]) ;
                }

                public function status_lendai(Request $request)
                {
                    
                    $id_dangkydai = $request->input('id');
                    $orders = dangkydai::where('id', $id_dangkydai)->first(); // Sử dụng get() để lấy tất cả các hóa đơn
                    
                    if (!$orders) {
                        // Trả về lỗi với mã trạng thái 404 và thông báo lỗi
                        return response()->json(['error' => 'Order not found'], 404);
                    }
                    
                    return response()->json([
                        'status' => $orders->trang_thai_thanh_toan,
                        'member_id' => $orders->id_atg_members,
                    ], 200);
                }

        public function qrPayment_dai(Request $request)
        {
        //     // Xác thực người dùng bằng JWT
        // $user = JWTAuth::parseToken()->authenticate();
        
        // if (!$user) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        // // Validate các trường bắt buộc
        // $validator = Validator::make($request->all(), [
        //     'id_dai' => 'required|exists:table_educationgrades,id',
        //     'chi_phi' => 'required|numeric|min:0',
        // ]);
        
        // if ($validator->fails()) {
        //     return response()->json(['error' => $validator->errors()], 400);
        // }

        // // Lấy thông tin thành viên (hiện tại là người dùng đã xác thực) và đai đã đăng ký
        // $member = table_atg_members::findOrFail($user->id); // Sử dụng ID của người dùng đã xác thực
        // $daiDangKy = EducationGrades::findOrFail($request->id_dai);
        // $capDoHienTai = $member->educationGrade;
        
        // // Kiểm tra điều kiện đăng ký đai (giống như trước)
        // if (!$capDoHienTai || $daiDangKy->order <= $capDoHienTai->order) {
        //     return response()->json(['error' => 'Cấp đai đăng ký không hợp lệ.'], 400);
        // }

        // if ($daiDangKy->order - $capDoHienTai->order >= 2) {
        //     return response()->json(['error' => 'Chỉ được đăng ký lên đai cao hơn 1 bậc.'], 400);
        // }

        // // Tạo bản ghi đăng ký đai mới
        // $ngayThi = Carbon::now()->addMonth(); 
        // $order = dangkydai::create([
        //     'id_atg_members' => $member->id,  // Sử dụng ID của người dùng đã xác thực
        //     'id_dai' => $request->id_dai,
        //     'chi_phi' => $request->chi_phi,
        //     'ngay_tao' => now(),
        //     'ngay_thi' => $ngayThi,
        //     'trang_thai' => 'chưa thi',
        //     'trang_thai_thanh_toan' => 'chưa thanh toán', 
        // ]);
        
        // $id_dangkydai = $order->id;
        
        // $amount = dangkydai::where('id', $id_dangkydai)->first()->chi_phi;
                
        // $sdt = '0937759311';
        // $comment = 'TIEN DANG KY LEN DAI';
        
        $id_news = $request->input('id');

        $id_main = KetQuaThi::where('id',$id_news)->first();

        $member_id = $id_main->id_member;
        $member_name = table_atg_members::where('id',$member_id)->first()->ten;

        $khoa_thi = $id_main->id_exam;
            
        $hoc_phi = News::where('id',$khoa_thi)->first()->lephithi; //Khoa thi
        

        $qrUrl = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=" . $hoc_phi . "&addInfo= " . " _ ". $member_name . ' _ LPT Khoa '. $khoa_thi;
        return $qrUrl;       

        }

}
