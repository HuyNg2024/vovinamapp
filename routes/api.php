<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\CartController;
//--add new -----
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
//---mua hang------------
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\Api\PayController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\CityController;
//------tintuc-lythuyet-------------
use App\Http\Controllers\Api\ClubController;

use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\CategoryController;

use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Api\ClassController;

use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CheckinController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\RegisterClubController;
use App\Http\Controllers\RegisterExamController;
use App\Http\Controllers\Api\DangkydaiController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Api\Atg_memberController;
use App\Http\Controllers\Api\ClassPaymentController;
use App\Http\Controllers\Api\RegisterClassController;
use App\Http\Controllers\Api\EducationGradesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
| 
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//---------------------------------------Dũng----------------------------------------------

// for authentication
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/register', [AuthController::class, 'register']); //1
    Route::post('/login', [AuthController::class, 'login']);//2
    Route::post('/logout', [AuthController::class, 'logout']);//4
    Route::post('/refresh', [AuthController::class, 'refresh']);//3
    Route::get('/profile', [AuthController::class, 'me']);//5
    Route::put('/updateInfo', [AuthController::class,'updateInfo']);//8
    Route::put('/updatePass',[AuthController::class, 'updatePassword']);//9
    Route::get('/profile/all', [AuthController::class, 'profileAll']);//6
    Route::get('/profile/viaId', [AuthController::class, 'profileviaID']);//7
    Route::get('/clubs/getdetail/member',[AuthController::class, 'getMemberclub']);//10 //add en
    Route::post('/delete-account', [AuthController::class, 'deleteAccount']);
});
// forgot password method
Route::post('forgotpassword/request', [PasswordResetController::class, 'sendOtp']);//11
Route::post('forgotpassword/verify', [PasswordResetController::class, 'verifyOtp']);//12
Route::post('forgotpassword/reset', [PasswordResetController::class, 'resetPassword']);//13


//------atg_member query
Route::get('showAll', [Atg_memberController::class, 'index']);//.
Route::get('show/{id}', [Atg_memberController::class, 'show']);//.
// Route::post('create', [Atg_memberController::class, 'store']);
// Route::put('update/{id}', [Atg_memberController::class, 'update']);
// Route::delete('delete/{id}', [Atg_memberController::class, 'destroy']);

//upload img new final n
Route::middleware('auth:api')->group(function () {
    Route::post('/upload/avatar', [UploadController::class, 'UploadAvatar']);
});//14
// Route::post('/upload/imgProduct',[FileUploadController::class, 'uploadProductImage']);
// Route::post('/upload/imgClub',[FileUploadController::class, 'uploadClubImage']);
// Route::post('/upload/imgBelt',[FileUploadController::class, 'uploadBeltImage']);

