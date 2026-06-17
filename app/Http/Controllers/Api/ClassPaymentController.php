<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreClassPaymentRequest;

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

class ClassPaymentController extends Controller
{
                public function createCLB(StoreClassPaymentRequest $request)
    {
        
        $class_id = $request->input('class_id');
        $member_id = $request->input('member_id');
        $name_member = $request->input('name_member');
        $id_classpayment = $request->input('id_classpayment');

        $token = Str::random(32);
        $data = [
            'id_classpayment' => $id_classpayment,
            'member_id' => $member_id,
            'class_id' => $class_id,
            'name_member' => $name_member,
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
    "vnp_ReturnUrl" => route('vnpay.returnCLB', ['token' => $token]),//--trả về trang thông tin thanh toán vnpay_return
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

public function vnpayreturnCLB(Request $request)
{
    $data = $request->all();
    $token = $request->query('token');//phần cache
    $data = Cache::get($token);
    

    if (!$data) { // **Kiểm tra nếu không tìm thấy ID**
        return response()->json([
            'error' => 'Cache hết hạn'
        ], 400);
    }

    $id_classpayments = $data['id_classpayment'];
    $class_id = $data['class_id'];
    $name_member = $data['name_member'];//phần cache
    $member_id = $data['member_id'];

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

        $order = class_payment::where('id', $id_classpayments)->update([
            'name_member' => $name_member,
            'id_class' => $class_id,
            'member_id' => $member_id,
            'hocphi' => $request->get('vnp_Amount') / 100,
            'created_at' => $request->get('vnp_PayDate'),
            'status' => $status,
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

        $order = class_payment::find($id_classpayments);

        // Kiểm tra nếu tìm thấy bản ghi
        if ($order) {
            $link = 'https://app.giasuthethao.com/api/classes/joinclass?id=' . $order->id . '&member_id=' . $order->member_id;
            // Trả về link hoặc các thao tác tiếp theo
            return $link;
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

                public function showcartCLB(Request $request)
                {
                    
                    // Lấy giá trị id từ body của request với key là id
                    $id_classpayment = $request->input('id');
                    $member_id = $request->input('member_id');

                    // Tra cứu đơn hàng theo id
                    $order = class_payment::where('id',$id_classpayment)->first();
                    if (!$order) {
                        return response()->json(['message' => 'Order not found'], 404);
                    }

                // Giả sử `$txn_ref` là một thuộc tính của đơn hàng
                    $price = $order->hocphi;
                    $id_class = $order->id_class;

                    $class = Classes::where('id', $id_class)->first();
                    $name_class = $class ? $class->ten : 'Lớp không xác định';

                    $id_member = $order->member_id;

                    $member = table_atg_members::where('id', $id_member)->first();
                    $name_member = $member ? $member->ten : 'Unknown Member';
                    
                    
                    // Trả về chuỗi JSON của đơn hàng
                    return view('vnpay_CLB.vnpay_payCLB', [
                        'giatien' => $price,
                        'class_id' => $id_class,
                        'member_id' => $member_id,
                        'name_member' => $name_member,
                        'name_class' => $name_class,
                        'id_classpayment' => $id_classpayment,
                    ]) ;
                }

                public function status_payingclass(Request $request)
                {
                    
                    $id_classpayment = $request->input('id');
                    $orders = class_payment::where('id', $id_classpayment)->first(); // Sử dụng get() để lấy tất cả các hóa đơn
                    
                    if (!$orders) {
                        // Trả về lỗi với mã trạng thái 404 và thông báo lỗi
                        return response()->json(['error' => 'Order not found'], 404);
                    }
                    
                    return response()->json([
                        'status' => $orders->status,
                        'member_id' => $orders->member_id
                    ], 200);
                }

        public function qrPaymentCLB(Request $request)
        {

                //-----------------> check đã tham gia CLB chưa
                $table_atg_member = JWTAuth::user();
                $member_id = $table_atg_member->id;

                $id_class = $request->input('id_class');

                $amount = $request->input('amount');
                // $id_club = $request->query('id_club');

                $create_at = date('YmdHis');

                // Tra cứu đơn hàng mới nhất theo member_id
                // $order = Classes::where('id', $id_class)
                //             ->orderBy('id', 'desc')  // Sắp xếp theo cột 'id' giảm dần
                //             ->first();  // Lấy bản ghi đầu tiên (mới nhất)

                $end_at = date('YmdHis');
                $date = DateTime::createFromFormat('YmdHis', $end_at);


                if($amount<=1500000){
                    $date->modify('+3 months');
                }
                elseif($amount<=2000000){
                    $date->modify('+6 months');
                }
                else{
                    $date->modify('+12 months');
                }

                $status = 'chua thanh toan';

                $name_member = table_atg_members::where('id',$member_id)->first();
                if (!$name_member) {
                    return response()->json(['message' => 'id học viên không tồn tại'], 404);
                }
                $name_member_full = $name_member ? $name_member->ten : 'Lớp không xác định';


                $class_payment = class_payment::create([
                    'id_class' => $id_class,
                    'member_id' => $member_id,
                    'created_at' => $create_at,
                    'hocphi' => $amount,
                    'status' => $status,
                    'name_member' => $name_member_full,
                    'end_date' => $date,
                ]);
                $id_classpayment = $class_payment->id;
                $info = class_payment::where('id', $id_classpayment)->first();

                $amount = $info->hocphi;
                
                $sdt = '0937759311';
                $comment = 'DONG HOC PHI';

                $qrUrl = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=" . $amount . "&addInfo= " . " _ ". $sdt . ' _ '. $comment;
                return $qrUrl;
        }

                public function getlinkpayCLB(Request $request) //ĐĂNG KÝ LỚP HỌC đề status chưa thanh toán
                {
                    // $user = JWTAuth::user();
//-----------------> check đã tham gia CLB chưa
                    $table_atg_member = JWTAuth::user();
                    $member_id = $table_atg_member->id;

                    $id_class = $request->input('id_class');

                    $amount = $request->input('amount');
                    // $id_club = $request->query('id_club');

                    $create_at = date('YmdHis');
                    
                    // Tra cứu đơn hàng mới nhất theo member_id
                    // $order = Classes::where('id', $id_class)
                    //             ->orderBy('id', 'desc')  // Sắp xếp theo cột 'id' giảm dần
                    //             ->first();  // Lấy bản ghi đầu tiên (mới nhất)
                    
                    $end_at = date('YmdHis');
                    $date = DateTime::createFromFormat('YmdHis', $end_at);

                    
                    if($amount<=1500000){
                        $date->modify('+3 months');
                    }
                    elseif($amount<=2000000){
                        $date->modify('+6 months');
                    }
                    else{
                        $date->modify('+12 months');
                    }

                    $status = 'chua thanh toan';

                    $name_member = table_atg_members::where('id',$member_id)->first();
                    if (!$name_member) {
                        return response()->json(['message' => 'id học viên không tồn tại'], 404);
                    }
                    $name_member_full = $name_member ? $name_member->ten : 'Lớp không xác định';


                    $class_payment = class_payment::create([
                        'id_class' => $id_class,
                        'member_id' => $member_id,
                        'created_at' => $create_at,
                        'hocphi' => $amount,
                        'status' => $status,
                        'name_member' => $name_member_full,
                        'end_date' => $date,
                    ]);
                    $id_classpayment = $class_payment->id;


                    // $token_first = Str::random(32);
                    // $datas = [
                    //     'id_classpayment' => $id_classpayment,
                    // ];
                    
                    // Cache::put($token_first, $datas, 600);
                    
                    $link = 'https://app.giasuthethao.com/pay_clb/show?id=' . $id_classpayment . '&member_id=' . $member_id ;
                    return $link;


                    // if ($order) {
                    //     $link = 'https://vovinammoi-4bedb6dd1c05.herokuapp.com/pay_clb/show?id_club=' . $order->id . "&member_id=" . $member_id;
                    //     return $link;
                    // } else {
                    //     return response()->json(['error' => 'Bạn chưa tham gia câu lạc bộ'], 404);
                    // }
                }

}
