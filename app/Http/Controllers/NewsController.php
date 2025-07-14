<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\News; 
use App\Models\Club;
use App\Models\NewsCategory; 
use App\Models\NewsList; 
use App\Models\District; 
use App\Models\EducationGrade; 
use App\Models\EducationGrades; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth; // Thêm khai báo JWTAuth
class NewsController extends Controller
{
    /**
     * Lấy danh sách tin tức (thông báo).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnnouncements(Request $request)
{
    // Lấy các tham số từ request
    $id_district = $request->input('id_district');
    $role = $request->input('role'); // hlv hoặc hocvien
    $id_club = $request->input('id_club'); // Lọc theo id_club

    // Kiểm tra xem id_district, role, và id_club có tồn tại không
    if (!$id_district || !$role || !$id_club) {
        return response()->json(['error' => 'Thiếu id_district, role, hoặc id_club'], 400);
    }

    // Xây dựng query để lấy thông báo có type = 'tin-tuc', thuộc về quận và câu lạc bộ cụ thể
    $query = News::where('type', 'tin-tuc')
                ->where('id_district', $id_district)
                ->where('id_club', $id_club); // Lọc theo id_club

    // Lọc theo role (HLV hoặc học viên)
    if ($role === 'hlv') {
        // Chỉ lấy thông báo với hlv = 1
        $query->where('hlv', 1);
    } elseif ($role === 'hocvien') {
        // Chỉ lấy thông báo với hocvien = 1
        $query->where('hocvien', 1);
    } else {
        return response()->json(['error' => 'Role không hợp lệ'], 400);
    }

    // Lấy kết quả và sắp xếp theo thứ tự mới nhất
    $announcements = $query->orderBy('ngaytao', 'desc')->get();

    // Trả về kết quả dưới dạng JSON
    return response()->json($announcements);
}
//lấy tin tức type=tin-tuc
public function getnew(Request $request)
{
    $announcements = News::where('type', 'tin-tuc')->get();
        return response()->json($announcements);
}
// Lấy 30 tin tức mới nhất có type = 'tin-tuc'
public function getnew30(Request $request)
{
    $lang = $request->input('lang', 'vi'); // Mặc định là tiếng Việt nếu không có tham số lang
    $latestNews = News::where('type', 'tin-tuc') // Ràng buộc type = 'tin-tuc'
                    ->orderBy('ngaytao', 'desc') // Sắp xếp theo ngaytao từ mới đến cũ
                    ->limit(30) // Lấy tối đa 30 bản ghi
                    ->get();

    // Duyệt qua từng tin tức và cập nhật các thuộc tính theo ngôn ngữ
    $latestNews->each(function($newsItem) use ($lang) {
        if ($lang === 'en') {
            // Đổi các thuộc tính hiện tại sang giá trị tiếng Anh
            $newsItem->ten = $newsItem->tenen;
            $newsItem->noidung = $newsItem->noidungen;
            $newsItem->mota = $newsItem->motaen;
        } else {
            // Đổi các thuộc tính hiện tại sang giá trị tiếng Việt
            $newsItem->ten = $newsItem->tenvi;
            $newsItem->noidung = $newsItem->noidungvi;
            $newsItem->mota = $newsItem->motavi;
        }
    });

    return response()->json($latestNews);
}

   
    public function getAnnouncementDetail($id) {
        // Tìm thông báo theo id và loại là 'tin-tuc'.
        $announcement = News::where('id', $id)->where('type', 'tin-tuc')->first();
        if (!$announcement) {
            return response()->json(['message' => 'Announcement not found'], 404);
        }
        return response()->json($announcement);
    }

    /**
     * Lấy danh sách lý thuyết võ đạo.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMartialArtsTheory() {
        // Lấy tất cả bản ghi từ bảng News có loại là 'lythuyetvovinam'.
        $theories = News::where('type', 'lythuyetvovinam')->get();
        return response()->json($theories);
    }

    /**
     * Lấy chi tiết lý thuyết võ đạo theo id.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMartialArtsTheoryDetail($id) {
        // Tìm lý thuyết võ đạo theo id và loại là 'lythuyetvovinam'.
        $theory = News::where('id', $id)->where('type', 'lythuyetvovinam')->first();
        if (!$theory) {
            return response()->json(['message' => 'Theory not found'], 404);
        }
        return response()->json($theory);
    }

    /**
     * Tìm kiếm thông báo dựa vào tên.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAnnouncements($tenvi) {
        // Tìm kiếm thông báo có tên chứa từ khóa tìm kiếm.
        $announcements = News::where('type', 'tin-tuc')
                             ->where('tenvi', 'like', '%' . $tenvi . '%')
                             ->get();
        return response()->json($announcements);
    }

    /**
     * Tìm kiếm lý thuyết võ đạo dựa vào tên.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchMartialArtsTheory($tenvi) {
        // Tìm kiếm lý thuyết võ đạo có tên chứa từ khóa tìm kiếm.
        $theories = News::where('type', 'lythuyetvovinam')
                        ->where('tenvi', 'like', '%' . $tenvi . '%')
                        ->get();
        return response()->json($theories);
        
    }    public function getBeltTypes() {
        // Lấy tất cả bản ghi từ bảng EducationGrade.
        $belts = EducationGrade::all();
        return response()->json($belts);
    }
    /**
 * Lọc tin tức và thông báo theo id_club.
 * 
 * @param int $id_club
 * @return \Illuminate\Http\JsonResponse
 */
public function filterAnnouncementsByClub($id_club) {
    // Lọc tin tức và thông báo theo id_club và loại là 'tin-tuc'.
    $announcements = News::where('id_club', $id_club)
                         ->where('type', 'tin-tuc')
                         ->get();
    
    return response()->json($announcements);
}
/**
 * Lọc lý thuyết Vovinam theo id_club.
 * 
 * @param int $id_club
 * @return \Illuminate\Http\JsonResponse
 */
public function filterMartialArtsTheoryByClub($id_club) {
    // Lọc lý thuyết Vovinam theo id_club và loại là 'lythuyetvovinam'.
    $theories = News::where('id_club', $id_club)
                    ->where('type', 'lythuyetvovinam')
                    ->get();
    
    return response()->json($theories);
}
/**
 * Lọc lý thuyết Vovinam theo id_club và cấp đai (id từ bảng table_educationgrades).
 * 
 * @param int $id_club
 * @param int $beltId
 * @return \Illuminate\Http\JsonResponse
 */
public function filterMartialArtsTheoryByClubAndBelt($beltId) {
    // Lọc lý thuyết Vovinam theo  cấp đai (beltId) và loại là 'lythuyetvovinam'.
    $theories = News::where('id_dai', $beltId)
                    ->where('type', 'lythuyetvovinam')
                    ->get();

    return response()->json($theories);
}
//Lấy toàn bộ dữ liệu trong news
public function getAllNews()
{
    $news = News::all();
    return response()->json($news);
}
public function getLatestNews(Request $request)
{
    // Lấy thông tin người dùng từ JWT token đã xác thực
    $user = JWTAuth::user();
    
    if (!$user) {
        return response()->json(['error' => 'Không tìm thấy thông tin người dùng'], 404);
    }

    // Lấy id_club từ bảng table_atg_members
    $id_club = $user->id_club;

    // Lấy id_district từ bảng table_club dựa vào id_club
    $club = Club::where('id', $id_club)->first();

    if (!$club) {
        return response()->json(['error' => 'Không tìm thấy thông tin câu lạc bộ'], 404);
    }

    $id_district = $club->id_district;

    // Lấy role và lang từ request
    $role = $request->input('role'); // hlv hoặc hocvien
    $lang = $request->input('lang', 'vi'); // Mặc định là tiếng Việt nếu không có tham số `lang`

    // Kiểm tra xem role có tồn tại không
    if (!$role) {
        return response()->json(['error' => 'Thiếu role'], 400);
    }

    // Xây dựng query để lấy tin tức mới nhất theo id_district, id_club và type = 'tin-tuc'
    $query = News::where('id_district', $id_district) // Lọc theo id_district
                 ->where('id_club', $id_club) // Lọc theo id_club
                 ->where('type', 'tin-tuc') // Lọc theo type = 'tin-tuc'
                 ->orderBy('ngaytao', 'desc') // Sắp xếp theo ngaytao từ mới đến cũ
                 ->limit(3); // Lấy tối đa 3 bản ghi

    // Lọc theo role (HLV hoặc học viên)
    if ($role === 'hlv') {
        $query->where('hlv', 1);
    } elseif ($role === 'hocvien') {
        $query->where('hocvien', 1);
    }

    // Lấy kết quả
    $latestNews = $query->get();

    // Duyệt qua từng tin tức và cập nhật các thuộc tính theo ngôn ngữ
    $latestNews->each(function($newsItem) use ($lang) {
        if ($lang === 'en') {
            // Đổi các thuộc tính hiện tại sang giá trị tiếng Anh
            $newsItem->ten = $newsItem->tenen;
            $newsItem->noidung = $newsItem->noidungen;
            $newsItem->mota = $newsItem->motaen;
        } else {
            // Đổi các thuộc tính hiện tại sang giá trị tiếng Việt
            $newsItem->ten = $newsItem->tenvi;
            $newsItem->noidung = $newsItem->noidungvi;
            $newsItem->mota = $newsItem->motavi;
        }
    });

    // Trả về kết quả dưới dạng JSON
    return response()->json($latestNews);
}






public function getLatestNews10(Request $request)
{
    // Lấy tham số size từ query, mặc định là 10 nếu không có
    $size = $request->query('size', 10);

    // Lấy 10 tin tức mới nhất dựa trên cột ngaytao và phân trang
    $latestNews = News::orderBy('ngaytao', 'desc')
                          ->paginate($size);

    // Trả về kết quả dưới dạng JSON
    return response()->json($latestNews);
}
public function getNewsByClub(Request $request, $id_club)
{
    // Lấy tham số size từ query, mặc định là 10 nếu không có
    $size = $request->query('size', 10);

    // Lấy tin tức theo id_club và phân trang
    $news = News::where('id_club', $id_club)
                ->orderBy('ngaytao', 'desc')
                ->paginate($size);

    // Trả về kết quả dưới dạng JSON
    return response()->json($news);
}
public function filterByClubWithoutType($id_club)
    {
        // Lọc tin tức và thông báo theo id_club mà không phụ thuộc vào type
        $news = News::where('id_club', $id_club)->get();

        // Trả về kết quả dưới dạng JSON
        return response()->json($news);
    }
    
    public function getLatestNews30(Request $request)
    {
        $lang = $request->input('lang', 'vi');

        $latestNews = News::orderBy('ngaytao', 'desc')
                        ->limit(30)
                        ->get()
                        ->map(function ($newsItem) use ($lang) {
                            return [
                                'id' => $newsItem->id,
                                'ten' => $lang === 'en' ? $newsItem->tenen : $newsItem->tenvi,
                                'noidung' => $lang === 'en' ? $newsItem->noidungen : $newsItem->noidungvi,
                                'mota' => $lang === 'en' ? $newsItem->motaen : $newsItem->motavi,
                                'ngaytao' => $newsItem->ngaytao,
                            ];
                        });

        return response()->json([
            'success' => true,
            'data' => $latestNews
        ]);
    }
    
}
