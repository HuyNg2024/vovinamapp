<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\cart_payments;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;

use App\Models\class_payment;
use App\Models\detail_cart;
use GuzzleHttp\Promise\Create;

class CartController extends Controller
{
    public function getCartItems(Request $request)
{
    $memberId = $request->input('member_id');
    $lang = $request->input('lang', 'vi'); // Mặc định là tiếng Việt nếu không có tham số `lang`

    // Lấy các mục trong giỏ hàng kèm sản phẩm
    $cartItems = Cart::where('member_id', $memberId)->with('product')->get();

    // Duyệt qua từng mục trong giỏ hàng và cập nhật các thuộc tính trong product theo ngôn ngữ
    $cartItems->each(function($cartItem) use ($lang) {
        $product = $cartItem->product;

        if ($lang === 'en') {
            // Đổi các thuộc tính sang tiếng Anh
            $product->ProductName = $product->tenenglish;
            $product->CategoryName = $product->CategoryNameEng;
            $product->SupplierName = $product->SupplierNameEng;
        }
        // Nếu `lang=vi`, giữ nguyên giá trị tiếng Việt, không cần thay đổi gì
    });

    // Trả về dữ liệu với cấu trúc không thay đổi
    return response()->json(['cart' => $cartItems]);
}





    public function addToCart(Request $request)
{
    $idAtgMembers = $request->input('member_id');
    $productId = $request->input('product_id');
    $quantity = $request->input('quantity');

    $product = Product::find($productId);

    if ($product) {
        // Tính giá sau khi áp dụng giảm giá
        $priceAfterSale = $product->UnitPrice * (1 - $product->sale); // Giảm giá
        $totalPrice = $priceAfterSale * $quantity; // Tính tổng giá cho số lượng

        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        $existingCartItem = Cart::where('member_id', $idAtgMembers)
                                 ->where('product_id', $productId)
                                 ->first();

        if ($existingCartItem) {
            // Nếu sản phẩm đã có trong giỏ hàng, cập nhật số lượng và tổng giá
            $existingCartItem->quantity += $quantity; // Tăng số lượng
            $existingCartItem->total_price += $totalPrice; // Cập nhật tổng giá
            $existingCartItem->save(); // Lưu lại thay đổi
        } else {
            // Nếu sản phẩm chưa có trong giỏ hàng, thêm mới
            Cart::create([
                'member_id' => $idAtgMembers,
                'product_id' => $productId,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ]);
        }

        return response()->json(['message' => 'Product added to cart.']);
    }

    return response()->json(['message' => 'Product not found.'], 404);
}

    // API để lấy tổng giá trị của giỏ hàng
    public function getTotalPrice(Request $request)
{
    $memberId = $request->input('member_id');
    $cartItems = Cart::where('member_id', $memberId)->get();

    $totalPrice = 0;

    foreach ($cartItems as $cartItem) {
        $product = Product::find($cartItem->product_id);

        // Tính giá sau khi áp dụng phần trăm giảm giá
        $priceAfterSale = $product->UnitPrice * (1 - $product->sale);

        // Tính tổng tiền cho từng mục giỏ hàng
        $itemTotal = $priceAfterSale * $cartItem->quantity;

        // Cập nhật total_price trong bảng carts cho từng mục giỏ hàng
        $cartItem->total_price = $itemTotal;
        $cartItem->save();

        // Cộng dồn tổng tiền cho tất cả các mục giỏ hàng
        $totalPrice += $itemTotal;
    }

    // Trả về tổng số tiền đã tính toán sau khi áp dụng giảm giá
    return response()->json(['total_price' => $totalPrice]);
}
public function updateCartTotalPrices()
{
    $cartItems = Cart::all();

    foreach ($cartItems as $cartItem) {
        $product = Product::find($cartItem->product_id);
        if ($product) {
            $cartItem->total_price = $cartItem->quantity * $product->UnitPrice;
            $cartItem->save();
        }
    }
}