//Checkin and Class and Belts
Route::group(['middleware' => ['auth:api']], function () {
    // Lấy ds lớp của giảng viên 
    Route::get('/teacher/classes/getall',[CheckinController::class, 'getTeacherClasses']); //add en

    // Lấy ds các thành viên trong lớp của giảng viên theo id_class 
    Route::get('/teacher/classes/getdetail',[CheckinController::class, 'getClassMembers']);//28

    // Route cho giáo viên điểm danh
    Route::post('/teacher/checkin', [CheckinController::class, 'teacherCheckin']);//29
    
    // Route cho thành viên xem thông tin điểm danh của mình
    Route::get('/member/view-checkin', [CheckinController::class, 'memberViewCheckin']);//32 //add en
    
    // Route cho giáo viên xem thông tin điểm danh của các lớp trong CLB
    Route::get('/teacher/view-checkin', [CheckinController::class, 'teacherViewCheckinAll']);//30 

    // [FIX] Removed duplicate GET /teacher/view-checkin route (was calling teacherViewCheckin).
    // Keeping teacherViewCheckinAll above since it is more inclusive.

    //Route cho giảng viên lấy các lớp theo ngày và thời điểm đó
    Route::post('/teacher/classofday-forcheckin', [CheckinController::class, 'getClassofDayForCheckin']);//31 add en

    //Route cho giảng viên tìm kiếm lớp của mình 
    Route::post('/teacher/search-Myclasses', [CheckinController::class, 'searchClassesOfTeacher']);//31 add en

    //CURD class
    Route::post('/teacher/create-class', [ClassController::class, 'create']);
    //Route::get('/teacher/create-class', [ClassController::class, 'read']);
    Route::put('/teacher/update-class', [ClassController::class, 'update']);
    Route::delete('/teacher/delete-class', [ClassController::class, 'delete']);
    Route::get('/teacher/list-class', [ClassController::class, 'list']);

    //Belt
    //0. Lấy ds các thành viên trong clb mà đăng ký thi
    Route::get('/teacher/belts/get-allRegistration', [DangkydaiController::class, 'ResultBeltTeacherView']);
    // 1. Cập nhật thông tin người đăng ký thi
    Route::post('/teacher/belts/update-registration-info', [DangkydaiController::class, 'updateRegistrationInfo']);
    // 2. Duyệt hồ sơ
    Route::post('/teacher/belts/approve-registration', [DangkydaiController::class, 'approveRegistration']);
    // 3. Không duyệt và xóa hồ sơ
    Route::post('/teacher/belts/reject-registration', [DangkydaiController::class, 'rejectAndDeleteRegistration']);
    // 4. Cập nhật kết quả thi
    Route::post('/teacher/belts/update-exam-result', [DangkydaiController::class, 'updateExamResult']);

    //CURD Exams
    Route::get('/exams/getAll', [ExamController::class, 'getAllExam']);
    Route::get('exams/getDetail', [ExamController::class, 'getExam']);
    Route::post('/exams/create', [ExamController::class, 'createExam']);
    Route::put('/exams/update', [ExamController::class, 'updateExam']);
    Route::delete('/exams/delete', [ExamController::class, 'deleteExam']);
});

//get banner
Route::get('/getBanners', [BannerController::class,'getBanner']);
//get ds club from table_club
Route::get('/clubs/getall', [ClubController::class, 'getAll']); //15 //add en
Route::get('/clubs/getdetail', [ClubController::class, 'getDetailclub']); //16 //add en

Route::put('/clubs/joinclub',[ClubController::class, 'joinClub']);
Route::put('/clubs/outclub',[ClubController::class, 'outClub']);
Route::get('/clubs/classes/getall', [ClassController::class, 'getClassesinclub']);//17 //add en
Route::get('clubs/classes/getdetail',[ClassController::class, 'getClassinclub']);//18  //add en
Route::post('/classes/joinclass', [ClassController::class, 'joinClass']); //thanh toán online

// join class new (Pending)
Route::post('/clubs/classes/join-classpending', [ClassController::class, 'joinClassPending']); //thanh toán trực tiếp//19
// Lấy danh sách class đang chờ xét duyệt của 1 người
Route::get('/clubs/classes/pending', [ClassController::class, 'getPendingClasses']); //20 //add en
// rời class-pending(đang duyệt) cho người dùng
Route::post('clubs/classes/out-pending',[ClassController::class, 'leaveClassPending']);//21


// [FIX] Removed duplicate GET /teacher/classes/getall route. It already exists inside auth:api middleware (line 103).
// The authenticated version should be used instead.
// hiện các thành viên đang đăng ký clb của coach đó
Route::get('/coach/classes/getMemberpending', [ClassController::class, 'getPendingClassesForCoach']); //23 //add en
// hlv duyệt các thành viên đó
Route::post('/coach/classes/approve-join', [ClassController::class, 'approveJoinClassRequest']);//26
//hlv ko phê duyệt
Route::post('/coach/classes/disapprove-join',[ClassController::class, 'rejectJoinClassRequest']);//24
//hlv cập nhật yêu cầu, đồng thời phê duyệt luôn
Route::post('/coach/classes/update-pending',[ClassController::class, 'updateMemberClassRequest']);//25
// Lấy 3 sp bán chạy nhất (dựa trên số lượng sản phẩm bán)
Route::get('/getTopSellingProducts',[ProductController::class, 'getTopSellingProducts']);//.27 //add en

