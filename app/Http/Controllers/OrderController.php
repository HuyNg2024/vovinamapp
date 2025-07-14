<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product; 
use App\Models\detail_cart;  
use App\Models\table_atg_members;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
class OrderController extends Controller
{
    
    public function showByMemberId(Request $request)
    {
        
        $memberId = $request->input('member_id'); 

        
        $validator = Validator::make(['member_id' => $memberId], [
            'member_id' => 'required|integer|exists:table_atg_members,id'
        ], [
            'member_id.exists' => 'Member not found.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ], 422); 
        }

       
        $member = table_atg_members::findOrFail($memberId);
        
       
        $order = Order::with('member')
            ->where('member_id', $memberId)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    } 

     // Hàm hiển thị thông tin đơn hàng dựa trên tên sản phẩm
    public function showByProductName(Request $request)
    {
        try {
           
            $user = JWTAuth::parseToken()->authenticate(); // Xác thực người dùng thông qua JWT
    
            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401); 
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401); 
        }
    
        $productName = $request->input('product_name');  // Lấy tên sản phẩm từ request
    
        $validator = Validator::make(['product_name' => $productName], [
            'product_name' => 'required|string'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tên sản phẩm không hợp lệ'
            ], 422);
        }
    
        $orders = Order::with(['detailCarts.product'])  // Lọc đơn hàng theo tên sản phẩm và ID người dùng
            ->where('member_id', $user->id)
            ->whereHas('detailCarts.product', function ($query) use ($productName) {
                $query->where('ProductName', 'like', "%$productName%");
            })->get();
    
        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng nào cho người dùng này'], 404); 
        }
    
        return response()->json($orders); 
    }

     // Hàm lọc đơn hàng dựa trên giá
    public function filterByPrice(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }
       
        $validator = Validator::make($request->all(), [
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric|gte:min_price', 
        ], [
            'min_price.required' => 'Giá tối thiểu không được để trống.',
            'max_price.required' => 'Giá tối đa không được để trống.',
            'max_price.gte' => 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $minPrice = $request->input('min_price'); // Lấy giá trị giá tối thiểu và tối đa từ request
        $maxPrice = $request->input('max_price');

        $query = Order::with(['detailCarts.product'])  // Lọc đơn hàng theo giá và ID người dùng
            ->where('member_id', $user->id);

        if ($minPrice) {
            $query->where('amount', '>=', $minPrice); 
        }

        if ($maxPrice) {
            $query->where('amount', '<=', $maxPrice); 
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return response()->json([], 200); // Trả về danh sách rỗng và status code 200
        }

        return response()->json($orders);
    }

    // Hàm lọc đơn hàng dựa trên ngày thanh toán
    public function filterByPayDate(Request $request) 
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();  // Xác thực người dùng thông qua JWT

            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }
        // Kiểm tra tính hợp lệ của ngày bắt đầu và ngày kết thúc
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', // Kiểm tra tính hợp lệ của ngày
        ], [
            'start_date.required' => 'Ngày bắt đầu không được để trống.',
            'end_date.required' => 'Ngày kết thúc không được để trống.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }
        // Lấy ngày bắt đầu và ngày kết thúc từ request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Order::with(['detailCarts.product'])
            ->where('member_id', $user->id);

        if ($startDate) {
            $query->whereDate('pay_date', '>=', $startDate); // Lọc theo ngày bắt đầu
        }

        if ($endDate) {
            // Tạo ngày kết thúc vào lúc 23:59:59 để bao gồm tất cả giao dịch trong ngày
            $endDateWithTime = $endDate . ' 23:59:59'; 
            $query->whereDate('pay_date', '<=', $endDateWithTime); // Lọc theo ngày kết thúc
        }

        $orders = $query->get();

        if ($orders->isEmpty()) {
            return response()->json([], 200); // Trả về danh sách rỗng và status code 200
        }

        return response()->json($orders);
    }

    // Hàm hiển thị đơn hàng chưa giao
    public function showUndeliveredOrders(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();  // Xác thực người dùng thông qua JWT

            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }
        // Lọc đơn hàng chưa giao theo ID người dùng
        $orders = Order::with(['detailCarts.product'])
            ->where('member_id', $user->id)
            ->where('giao_hang', 'chưa giao hàng') // Lọc theo trạng thái chưa giao hàng
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng nào chưa giao cho người dùng này'], 404);
        }

        return response()->json($orders);
    }

   

    public function showAllOrders2(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }

        $lang = $request->input('lang', 'vi'); // Lấy tham số lang từ request, mặc định là 'vi'

        $orders = Order::with(['detailCarts.product' => function ($query) use ($lang) {
            $query->select('ProductID', 'ProductName', 'SupplierID', 'UnitPrice', 'UnitsInStock', 'link_image', 'SupplierName', 'created_at', 'updated_at', 'sale', 'noibat'); 
            if ($lang === 'en') {
                $query->addSelect(DB::raw("tenenglish as ProductName, CategoryNameEng as CategoryName, SupplierNameEng as SupplierName"));
            }
        }])
        ->where('member_id', $user->id) 
        ->get(); 

        if ($orders->isEmpty()) {
            return response()->json([], 200); 
        }

        return response()->json($orders);
    }
    
    public function showAllOrders(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }

        $lang = $request->input('lang', 'vi'); 

        $orders = Order::with([
            'detailCarts.product' => function ($query) use ($lang) {
                $query->select('ProductID', 'ProductName', 'SupplierID', 'UnitPrice', 'UnitsInStock', 'link_image', 'SupplierName', 'created_at', 'updated_at', 'sale', 'noibat', 'CategoryName'); 
                if ($lang === 'en') {
                    $query->addSelect(DB::raw("tenenglish as ProductName, CategoryNameEng as CategoryName, SupplierNameEng as SupplierName"));
                }
            },
            'detailCarts.product.category' // Thêm eager loading cho danh mục
        ])
        ->where('member_id', $user->id) 
        ->get(); 

        if ($orders->isEmpty()) {
            return response()->json([], 200); 
        }

        return response()->json($orders);
    }

    public function showAllOrders3(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['message' => 'Không được phép truy cập'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Mã thông báo không hợp lệ'], 401);
        }

        $lang = $request->input('lang', 'vi'); 

        $orders = Order::with([
            'detailCarts.product' => function ($query) use ($lang) {
                $query->select('ProductID', 'ProductName', 'SupplierID', 'UnitPrice', 'UnitsInStock', 'link_image', 'SupplierName', 'created_at', 'updated_at', 'sale', 'noibat', 'CategoryName'); 
                if ($lang === 'en') {
                    $query->addSelect(DB::raw("tenenglish as ProductName, CategoryNameEng as CategoryName, SupplierNameEng as SupplierName"));
                }
            },
            'detailCarts.product.category' 
        ])
        ->where('member_id', $user->id) 
        ->get(); 

        if ($orders->isEmpty()) {
            return response()->json([], 200); 
        }

        // Dịch các trường cần thiết
        $orders->transform(function ($order) use ($lang) {
            if ($lang === 'en') {
                $order->order_info = ($order->order_info === 'Thanh toán khi nhận hàng') ? 'Cash on delivery' : $order->order_info;
                $order->status = ($order->status === 'chưa thanh toán') ? 'Not yet paid' : 
                                (($order->status === 'chưa thành toán') ? 'Not yet paid' : 
                                (($order->status === 'thành công') ? 'Success' : 
                                (($order->status === 'đã hủy') ? 'Cancelled' : $order->status))); 
            }
            return $order;
        });

        return response()->json($orders);
    }



}

