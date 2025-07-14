<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        // Lấy danh sách các loại sản phẩm
        $categories = Category::all(); 
        // Category::select('CategoryID', 'CategoryName')->get();
        return response()->json($categories);
    }
}