//Route lấy 30 danh sách tin tức mới nhất
Route::get('/news/latest30', [NewsController::class, 'getLatestNews30']);

//đăng ký nhanh và yêu câu tham gia lớp cho khách 
Route::post('/quick-register', [AuthController::class, 'quickRegister']);


//---------------------------------------Huy----------------------------------------------


Route::get('countries', [CountryController::class, 'index']); // dùng
// Route::get('countries/{id}', [CountryController::class, 'show']);
Route::get('countries/id', [CountryController::class, 'show']);

// Tìm kiếm địa chỉ clb trong bán kính 10km
Route::get('map3', [MapController::class, 'findClubsWithinRadius']);
Route::get('map2', [MapController::class, 'showMap']);
Route::get('map1', [MapController::class, 'showClubsByCountry']);

Route::get('clubs/country', [MapController::class, 'showClubsByCountry']); // Thay đổi route để dùng query parameter
Route::get('map', [MapController::class, 'showMap']);
Route::get('find-address', [MapController::class, 'findAddress']);



Route::get('/cities', [CityController::class, 'index']); // dùng
Route::get('/cities/{id}', [CityController::class, 'show']);

Route::get('cities/country/{countryId}', [CityController::class, 'citiesByCountry']);   



Route::get('/classes', [ClassController::class, 'index']);
Route::post('/classes', [ClassController::class, 'store']);


// [FIX] Cart modification routes now require auth:api — cart operations must be tied to a logged-in user
Route::middleware('auth:api')->group(function () {
    // Xóa sản phẩm ra khỏi giỏ hàng
    Route::post('/cart/remove', [CartController::class, 'removeFromCart']);

    // Tăng số lượng sản phẩm trong giỏ hàng
    Route::post('/cart/increase-quantity', [CartController::class, 'increaseQuantity']);

    // Giảm số lượng sản phẩm trong giỏ hàng
    Route::post('/cart/decrease-quantity', [CartController::class, 'decreaseQuantity']);
});

// Hiện thông tin lớp học đã đăng ký hoặc chưa đăng ký của người dùng
Route::get('user/classes', [ClassController::class, 'getUserRegisteredClasses']);

// Hiện danh sách đai
Route::get('/all-belts', [EducationGradesController::class, 'index']);
//Hiện cấp đai hiện tại của người dùng
Route::get('members/belt-level', [Atg_memberController::class, 'getCapdaiById']);
//Hiện thông tin chi tiết của đai
Route::get('education-grades/belt-info', [EducationGradesController::class, 'showBeltInfo']);

// dang ky lop hoc -> thanh cong se có id_dai//hien co
// Đăng ký thi lên đai
// [FIX] Belt registration requires auth:api — must know which member is registering
Route::middleware('auth:api')->post('/register-belt', [Atg_memberController::class, 'dangKyDai']);

// Rời khỏi lớp học
// Route::post('/roi-khoi-lop-hoc', [ClassController::class, 'leaveClass']); 
Route::post('/leave-class', [ClassController::class, 'leaveClass']);
// // Lấy thông tin CLB đã đăng ký
// Route::get('user/registeredClub', [ClubController::class, 'getClubRegisteredClasses']); 

// Hiện hóa đơn đăng ký lớp học của người dùng
Route::get('/payment-history', [ClassPaymentController::class, 'getPaymentHistory']); 

// Tìm kiếm clb theo tên 
Route::get('/clubs/search_name', [ClubController::class, 'searchClubs']); 

// Tìm kiếm lớp học theo tên
Route::get('/classes/search_name', [ClassController::class, 'searchClasses']);

// Hiện thông tin đăng ký lớp học
Route::get('register-class/get', [RegisterClassController::class, 'get']);