    // Xóa một sản phẩm cụ thể khỏi giỏ hàng cho người dùng
    public function removeFromCart(Request $request)
    {
        $memberId = $request->input('member_id');   // Lấy ID thành viên và ID sản phẩm từ request
        $productId = $request->input('product_id');

       
        $cartItems = Cart::where('member_id', $memberId)
                        ->where('product_id', $productId)
                        ->get();

        if ($cartItems->isNotEmpty()) {
            // Nếu tìm thấy mục giỏ hàng, xóa khỏi giỏ
            Cart::where('member_id', $memberId)
                ->where('product_id', $productId)
                ->delete();
            // Trả về thông báo thành công với ID sản phẩm đã xóa
            return response()->json([
                'message' => 'Sản phẩm đã được xóa khỏi giỏ hàng hoàn toàn.',
                'product_id' => $productId,
            ]);
        }
        // Nếu không tìm thấy mục giỏ hàng, trả về lỗi 404
        return response()->json(['message' => 'Không tìm thấy sản phẩm trong giỏ hàng.'], 404);
    }

 

    // API để tăng số lượng sản phẩm trong giỏ hàng
    public function increaseQuantity(Request $request)
    {
        $memberId = $request->input('member_id');  // Lấy ID thành viên, ID sản phẩm và số lượng tăng từ request
        $productId = $request->input('product_id');
        $quantityIncrement = $request->input('quantity_increment', 1); 

        $cartItem = Cart::where('member_id', $memberId) // Tìm mục giỏ hàng cho thành viên và sản phẩm cụ thể
                        ->where('product_id', $productId)
                        ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantityIncrement;  // Tính toán số lượng và tổng giá mới
            $newTotalPrice = $cartItem->product->UnitPrice * $newQuantity;
            // Cập nhật mục giỏ hàng với số lượng và tổng giá mới
            $cartItem->update([
                'quantity' => $newQuantity,
                'total_price' => $newTotalPrice,
            ]);

            // return response()->json(['message' => 'Product quantity updated.', 'new_quantity' => $newQuantity, 'new_total_price' => $newTotalPrice]);
            return response()->json([
                'message' => 'Product quantity updated.',
                'product_id' => $productId,
                'new_quantity' => $newQuantity,
                'new_total_price' => $newTotalPrice
            ]); 
        }
        // Nếu không tìm thấy sản phẩm trong giỏ hàng, trả về lỗi 404
        return response()->json(['message' => 'Product not found in cart.'], 404);
    }

    // Giảm số lượng của một sản phẩm trong giỏ hàng
    public function decreaseQuantity(Request $request)
    {
        $memberId = $request->input('member_id');  // Lấy ID thành viên, ID sản phẩm và số lượng giảm từ request
        $productId = $request->input('product_id');
        $quantityDecrement = $request->input('quantity_decrement', 1); 

        $cartItem = Cart::where('member_id', $memberId)  // Tìm mục giỏ hàng cho thành viên và sản phẩm cụ thể
                        ->where('product_id', $productId)
                        ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity - $quantityDecrement;   // Tính toán số lượng mới sau khi giảm
            // Nếu số lượng mới lớn hơn 0, cập nhật mục giỏ hàng
            if ($newQuantity > 0) {
                $newTotalPrice = $cartItem->product->UnitPrice * $newQuantity;

                $cartItem->update([
                    'quantity' => $newQuantity,
                    'total_price' => $newTotalPrice,
                ]);

                return response()->json([
                    'message' => 'Product quantity decreased.',  // Trả về thông báo thành công với số lượng và tổng giá đã cập nhật
                    'product_id' => $productId,
                    'new_quantity' => $newQuantity,
                    'new_total_price' => $newTotalPrice
                ]);
            } else {
                $cartItem->delete();  // Nếu số lượng mới bằng hoặc nhỏ hơn 0, xóa mục giỏ hàng
                return response()->json([    // Trả về thông báo sản phẩm đã bị xóa khỏi giỏ hàng
                    'message' => 'Product removed from cart as quantity reached zero.',
                    'product_id' => $productId
                ]);
            }
        }
        // Nếu không tìm thấy sản phẩm trong giỏ hàng, trả về lỗi 404
        return response()->json(['message' => 'Product not found in cart.'], 404);
    }

}

