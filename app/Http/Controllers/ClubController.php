<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Club;

class ClubController extends Controller
{
    public function findNearbyClubs(Request $request)
    {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $distance = 10; // 10km

        $clubs = DB::table('table_club')
            ->select(DB::raw('*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance'))
            ->having('distance', '<', $distance)
            ->orderBy('distance')
            ->setBindings([$latitude, $longitude, $latitude])
            ->get();

        return response()->json($clubs);
    }

    public function searchClubs(Request $request)
    {
        $district = $request->input('district');
        $city = $request->input('city');

        $clubs = DB::table('table_club')
            ->when($district, function ($query, $district) {
                return $query->where('district', $district);
            })
            ->when($city, function ($query, $city) {
                return $query->where('city', $city);
            })
            ->get();

        return response()->json($clubs);
    }
}