// [FIX] Order viewing routes require auth:api — order data is user-specific and sensitive
Route::middleware('auth:api')->group(function () {
    // Hiện hóa đơn theo người dùng (chỉ hiện đã giao hàng)
    Route::get('orders/orders/user', [OrderController::class, 'showByMemberId']);

    // Tìm kiếm hóa đơn khi nhập tên sản phẩm của người dùng
    Route::get('orders/search-by-product-name', [OrderController::class, 'showByProductName']); 

    // Lọc hóa đơn theo giá (min, max)
    Route::post('/orders/filter-by-price', [OrderController::class, 'filterByPrice']);
    // Tìm kiếm hóa đơn khi nhập tên sản phẩm của người dùng
    Route::post('/orders/filter-by-paydate', [OrderController::class, 'filterByPayDate']);
    // Get danh sách đơn hàng đang vận chuyển
    Route::get('/orders/undelivered', [OrderController::class, 'showUndeliveredOrders']);

    // Lấy hóa đơn của người dùng
    Route::get('/orders/All', [OrderController::class, 'showAllOrders']);
    Route::get('/orders/All3', [OrderController::class, 'showAllOrders3']);
});
// Sort sản phẩm nổi bật
Route::get('/productsFeatured', [ProductController::class, 'sortByFeatured']);

// Sort sản phẩm mới thêm
Route::get('/productsNew', [ProductController::class, 'sortByNew']);

// Sort sản phẩm theo sale 
Route::get('/productsSale', [ProductController::class, 'sortBySale']);


// Tham gia các clb(trạng thái chờ được duyệt của người dùng)
Route::post('/clubs/join-clubpending', [ClubController::class, 'joinClubPending']); 
// Lấy danh sách clb đang chờ xét duyệt của 1 người
Route::get('/clubs/pending', [ClubController::class, 'getPendingClubs']); 

// Rời clb-pending(đang duyệt) cho người dùng
Route::post('/clubs/out-pending',[ClubController::class, 'leaveClubPending']);

// Hiện clb của coach
Route::get('/coach/clubs', [ClubController::class, 'getCoachClubs']); // ko dùng
// Hiện các thành viên đang đăng ký clb của coach đó
Route::get('/coach/join-memberpending', [ClubController::class, 'getPendingClubsforCoach']); 
// Hlv duyệt các thành viên đó
Route::post('/coach/approve-join', [ClubController::class, 'approveJoinRequest']);

 // [FIX] Leave class/club request routes require auth:api — these actions are member/coach-specific
 Route::middleware('auth:api')->group(function () {
    /*------------------------Yêu cầu rời lớp(clb) của môn sinh, Xem ds môn sinh yêu cầu rời, và duyệt rời lớp hoc (clb) cho hlv------------------*/
    Route::post('/member/leave-class-request', [Atg_memberController::class, 'leaveClassRequest']);
    Route::get('/member/leave-class-request-status', [Atg_memberController::class, 'getLeaveClassRequestStatus']);
    Route::get('/coach/leave-class-requests', [Atg_memberController::class, 'getLeaveClassRequests']);
    Route::post('/coach/approve-leave-class-request', [Atg_memberController::class, 'approveLeaveClassRequest']);
    /*------------------------Yêu cầu rời clb(môn sinh), Xem ds môn sinh yêu cầu rời, và duyệt rời clb(Hlv)------------------*/
    Route::post('/member/leave-club-request', [Atg_memberController::class, 'leaveClubRequest']);
    Route::get('/coach/leave-club-requests', [Atg_memberController::class, 'getLeaveClubRequestsForCoach']);
    Route::post('/coach/approve-leave-club-request', [Atg_memberController::class, 'approveLeaveClubRequest']);
 });

Route::get('/coach/leave-club-requests-allstatus', [Atg_memberController::class, 'getLeaveClubRequestsAllStatus']); // ko dùng
/*------------------------Lấy danh sách lớp học theo id clb------------------*/
Route::get('/classes/by-club', [ClassController::class, 'getClassesByClubId']);

// [FIX] Removed duplicate GET /coach/leave-club-requests-allstatus route (already defined on line 315).
/*------------------------Lấy thông tin lớp học------------------*/
Route::get('/classes/by-id', [ClassController::class, 'getClassById']);
/*------------------------Lấy danh sách sản phẩm khuyến mãi------------------*/

Route::get('/sale-products', [ProductController::class, 'getSaleProductsSortedBySale']);


