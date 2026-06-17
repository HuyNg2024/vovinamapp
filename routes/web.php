<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/getdbs', function () {
    $gay= DB::table('table_user')->where('id',1)->first();
    if($gay){
        return response()->json($gay);
    }
    else
    return "you wrong";
});

Route::get('/map', function () {
    return view('map');
});

Route::get('clubs/nearby',function(){
    $clubs = DB::table('table_club')->get();
    return response()->json($clubs);
    });

Route::get('clubs/search',function(){
    $clubs = DB::table('table_club')->get();
    return response()->json($clubs);
    });




//----------------HHOANG--------------------START
//---route gọi form nhập id giỏ hàng muốn thanh toán


Route::get('/orders', function(){
    return view('vnpay.formthanhtoan');
});//form nhập id cart

use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\ClassPaymentController;
use App\Http\Controllers\Api\BeltPaymentController;
Route::get('/orders/show', [OrderPaymentController::class, 'showcart'])->name('orders');

//-------------------------------------------------------------------

Route::get('/paymentss/getlink', [OrderPaymentController::class, 'getlinkpay']);
// Route::get('/orders/checkhoadon', [OrderPaymentController::class, 'checkhoadon']);

///////////////////////////////////////
//------THanh toán CLB--------------
Route::get('/pay_clb/getlink', [ClassPaymentController::class, 'getlinkpayCLB']);
Route::get('/pay_clb/show', [ClassPaymentController::class, 'showcartCLB'])->name('ordersCLB');


//kiểm tra trạng thái thanh toán -->> ko liên quan đến xử lý dữ liệu
Route::get('/status_order', [OrderPaymentController::class, 'status_order']);


//kiểm tra trạng thái thanh toán -->> ko liên quan đến xử lý dữ liệu
Route::get('/status_payingclass', [ClassPaymentController::class, 'status_payingclass']);


//Thanh toán đăng ký lên đai
Route::get('/pay_dai/getlink', [BeltPaymentController::class, 'showcart_dai']);
//kiểm tra trạng thái thanh toán -->> ko liên quan đến xử lý dữ liệu
Route::get('/status_lendai', [BeltPaymentController::class, 'status_lendai']);






//=============>QR LINK=============
Route::get('/qr-payment', [OrderPaymentController::class, 'qrPayment'])->name('qr_payment');

Route::get('/qr-payment-clb', [ClassPaymentController::class, 'qrPaymentCLB'])->name('qr_paymentCLB');

Route::get('/qr_payment_dai', [BeltPaymentController::class, 'qrPayment_dai'])->name('qr_payment-dai');


//=============>TRA CỨU THÔNG TIN GIAO HÀNG=============
//=======================CẬP NHẬT TRẠNG THÁI GIAO HÀNG======
Route::get('/delivery_update', [OrderPaymentController::class, 'delivery_update'])->name('delivery_update');
Route::get('/update_second_delivery', [OrderPaymentController::class, 'update_second_delivery']);
Route::get('/update_third_delivery', [OrderPaymentController::class, 'update_third_delivery']);



//==================TÌM KIẾM ĐƠN HÀNG========
Route::get('/search_order', [OrderPaymentController::class, 'search_order'])->name('search_order');

//=============>ĐẶT HÀNG CHƯA THANH TOÁN=============

Route::get('/getlink_nopay', [OrderPaymentController::class, 'getlink_nopay'])->name('getlink_nopay');


//======================HỦY ĐẶT HÀNG===========================
Route::get('/update_delete_delivery', [OrderPaymentController::class, 'update_delete_delivery']);


Route::get('/admin_order', [OrderPaymentController::class, 'order_showall']);

Route::get('/info_new_money', [OrderPaymentController::class, 'info_new_money']);

//----------------HHOANG--------------------END========================================

