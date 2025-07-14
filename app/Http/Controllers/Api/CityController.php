<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lấy tất cả các bản ghi từ bảng table_city
        $cities = City::all();
        return response()->json($cities);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Lấy bản ghi cụ thể dựa trên ID
        $city = City::find($id);
        
        if (is_null($city)) {
            return response()->json(['message' => 'Không tìm thấy tỉnh/thành phố'], 404);
        }
        
        return response()->json($city);
    }


     public function citiesByCountry(string $countryId)

    {
        // Lấy tất cả các thành phố dựa trên ID quốc gia
        $cities = City::where('id_country', $countryId)->get();
        
        if ($cities->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy thành phố trong quốc gia này'], 404);
        }
        
        return response()->json($cities);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
