<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewController extends Controller
{
    // Lấy danh sách đánh giá cho một sản phẩm
    public function index($productId)
    {
        return Review::where('ProductID', $productId)->get();
    }

    // Thêm đánh giá mới
    public function store(Request $request)
    {
        $request->validate([
            'ProductID' => 'required|integer',
            'RatingValue' => 'required|integer|min:1|max:5',
            'RatingCount' => 'required|integer',
            'ReviewDate' => 'required|date',
            'ReviewContent' => 'required|string',
        ]);
$user=JWTAuth::user();
        $review = Review::create([
            'ProductID' => $request->input('ProductID'),
            'RatingValue' => $request->input('RatingValue'),
            'RatingCount' => $request->input('RatingCount'),
            'id_atg_members' => $user->id, // Lấy member_id từ người dùng hiện tại
            'ReviewDate' => $request->input('ReviewDate'),
            'ReviewContent' => $request->input('ReviewContent'),
        ]);

        return response()->json($review, 201);
    }
}
