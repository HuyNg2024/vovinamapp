<?php

// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Models\Cart; // Model giỏ hàng
use App\Models\Order; // Model đơn hàng
use App\Models\OrderDetail; // Model chi tiết đơn hàng
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function generateQRCode(Request $request)
    {
        $memberId = $request->input('member_id');
        
        // Tính tổng giá trị giỏ hàng
        $cartItems = Cart::where('member_id', $memberId)->get();
        $totalAmount = $cartItems->sum(function ($item) {
            return $item->product->UnitPrice * $item->quantity; // Sử dụng UnitPrice từ bảng products
        });

        // Tạo QR code (sử dụng thư viện của bạn)
        // $qrCode = ...; // Sinh QR code với tổng giá trị $totalAmount

        return response()->json([
            'amount' => $totalAmount,
            'qr_code' => 'https://api.vietqr.io/image/970425-0937759311-cG4PADy.jpg?accountName=HUYNH%20HUU%20LOI' // Thay bằng QR code thực tế
        ]);
    }

    public function handlePayment(Request $request)
    {
        $memberId = $request->input('member_id');
        $paymentId = $request->input('payment_id'); // ID thanh toán từ hệ thống thanh toán
        //$paymentId = uniqid('123', true); // Tạo ID tạm thời

        // Kiểm tra xem thanh toán có thành công không
        // Giả sử bạn có một hàm kiểm tra thanh toán
        
        $isPaymentSuccessful = true; // Thay thế bằng logic kiểm tra thực tế
        if ($isPaymentSuccessful) {
            // Chuyển sản phẩm từ giỏ hàng sang đơn hàng
            $cartItems = Cart::where('member_id', $memberId)->get();

            $order = Order::create([
                'member_id' => $memberId,
                'payment_id' => $paymentId,
                'total_amount' => $cartItems->sum('price'),
            ]);

            foreach ($cartItems as $item) {
                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            }

            // Xóa sản phẩm trong giỏ hàng
            Cart::where('member_id', $memberId)->delete();

            return response()->json(['message' => 'Payment successful and order created.']);
        }

        return response()->json(['message' => 'Payment failed.'], 400);
    }
}
