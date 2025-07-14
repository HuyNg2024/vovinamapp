<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function getBanner(Request $request)
    {
        
        $lang = $request->input('lang', 'vi');
        $currentDate = Carbon::now();

        // Tìm banner còn hiệu lực
        $banner = Banner::where('startdate', '<=', $currentDate)
                        ->where('enddate', '>=', $currentDate)
                        //->where('id', $request->id)
                        ->first();

        // Nếu không tìm thấy banner hiệu lực, lấy banner mặc định (id = 1)
        if (!$banner) {
            $banner = Banner::find(1);
        }

        // Nếu vẫn không tìm thấy banner, trả về lỗi
        if (!$banner) {
            return response()->json([
                'success' => false,
                'message' => 'No banner found'
            ], 404);
        }

        // Trả về thông tin banner
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $banner->id,
                'url_logo' => $banner->url_logo,
                'url_background' => $banner->url_background,
                'title' => $lang == 'vi' ? $banner->titlevi : $banner->titleen,
                'description' => $lang == 'vi' ? $banner->descvi : $banner->descen,
                'startdate' => $banner->startdate,
                'enddate' => $banner->enddate,
                'note' => $banner->note,
                'created_date' => $banner->created_date,
                'is_default' => $banner->id == 1
            ]
        ]);
    }
}