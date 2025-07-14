<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

class PayController extends Controller
{



    public function create()
    {
        
        $member_id = $_POST['member_id'];
        $token = Str::random(32);
        $data = [
            'member_id' => $member_id,
            
        ];
        
        Cache::put($token, $data, 600);

        $vnp_TxnRef = rand(1,100000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $_POST['amount']; // Số tiền thanh toán
        $vnp_Locale = $_POST['language']; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = $_POST['bankCode']; //Mã phương thức thanh toán
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán
        
        
        
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
    "vnp_ReturnUrl" => route('vnpay.return', ['token' => $token]),//--trả về trang thông tin thanh toán vnpay_return
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

    

    
    


  


    // public function vnpayreturn(Request $request){
    //     return view('vnpay.vnpay_return');
    // }

    //trả về trang vnpay_return


//--------------Lưu hóa đơn vào database
    public function vnpayreturn(Request $request)
{
    $data = $request->all();
    $token = $request->query('token');//phần cache
    $data = Cache::get($token);

    
    if (!$data) { // **Kiểm tra nếu không tìm thấy ID**
        return response()->json([
            'error' => 'Cache hết hạn'
        ], 400);
    }

    
    $member_id = $data['member_id'];//phần cache
    $user = table_atg_members::where('id', $member_id)->first();
    $user_name = $user ? $user->ten : 'Unknown';
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

        $txn_ref = $request->get('vnp_TxnRef');
        $existingOrder = Order::where('txn_ref', $txn_ref)->first();
        if ($existingOrder) {
            // Giá trị txn_ref đã tồn tại, hiển thị lỗi
            return response()->json([
                'error' => 'undefined vnp_TxnRef',
            ], 409); // 409 Conflict
        }

        $amount = $request->get('vnp_Amount')/100;


        
        $qr_link = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=0" . "&addInfo= " . ' Ma GD'. $txn_ref . ' ID nguoi dung' . $member_id. ' trang thai'. $status ;
        // Tạo mã QR

        $order = Order::create([
            'member_id' => $member_id,
            'txn_ref' => $request->get('vnp_TxnRef'),
            'amount' => $request->get('vnp_Amount')/100,
            'order_info' => $request->get('vnp_OrderInfo'),
            'response_code' => $request->get('vnp_ResponseCode'),
            'transaction_no' => $request->get('vnp_TransactionNo'),
            'bank_code' => $request->get('vnp_BankCode'),
            'pay_date' => $request->get('vnp_PayDate'),
            'status' => $status,
            'ten' => $user_name,
            'giao_hang' => 'chờ xác nhận',
            'qr_link' => $qr_link
        ]);

        $cartItems = Cart::where('member_id', $member_id)->get();

        // Chuyển dữ liệu từ cart sang order_items
            foreach ($cartItems as $cartItem) {
                detail_order::create([
                    'id_order' => $order->id,
                    'id_product' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    // Thêm các trường khác nếu cần
                ]);
            }
            
            //Xóa dữ liệu cart sau khi thanh toán
        Cart::where('member_id', $member_id)->delete();

        
        // Trả về JSON response
        return view('vnpay.vnpay_return', [
            'member_id' => $member_id,
            'txn_ref' => $order->txn_ref,
            'user_name' => $user_name,
            'amount' => number_format($request->get('vnp_Amount')/100, 0, ',', '.') . ' VNĐ',
            'order_info' => $order->order_info,
            'response_code' => $order->response_code,
            'transaction_no' => $order->transaction_no,
            'bank_code' => $order->bank_code,
            'pay_date' => date('Y-m-d H:i:s', strtotime($order->pay_date)),
            'status' => $status
        ]);
    } else {
        return view('vnpay.vnpay_return', [
            'status' => 'Chữ ký không hợp lệ'
        ]);
    }
}

    


        // -------->Thanh toán giỏ hàng
            public function showcart(Request $request)
                {
                    
                    // Lấy giá trị id từ body của request với key là id
                    
                    $id = $request->input('id');

                    // Tra cứu đơn hàng theo id
                    $getid_cart = Cart::where('id', $id)->first();

                    if (is_null($getid_cart)) {
                        return 'Đơn hàng không tồn tại';
                    }

                    $member_id= $getid_cart->member_id;
                    
                    
                    $order = Cart::where('member_id',$member_id)->get();
                // Kiểm tra nếu đơn hàng không tồn tại
                if ($order->isEmpty()) {
                    return response()->json([   
                        'message' => 'Order not found',
                    ], 404);
                }
                
                // Giả sử `$txn_ref` là một thuộc tính của đơn hàng
                    $price = $order->sum('total_price');

                    $member_id = $order->first()->member_id;

                    // $id = $order->first('id');
                    
                    
                    // Trả về chuỗi JSON của đơn hàng
                    return view('vnpay.vnpay_pay', [
                        'giatien' => $price,
                        // 'order' => $order,
                        // 'id' => $id,
                        'member_id' => $member_id,
                    ]) ;
                }

                // ------------------Truy xuất hóa đơn theo member_id
                public function checkhoadon(Request $request)
                {
                    $user = JWTAuth::user();
                    $member_id = $user->id;
                    // $member_id = $request->query('id');
                    $orders = Order::where('member_id', $member_id)->get(); // Sử dụng get() để lấy tất cả các hóa đơn
                    
                    if ($orders->isEmpty()) {
                        // Trả về lỗi với mã trạng thái 404 và thông báo lỗi
                        return response()->json([], 200);

                    }
                    return response()->json($orders);
                }

                public function chitiet_hoadon(Request $request)
                {
                    // Lấy giá trị 'id_order' từ request
                    $id_order = $request->input('id_order');
                    
                    // Truy vấn dữ liệu từ bảng 'detail_cart' và eager load thông tin sản phẩm
                    $orderDetails = detail_order::where('id_order', $id_order)
                        ->with('product') // Eager load product
                        ->get();
                    
                    if ($orderDetails->isEmpty()) {
                        // Nếu không tìm thấy, trả về thông báo lỗi
                        return response()->json(['message' => 'Order not found'], 404);
                    }

                    // Chuyển đổi dữ liệu thành dạng mảng và thêm thông tin từ bảng products
                    $result = $orderDetails->map(function ($detail) {
                        return [
                            'id_order' => $detail->id_order,
                            'id_product' => $detail->id_product,
                            'quantity' => $detail->quantity,
                            'link_image' => $detail->product->link_image ?? null,
                            'CategoryName' => $detail->product->category->CategoryName ?? null,
                            'UnitPrice' => $detail->product->UnitPrice ?? null,
                            'ProductName' => $detail->product->ProductName ?? null,
                            'SupplierName' => $detail->product->SupplierName ?? null,
                        ];
                    });

                    // Trả về thông tin của đơn hàng với các trường bổ sung
                    return response()->json($result);
                }

                public function getlinkpay(Request $request) //PHẦN CÓ THỂ KHÔNG ĐỤNG TỚI
                {
                    $user = JWTAuth::user();
                    $member_id = $user->id;

                    // $member_id = $request->query('member_id');
                    
                    
                    // Tra cứu đơn hàng mới nhất theo member_id
                    $order = Cart::where('member_id', $member_id)
                                ->latest()  // Sắp xếp theo thời gian tạo mới nhất
                                ->first();  // Lấy bản ghi đầu tiên (mới nhất)
                    
                    
                    if ($order) {
                        $link = 'https://app.giasuthethao.com/orders/show?id=' . $order->id;
                        return $link;
                    } else {
                        return response()->json(['error' => 'Order not found'], 404);
                    }
                }




                //Thanh toán đăng ký lớp học---------===================================
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



                public function createCLB()
    {
        
        $class_id = $_POST['class_id'];
        $member_id = $_POST['member_id'];
        $name_member = $_POST['name_member'];
        $id_classpayment = $_POST['id_classpayment'];

        $token = Str::random(32);
        $data = [
            'id_classpayment' => $id_classpayment,
            'member_id' => $member_id,
            'class_id' => $class_id,
            'name_member' => $name_member,
        ];
        
        Cache::put($token, $data, 600);

        $vnp_TxnRef = rand(1,100000); //Mã giao dịch thanh toán tham chiếu của merchant
        $vnp_Amount = $_POST['amount']; // Số tiền thanh toán
        $vnp_Locale = $_POST['language']; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = $_POST['bankCode']; //Mã phương thức thanh toán
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán
        
        
        
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


//------------------Phần mã check database ko liên quan đến xử lý dữ liệu
                public function status_order(Request $request)
                {
                    
                    $id_order = $request->query('id');
                    $orders = Order::where('id', $id_order)->first(); // Sử dụng get() để lấy tất cả các hóa đơn
                    
                    if (!$orders) {
                        // Trả về lỗi với mã trạng thái 404 và thông báo lỗi
                        return response()->json(['error' => 'Order not found'], 404);
                    }
                    
                    return response()->json([
                        'status' => $orders->status,
                        'id_order' => $orders->id
                    ], 200);
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



                //========Thanh toán đăng ký lên đai---------===================================

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

//////////////////////////////////

                public function create_dai()
    {
        
        
        $token = $_POST['token'];//phần cache
        $data = Cache::get($token);


        $id_dangkydai_cache = $data['id_dangkydai'];
        

        $id_dai = $_POST['id_dai'];
        $member_id = $_POST['member_id'];


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
        $vnp_Amount = $_POST['amount']; // Số tiền thanh toán
        $vnp_Locale = $_POST['language']; //Ngôn ngữ chuyển hướng thanh toán
        $vnp_BankCode = $_POST['bankCode']; //Mã phương thức thanh toán
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; //IP Khách hàng thanh toán
        
        
        
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


//===========================Không liên quan tới dữ liệu=================
//------------------chech trạng thái đóng tiền đăng ký lên đai 

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


// ===============================================>Phần QR code====================
public function qrPayment(Request $request)
        {
            $member_id = JWTAuth::user()->id;
            $ma_donhang = rand(1,100000);
            $amount = Cart::where('member_id', $member_id)->sum('total_price');
            $sdt = '0937759311';
            $comment = 'THANH TOAN DON HANG';

            $qrUrl = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=" . $amount . "&addInfo= GD " . $ma_donhang ." _ ". $sdt . ' _ '. $comment;
            return $qrUrl;
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


        // ===============================================>Phần cập nhật trạng thái giao hàng====================



        public function delivery_update(Request $request)
        {
            // Lấy giá trị 'id' từ request
            $id_order_input = $request->input('id');
            
            // Tìm order theo id
            $order = Order::where('txn_ref', $id_order_input)->first();
            
            if ($order) {
                if ($order->giao_hang == 'đã giao hàng') {
                    return response()->json([
                        'message' => 'Đơn hàng đã được giao trước đó',
                    ]);
                } 
                else if ($order->giao_hang == 'đang giao hàng') {
                    $order->giao_hang = 'đã giao hàng';
                    $order->status = 'thành công';
                    $order->save();
                    
                    $qrUrl = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=0&addInfo= " . " _ Trạng thái: ". $order->status . ' _ Mã GD: '. $order->txn_ref . ' _ ID nguoi dung: ' . $order->member_id;
                    $order->qr_link = $qrUrl;
                    $order->save();
                    return response()->json([
                        'message' => 'Trạng thái giao hàng đã được cập nhật thành đã giao hàng',
                    ]);
                }
                else {
                    return response()->json([
                        'message' => 'Không thể cập nhật trạng thái giao hàng',
                    ]);
                }
            }
            
            // Trả về phản hồi nếu không tìm thấy đơn hàng
            return response()->json([
                'message' => 'Đơn hàng không tồn tại',
            ]);
        }

        public function search_order(Request $request)
        {
            // Lấy giá trị 'id' từ request
            $id_order_input = $request->input('id');
            
            // Tìm order theo id và cập nhật trạng thái giao hàng
            $order = Order::where('txn_ref', $id_order_input)->first();
            
            if ($order) {
                // Trả về thông tin order dưới dạng JSON
                return response()->json([
                    'id' => $order->id,
                    'giao_hang' => $order->giao_hang,
                    'status' => $order->status
                ]);
            } else {
                // Nếu không tìm thấy order, trả về phản hồi lỗi
                return response()->json([
                    'message' => 'Order not found'
                ], 404);
            }
        }


        //==============================>ĐẶT HÀNG NHƯNG TRẢ VỀ CHƯA THANH TOÁN========
        public function getlink_nopay(Request $request) 
                {
                    $user = JWTAuth::user();
                    $member_id = $user->id;

                    // $member_id = $request->query('member_id');
                    
                    
                    // Tra cứu đơn hàng mới nhất theo member_id
                    $order = Cart::where('member_id', $member_id)
                                ->latest()  // Sắp xếp theo thời gian tạo mới nhất
                                ->first();  // Lấy bản ghi đầu tiên (mới nhất)
                    
                    $id = $order->id ?? null;
                    

                    $getid_cart = Cart::where('id', $id)->first();
                    if (is_null($getid_cart)) {
                        return 'Đơn hàng không tồn tại';
                    }

                    $member_id= $getid_cart->member_id;
                    
                    $order = Cart::where('member_id',$member_id)->get();
                    
                    // Kiểm tra nếu đơn hàng không tồn tại

                    if ($order->isEmpty()) {
                        return response()->json([   
                            'message' => 'Order not found',
                        ], 404);
                    }
                    
                    // Giả sử `$txn_ref` là một thuộc tính của đơn hàng
                        $price = $order->sum('total_price');


                        $user= table_atg_members::where('id', $member_id)->first();
                        $user_name = $user->ten;
                        $txn_ref = rand(1,10000);
                        
                        $order = Order::create([
                            'member_id' => $member_id,
                            'giao_hang' => 'chờ xác nhận',
                            'amount' => $price,
                            'order_info' => 'Thanh toán khi nhận hàng',
                            'txn_ref' => $txn_ref,
                            'status' => 'chưa thanh toán',
                            'ten' => $user_name,
                        ]);
                        
                        // Thông tin hóa đơn
                        $invoiceInfo_orderid = "Mã đơn hàng: " . $txn_ref . "\n";
                        
                        $invoiceInfo = "Trạng thái: " . $order->status;
 
                        $amount = $order->amount;
                        $txn_ref = $invoiceInfo_orderid;


                        $sdt = '0937759311';


                        $qrUrl = "https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?amount=" . $amount . "&addInfo= " . " _ ". $sdt . ' _ '. $txn_ref . ' _ ' . $member_id. ' _ '.$invoiceInfo;
                        

                        
                        $order->update([
                            'qr_link' => $qrUrl,
                             // Thêm đường dẫn hình ảnh tải lên Firebase Storage vào cơ sở dữ liệu
                        ]);

                        //===============NGẮT======

                        $cartItems = Cart::where('member_id', $member_id)->get();
                        
                        
                        // Chuyển dữ liệu từ cart sang order_items
                            foreach ($cartItems as $cartItem) {
                                
                                detail_order::create([

                                    'id_order' => $order->id,
                                    'id_product' => $cartItem->product_id,
                                    'quantity' => $cartItem->quantity,
                                    // Thêm các trường khác nếu cần
                                ]);
                            }
                            
                        Cart::where('member_id', $member_id)->delete();

                        return response()->json([
                            'success' => 'đặt hàng thành công',
                            'mã đơn hàng' => $txn_ref,
                            "id_order" => $order->id,
                            "qr_link" => $qrUrl,
                        ]);
                }




                public function update_second_delivery(Request $request)
                {
                    // Lấy giá trị 'id' từ request
                    $id_order_input = $request->input('id');
                    
                    // Tìm order theo id
                    $order = Order::where('txn_ref', $id_order_input)->first();
                    
                    if ($order) {
                        if ($order->giao_hang == 'đã giao hàng') {
                            return response()->json([
                                'message' => 'Đơn hàng đã được giao',
                            ]);
                        }
                        else if ($order->giao_hang == 'chờ xác nhận') {
                            $order->giao_hang = 'chờ lấy hàng';
                            $order->save();
                            return response()->json([
                                'message' => 'Trạng thái giao hàng đã được cập nhật thành chờ lấy hàng',
                            ]);
                        }
                        else {
                            return response()->json([
                                'message' => 'Không thể cập nhật trạng thái giao hàng',
                            ]);
                        }
                    }
                    
                    // Trả về phản hồi nếu không tìm thấy đơn hàng
                    return response()->json([
                        'message' => 'Đơn hàng không tồn tại',
                    ]);
                }


                public function update_third_delivery(Request $request)
                {
                    // Lấy giá trị 'id' từ request
                    $id_order_input = $request->input('id');
                    
                    // Tìm order theo id
                    $order = Order::where('txn_ref', $id_order_input)->first();
                    
                    if ($order) {
                        if ($order->giao_hang == 'đã giao hàng') {
                            return response()->json([
                                'message' => 'Đơn hàng đã được giao',
                            ]);
                        }
                        else if ($order->giao_hang == 'chờ lấy hàng') {
                            $order->giao_hang = 'đang giao hàng';
                            $order->save();
                            return response()->json([
                                'message' => 'Trạng thái giao hàng đã được cập nhật thành đang giao hàng',
                            ]);
                        }
                        else {
                            return response()->json([
                                'message' => 'Không thể cập nhật trạng thái giao hàng',
                            ]);
                        }
                    }
                    
                    // Trả về phản hồi nếu không tìm thấy đơn hàng
                    return response()->json([
                        'message' => 'Đơn hàng không tồn tại',
                    ]);
                }



                public function update_delete_delivery(Request $request)
                {
                    // Lấy giá trị 'id' và 'action' từ request
                    $id_order_input = $request->input('id');
                    $action = $request->input('action');
                    
                    // Tìm order theo id
                    $order = Order::where('txn_ref', $id_order_input)->first();
                    
                    if ($order) {
                        if ($action == 'cancel' && $order->giao_hang == 'chờ xác nhận') {
                            $order->giao_hang = 'đã hủy';
                            $order->status = 'đã hủy';
                            $order->save();
                            return response()->json([
                                'message' => 'Đơn hàng đã được hủy thành công',
                            ]);
                        }
                        else if ($order->giao_hang == 'đã giao hàng') {
                            return response()->json([
                                'message' => 'Đơn hàng đã được giao',
                            ]);
                        }
                        else if ($order->giao_hang == 'chờ lấy hàng') {
                            $order->giao_hang = 'đang giao hàng';
                            $order->save();
                            return response()->json([
                                'message' => 'Trạng thái giao hàng đã được cập nhật thành đang giao hàng',
                            ]);
                        }
                        else {
                            return response()->json([
                                'message' => 'Không thể cập nhật trạng thái giao hàng',
                            ]);
                        }
                    }
                    
                    
                    return response()->json([
                        'message' => 'Đơn hàng không tồn tại',
                    ]);
                }


                public function order_showall()
                {
                    
                    // $admin = JWTAuth::user()->HLV === 1;
                    // if (!$admin) {
                    //     return response()->json(['message' => 'You are not admin'], 403);
                    // }

                    
                    $orders = Order::with(['detailCarts.product'])->get();

                    
                    return response()->json($orders);
                }


                //------------------------------------HOANG-----------------

                public function search_order_HLV(Request $request)
                {
                    // Lấy giá trị 'id' từ request
                    $id_order_input = JWTAuth::user()->hlv;
                    if($id_order_input!=1){
                        return response()->json("Bạn không phải huấn luyện viên");
                    }
                
                    $id_order_input = JWTAuth::user()->id;
                
                    // Tìm order theo id và cập nhật trạng thái giao hàng
                    $order = table_atg_members::where('id', $id_order_input)->first();
                
                    // Kiểm tra xem huấn luyện viên có quản lý câu lạc bộ nào không
                    if (!$order || !$order->id_club) {
                        return response()->json('Bạn chưa được giao quản lý câu lạc bộ nào.', 403);
                    }
                
                    $club_id = $order->id_club;
                
                    $getall_member = table_atg_members::where('id_club', $club_id)->get();
                    $member_ids = $getall_member->pluck('id');
                
                    $lang = $request->get('lang', 'vi'); // Lấy giá trị lang từ request, mặc định là 'vi'
                
                    $orders = Order::whereIn('member_id', $member_ids)
                        ->with(['detailCarts.product'])
                        ->get()
                        ->map(function ($order) use ($lang) {
                            $order->detailCarts->map(function ($detailCart) use ($lang) {
                                if ($detailCart->product) { // Kiểm tra product có tồn tại không
                                    $detailCart->product_name = $detailCart->product->getNameByLang($lang);
                                    $detailCart->category_name = $detailCart->product->getCategoryByLang($lang);
                                    $detailCart->supplier_name = $detailCart->product->getSupplierByLang($lang);
                                } else {
                                    // Xử lý trường hợp product không tồn tại, ví dụ:
                                    $detailCart->product_name = 'Sản phẩm không tồn tại';
                                    $detailCart->category_name = 'Không có';
                                    $detailCart->supplier_name = 'Không có';
                                }
                                return $detailCart;
                            });
                            return $order;
                        });
                
                    if ($orders->isEmpty()) {
                        return response()->json('Không có hóa đơn của học viên nào hết');
                    }
                
                    return response()->json($orders);
                }

                public function search_order_HLV2(Request $request)
                {
                    $id_order_input = JWTAuth::user()->hlv;
                    $lang = $request->query('lang', 'vi'); 

                    if ($id_order_input != 1) {
                        return response()->json("Bạn không phải huấn luyện viên");
                    }

                    $id_order_input = JWTAuth::user()->id;
                    $order = table_atg_members::where('id', $id_order_input)->first();
                    $club_id = $order->id_club;

                    $getall_member = table_atg_members::where('id_club', $club_id)->get();
                    $member_ids = $getall_member->pluck('id');

                    $orders = Order::whereIn('member_id', $member_ids)->with(['detailCarts.product'])->get();

                    if ($orders->isEmpty()) {
                        return response()->json('Không có hóa đơn của học viên nào hết');
                    }

                    // Transform product data within each order
                    $orders = $orders->map(function ($order) use ($lang) {
                        $order->detailCarts = $order->detailCarts->map(function ($detailCart) use ($lang) {
                            if ($detailCart->product) {
                                $detailCart->product->ProductName = $lang === 'en' ? $detailCart->product->tenenglish : $detailCart->product->ProductName;
                                $detailCart->product->CategoryName = $lang === 'en' ? $detailCart->product->CategoryNameEng : $detailCart->product->CategoryName;
                                $detailCart->product->SupplierName = $lang === 'en' ? $detailCart->product->SupplierNameEng : $detailCart->product->SupplierName;
                            }
                            return $detailCart;
                        });
                        return $order;
                    });

                    return response()->json($orders);
                }

            public function search_order_HLV3(Request $request) //Hoa don theo HLV
        {
            // Lấy giá trị 'id' từ request
            $id_order_input = JWTAuth::user()->hlv;
            if($id_order_input!=1){
                return response()->json("Bạn không phải huấn luyện viên");
            }
            
            $id_order_input = JWTAuth::user()->id;
            // Tìm order theo id và cập nhật trạng thái giao hàng
            $order = table_atg_members::where('id', $id_order_input)->first();
            $club_id = $order->id_club;
            
            $getall_member = table_atg_members::where('id_club', $club_id)->get();

            // Lấy danh sách member_id từ kết quả trên
            $member_ids = $getall_member->pluck('id'); // Giả sử 'id' là khóa chính của member

            // Lấy tất cả các order từ bảng table_orders dựa trên danh sách member_id
            $orders = Order::whereIn('member_id', $member_ids)->with(['detailCarts.product'])->get();
            if($orders->isEmpty()){
                return response()->json('Không có hóa đơn của học viên nào hết');
            }
            
            return response()->json($orders);
        }


        public function update_sanpham(Request $request)
        {
            // Lấy giá trị 'txn_ref' từ request
            $txn_ref = $request->input('txn_ref');

            // Kiểm tra xem hóa đơn có tồn tại không
            $order = Order::where('txn_ref', $txn_ref)->first();
            if (!$order) {
                return response()->json(['error' => 'Không tìm thấy hóa đơn'], 404);
            }

            // Kiểm tra thời gian thanh toán của đơn hàng
            // $payDate = Carbon::parse($order->pay_date, 'Asia/Ho_Chi_Minh');
            // $currentTime = now()->setTimezone('Asia/Ho_Chi_Minh');
            // $timeDiff = $currentTime->diffInMinutes($payDate);
            
            // if ($timeDiff > 60) {
            //     return response()->json(['error' => 'Không thể cập nhật đơn hàng vì đã quá 1 giờ kể từ khi thanh toán'], 400);
            // }

            // Lấy danh sách sản phẩm từ request
            $products = $request->input('products');
            if (!is_array($products) || empty($products)) {
                return response()->json(['error' => 'Danh sách sản phẩm không hợp lệ'], 400);
            }

            $id_order = $order->id;
            detail_order::where('id_order', $id_order)->delete();

            $updatedProducts = [];
            foreach ($products as $product) {
                if (!isset($product['id_product']) || !isset($product['quantity'])) {
                    return response()->json(['error' => 'Thông tin sản phẩm không đầy đủ'], 400);
                }

                $id_product = $product['id_product'];
                $quantity = $product['quantity'];

                // Kiểm tra sản phẩm có tồn tại không
                $productModel = Product::find($id_product);
                if (!$productModel) {
                    return response()->json(['error' => "Không tìm thấy sản phẩm có ID: $id_product"], 404);
                }

                $price_once = $productModel->UnitPrice;
                $sale = $productModel->sale;
                $price = $price_once * $quantity - $price_once * $quantity * $sale;

                $updatedProducts[] = [
                    'id_product' => $id_product,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }

            // Cập nhật chi tiết đơn hàng
            foreach ($updatedProducts as $product) {
                detail_order::updateOrCreate(
                    ['id_order' => $order->id, 'id_product' => $product['id_product']],
                    ['quantity' => $product['quantity']]
                );
            }

            // Tính tổng giá trị từ $updatedProducts
            $totalPrice = array_sum(array_column($updatedProducts, 'price'));

            // Cập nhật tổng giá trị đơn hàng
            $order->amount = $totalPrice;
            $order->save();

            return response()->json([
                'success' => 'Cập nhật đơn hàng thành công',
                'txn_ref' => $txn_ref,
                'total_price' => $totalPrice,
                'updated_products' => $updatedProducts
            ]);
        }

        public function info_new_money(Request $request)
        {
            $id_club = $request->input('id_club');

            $order = News::where('id_club',$id_club)->where('type', 'khoa-thi')->first();

            if (!$order) {
                return response()->json(['message' => 'id câu lạc bộ không tồn tại'], 404);
            }

            $money = $order->lephithi;
            $ngaythi = $order->thoigian;

            return response()->json([$money,$ngaythi]);
        }
}  