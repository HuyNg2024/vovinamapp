<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Lấy danh sách nhà cung cấp
    public function index()
    {
        return Supplier::all();
    }

    // Lấy chi tiết nhà cung cấp
    public function show($id)
    {
        return Supplier::findOrFail($id);
    }

    // Cập nhật đánh giá nhà cung cấp
    public function updateRating(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->rating = $request->input('rating');
        $supplier->save();

        return response()->json(['message' => 'Rating updated successfully']);
    }
}