//API điểm danh lọc theo tên cho hlv
Route::post('/teacher/view-checkin-by-name', [CheckinController::class, 'teacherViewCheckinByName']);
//API điểm danh lọc theo id cho hlv
Route::post('/teacher/view-checkin-by-id', [CheckinController::class, 'teacherViewCheckinById']);
//API điểm danh lọc theo số điện thoại cho hlv
Route::post('/teacher/view-checkin-by-phone', [CheckinController::class, 'teacherViewCheckinByPhone']);

// HLV hủy yêu cầu rời lớp học
Route::post('/coach/reject-leave-class-request', [Atg_memberController::class, 'rejectLeaveClassRequest']);


Route::get('/clubs/news-exam', [RegisterExamController::class, 'getNewsByClubAndType']);
Route::post('/register-for-exam', [RegisterExamController::class, 'registerForExam'])->middleware('auth:api'); 
Route::get('/exam-registration-confirmation', [RegisterExamController::class, 'createExamRegistrationConfirmation']);
Route::get('/register-for-exam-status', [RegisterExamController::class, 'checkRegistrationStatus']);


//HLV đăng ký clb mới

Route::post('/coach/register-newclub', [RegisterClubController::class, 'registerClub']);
Route::post('/coach/verify-otp-and-register-club', [RegisterClubController::class, 'verifyOTP']);


//lấy khóa thi theo clb
Route::get('/exams-by-club', [RegisterExamController::class, 'getAllExamsByClub']);
Route::post('/cancel-registration', [RegisterExamController::class, 'cancelRegistration']);

//----------------------------------Hậu--------------------------------------------------

// Route cho danh sách câu lạc bộ gần đây (ví dụ)
Route::get('clubs/nearby', [ClubController::class, 'findNearbyClubs']);

// Route cho tìm kiếm câu lạc bộ (ví dụ)
Route::get('clubs/search', [ClubController::class, 'searchClubs']);

///////mua sam 
//các api liên quan đấn sản phẩm
Route::get('products', [ProductController::class, 'index']);//lấy danh sách toàn bộ sản phẩm

Route::get('getAllproducts', [ProductController::class, 'getallsp']);//lấy danh sách toàn bộ sản phẩm

Route::get('products/{id}', [ProductController::class, 'show']);//Lấy chi tiết sản phẩm
Route::get('products/search/{name}', [ProductController::class, 'search']);//Tìm kiếm tên sản phẩm/////////
Route::post('/products/filter-by-supplier', [ProductController::class, 'filterBySupplier']);//lọc theo nhà cung cấp//////
Route::post('/products/filter-by-price', [ProductController::class, 'filterByPrice']);//lọc theo giá tiền///////
Route::get('suppliers', [SupplierController::class, 'index']);//lấy danh sách nhà cung cấp
Route::get('suppliers/{id}', [SupplierController::class, 'show']);//lấy chi tiết nhà cung cấp
//Route::post('suppliers/{id}/rate', [SupplierController::class, 'updateRating']);
Route::get('reviews/{productId}', [ReviewController::class, 'index']);//đánh giá sản phẩm
Route::post('reviews', [ReviewController::class, 'store']);//thêm đánh giá
Route::get('/categories', [CategoryController::class, 'index']);//lấy danh mục sản phẩm
Route::get('/products/category/{categoryname}', [ProductController::class, 'getByCategory']);//lấy danh sách sản phẩm theo loại/////////


