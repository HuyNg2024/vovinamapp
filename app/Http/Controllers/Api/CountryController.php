<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{
    public function index(Request $request)
    {
       
        $minClubs = $request->query('min_clubs', 0);

        
        $countries = Country::with(['cities.clubs'])
            ->get()
            ->filter(function ($country) use ($minClubs) {
                $clubCount = $country->cities->sum(function ($city) {
                    return $city->clubs->count();
                });

               
                return $clubCount >= $minClubs;
            })
            ->map(function ($country) {
                $clubCount = $country->cities->sum(function ($city) {
                    return $city->clubs->count();
                });

               
                return $country->ten . " (" . $clubCount . ")";
            });

        return response()->json($countries);
    }


    public function show(Request $request)
    {

        $id_country = $request->query('id_country');

        if (!$id_country) {
            return response()->json(['message' => 'Country ID is required'], 400);
        }

        $country = Country::with(['cities.clubs'])->find($id_country);

        if ($country) {
            $clubCount = $country->cities->sum(function ($city) {
                return $city->clubs->count();
            });

         
            return response()->json([
                'country' => $country->ten . " (" . $clubCount . ")"
            ]);
        } else {
            return response()->json(['message' => 'Không tìm thấy quốc gia'], 404);
        }
    }

   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