// Giỏ hàng
// [FIX] Cart view/add/total routes require auth:api — cart is user-specific
Route::middleware('auth:api')->group(function () {
    Route::get('cart', [CartController::class, 'getCartItems']);//lấy danh sách sản phẩm trong giỏ hàng
    Route::post('cart', [CartController::class, 'addToCart']);// thêm vào giỏ hàng
    //tính tổng tiền sản phẩm trong giỏ hàng
    Route::get('/cart/total-price', [CartController::class, 'getTotalPrice']);
});
//thanh toán đơn hàng
Route::get('orders', [OrderController::class, 'viewOrders']);
//tintuc-lythuyetvovnam
    Route::get('/dai', [NewsController::class, 'getBeltTypes']);
    Route::get('/thongbao', [NewsController::class, 'getAnnouncements']);//trandat FE
    Route::get('/thongbao/{id}', [NewsController::class, 'getAnnouncementDetail']);
    Route::get('/lythuyet', [NewsController::class, 'getMartialArtsTheory']);
    Route::get('/lythuyet/{id}', [NewsController::class, 'getMartialArtsTheoryDetail']);
    Route::get('/thongbao/search/{tenvi}', [NewsController::class, 'searchAnnouncements']);
    Route::get('/lythuyet/search/{tenvi}', [NewsController::class, 'searchMartialArtsTheory']);

    Route::get('/martial-arts-theory-by-club-and-belt/{beltId}', [NewsController::class, 'filterMartialArtsTheoryByClubAndBelt']);
    //Route::get('/theory-by-belt/{beltId}', [NewsController::class, 'getTheoryByBelt']);


// Route cho lọc tin tức và thông báo theo id_club
    Route::get('/news/filter-announcements-by-club/{id_club}', [NewsController::class, 'filterAnnouncementsByClub']);
// Route cho lọc tin tức và thông báo theo id_club mà không phụ thuộc vào type
    Route::get('/news/filter-by-club/{id_club}', [NewsController::class, 'filterByClubWithoutType']);    
// Route cho lọc lý thuyết Vovinam theo id_clubS
    Route::get('/news/filter-theory-by-club/{id_club}', [NewsController::class, 'filterMartialArtsTheoryByClub']);
//Route lấy toàn bộ dữ liệu trong bảng news
    Route::get('/news', [NewsController::class, 'getAllNews']);
//Route lấy 3 danh sách tin tức mới nhất
    Route::get('/news/latest', [NewsController::class, 'getLatestNews']);
//Route lấy 10 tin tức cho mọt trang
    Route::get('/news/10perpage', [NewsController::class, 'getLatestNews10']);
//Route lọc thêm id_club vẫn phân trang
    Route::get('/news/club/{id_club}', [NewsController::class, 'getNewsByClub']);

//Route lấy toàn bộ danh sách có type=tin-tuc
    Route::get('/news/tintuc', [NewsController::class, 'getnew']);
//route lấy 30 bài mới nhất có type=tin-tuc
    Route::get('/news/tintuc/30', [NewsController::class, 'getnew30']); 


//------------------------------HOANG--------------------------

//------------------------HoangSTART------------------//
//thanh toán giỏ hàng

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {

//hàm create nhận dữ liệu từ form và chuyền đến vnpayreturn
    Route::post('create_payment', [PayController::class, 'create'])->name('create_payment');
    Route::get('vnpay/return', [PayController::class, 'vnpayreturn'])->name('vnpay.return');
});




//tra cứu hóa đơn
Route::get('/hoadon', [PayController::class, 'checkhoadon']);

Route::get('/cart/show', [PayController::class, 'showcart'])->name('cart.show');


Route::get('/chitiethoadon', [PayController::class, 'chitiet_hoadon']);





//-----======================thanh toán đăng ký lớp học



//hàm create nhận dữ liệu từ form và chuyền đến vnpayreturn
    Route::post('create_paymentCLB', [PayController::class, 'createCLB'])->name('create_paymentCLB');
    Route::get('vnpayCLB.return', [PayController::class, 'vnpayreturnCLB'])->name('vnpay.returnCLB');





    //-----======================THANH TOÁN ĐĂNG KÝ LÊN ĐAI=============================---

    Route::post('create_paymentdai', [PayController::class, 'create_dai'])->name('create_paymentdai');
    Route::get('vnpaydai.return', [PayController::class, 'vnpayreturn_dai'])->name('vnpay.returndai');


    

//------------------------------HOANGEND--------------------------

//---------------------PHẦN ĐƠN HÀNG----------------------------
Route::post('/search_order_HLV', [PayController::class, 'search_order_HLV']);
Route::post('/search_order_HLV2', [PayController::class, 'search_order_HLV2']);
Route::post('/update_sanpham', [PayController::class, 'update_sanpham']);
