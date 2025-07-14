<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Club;
use GuzzleHttp\Client;
use App\Models\Country;
use Illuminate\Http\Request;
use Geocoder\StatefulGeocoder;
use Geocoder\Query\GeocodeQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Geocoder\Provider\OpenCage\OpenCage;
use Illuminate\Support\Facades\Validator; 
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;

class MapController extends Controller
{

    public function showMap2(Request $request)
    {
        $country_id = $request->query('id_country');
        $city_id = $request->query('id_city');

        // Check if both country_id and city_id are missing
        if (!$country_id || !$city_id) {
            return response()->json(['error' => 'Thiếu thông tin quốc gia hoặc thành phố'], 422);
        }

        if ($city_id) {
            $location = City::find($city_id);

            if (!$location) {
                return response()->json(['error' => 'Không tìm thấy thành phố'], 404);
            }

            $clubs = Club::where('id_city', $city_id)->get()->map(function($club) {
                return [
                    'id' => $club->id,
                    'ten' => $club->ten,
                    'diachi' => $club->diachi,
                    'map_lat' => number_format($club->map_lat, 6),
                    'map_long' => number_format($club->map_long, 6),
                ];
            });
        } else {
            $location = Country::find($country_id);

            if (!$location) {
                return response()->json(['error' => 'Không tìm thấy quốc gia'], 404);
            }

            $clubs = Club::whereHas('city', function($query) use ($country_id) {
                $query->where('id_country', $country_id);
            })->get()->map(function($club) {
                return [
                    'id' => $club->id,
                    'ten' => $club->ten,
                    'diachi' => $club->diachi,
                    'map_lat' => number_format($club->map_lat, 6),
                    'map_long' => number_format($club->map_long, 6),
                ];
            });
        }

        return response()->json([
            'clubs' => $clubs,
        ]);
    }

    public function showMap(Request $request)
    {
        $country_id = $request->query('id_country');
        $city_id = $request->query('id_city');
        $lang = $request->query('lang', 'vi');

        $validator = Validator::make($request->all(), [
            'id_country' => 'required_without:id_city|exists:table_country,id',
            'id_city' => 'required_without:id_country|exists:table_city,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($city_id) {
            $location = City::find($city_id);

            if (!$location) {
                return response()->json(['error' => 'City not found'], 404);
            }

            $clubs = Club::where('id_city', $city_id)->where('hienthi', 1)->get(); 
        } else {
            $location = Country::find($country_id);

            if (!$location) {
                return response()->json(['error' => 'Country not found'], 404);
            }

            $clubs = Club::whereHas('city', function($query) use ($country_id) {
                $query->where('id_country', $country_id);
            })->where('hienthi', 1)->get(); 
        }

        $clubs = $clubs->map(function($club) use ($lang) {
            return [
                'id' => $club->id,
                'ten' => $lang === 'en' ? ($club->tenen ?: $club->ten) : $club->ten,
                'diachi' => $lang === 'en' ? ($club->diachien ?: $club->diachi) : $club->diachi,
                'map_lat' => number_format($club->map_lat, 6),
                'map_long' => number_format($club->map_long, 6),
            ];
        });

        return response()->json([
            'clubs' => $clubs,
        ]);
    }

    public function showMap3(Request $request)
    {
        $country_id = $request->query('id_country');
        $city_id = $request->query('id_city');
        $lang = $request->query('lang', 'vi');

        
        $validator = Validator::make($request->all(), [
            'id_country' => 'required_without:id_city|exists:table_country,id',
            'id_city' => 'required_without:id_country|exists:table_city,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if ($city_id) {
            $location = City::find($city_id);

            if (!$location) {
                return response()->json(['error' => 'City not found'], 404);
            }

            $clubs = Club::where('id_city', $city_id)->get();
        } else {
            $location = Country::find($country_id);

            if (!$location) {
                return response()->json(['error' => 'Country not found'], 404);
            }

            $clubs = Club::whereHas('city', function($query) use ($country_id) {
                $query->where('id_country', $country_id);
            })->get();
        }

        
        $clubs = $clubs->map(function($club) use ($lang) {
            return [
                'id' => $club->id,
                'ten' => $lang === 'en' ? ($club->tenen ?: $club->ten) : $club->ten,
                'diachi' => $lang === 'en' ? ($club->diachien ?: $club->diachi) : $club->diachi,
                'map_lat' => number_format($club->map_lat, 6),
                'map_long' => number_format($club->map_long, 6),
            ];
        });

        return response()->json([
            'clubs' => $clubs,
        ]);
    }
    
    public function showClubsByCountry(Request $request)
    {
        $country_id = $request->query('id_country');
        $lang = $request->query('lang', 'vi'); 
    
        if (!$country_id) {
            return response()->json(['error' => 'Thiếu thông tin quốc gia'], 422);
        }
    
        $country = Country::find($country_id);
    
        if (!$country) {
            return response()->json(['error' => 'Không tìm thấy quốc gia'], 404);
        }
    
        $clubs = Club::whereHas('city', function($query) use ($country_id) {
            $query->where('id_country', $country_id);
        })->get()->map(function($club) use ($lang) {
            return [
                'id' => $club->id,
                'ten' => $lang === 'en' ? ($club->tenen ?: $club->ten) : $club->ten, 
                'diachi' => $lang === 'en' ? ($club->diachien ?: $club->diachi) : $club->diachi, 
                'map_lat' => number_format($club->map_lat, 6),
                'map_long' => number_format($club->map_long, 6),
            ];
        });
    
        return response()->json([
            'clubs' => $clubs,
        ]);
    }
    public function showClubsByCountry2(Request $request)
    {
        $country_id = $request->query('id_country');

        if (!$country_id) {
            return response()->json(['error' => 'Thiếu thông tin quốc gia'], 422);
        }

        $country = Country::find($country_id);

        if (!$country) {
            return response()->json(['error' => 'Không tìm thấy quốc gia'], 404);
        }

        $clubs = Club::whereHas('city', function($query) use ($country_id) {
            $query->where('id_country', $country_id);
        })->get()->map(function($club) {
            return [
                'id' => $club->id,
                'ten' => $club->ten,
                'diachi' => $club->diachi,
                'map_lat' => number_format($club->map_lat, 6),
                'map_long' => number_format($club->map_long, 6),
            ];
        });

        return response()->json([
            'clubs' => $clubs,
        ]);
    }
    private function getClubsWithinRadius($userLocation, $radiusInKm, $lang = 'vi')
    {
        $columns = [
            'id',
            ($lang === 'en' ? 'tenen AS ten' : 'ten'),
            ($lang === 'en' ? 'diachien AS diachi' : 'diachi'),
            'map_lat',
            'map_long',
        ];

        return Club::select($columns)
            ->selectRaw("(6371 * acos(cos(radians(?)) 
                            * cos(radians(map_lat)) 
                            * cos(radians(map_long) - radians(?)) 
                            + sin(radians(?)) 
                            * sin(radians(map_lat)))) AS distance", 
                            [$userLocation->getLatitude(), $userLocation->getLongitude(), $userLocation->getLatitude()])
            ->havingRaw('distance < ?', [$radiusInKm])
            ->orderBy('distance')
            ->get()->map(function ($club) {
               
                $club->map_lat = number_format($club->map_lat, 6);
                $club->map_long = number_format($club->map_long, 6);
                return $club;
            });
    }

    public function findClubsWithinRadius(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $apiKey = env('OPENCAGE_API_KEY');
        $httpClient = new GuzzleAdapter();
        $provider = new OpenCage($httpClient, $apiKey);
        $geocoder = new StatefulGeocoder($provider, 'en');

        try {
            $result = $geocoder->geocodeQuery(GeocodeQuery::create($request->address));

            if ($result->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy địa chỉ'], 404);
            }

            $userLocation = $result->first()->getCoordinates();
            $lang = $request->get('lang', 'vi');
            $clubsWithinRadius = $this->getClubsWithinRadius($userLocation, 10, $lang); 

            return response()->json(['clubs' => $clubsWithinRadius]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
    public function  findClubsWithinRadius2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

       
        $apiKey = env('OPENCAGE_API_KEY');
        $httpClient = new GuzzleAdapter();
        $provider = new OpenCage($httpClient, $apiKey);
        $geocoder = new StatefulGeocoder($provider, 'en');

        try {
            $result = $geocoder->geocodeQuery(GeocodeQuery::create($request->address));

            if ($result->isEmpty()) {
                return response()->json(['error' => 'Không tìm thấy địa chỉ'], 404);
            }

            $userLocation = $result->first()->getCoordinates();
            $clubsWithinRadius = $this->getClubsWithinRadius($userLocation, 10); 

            return response()->json(['clubs' => $clubsWithinRadius]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }

    private function getClubsWithinRadius2($userLocation, $radiusInKm)
    {
        return Club::select('id', 'ten', 'diachi', 'map_lat', 'map_long') 
            ->selectRaw("(6371 * acos(cos(radians(?)) 
                               * cos(radians(map_lat)) 
                               * cos(radians(map_long) - radians(?)) 
                               + sin(radians(?)) 
                               * sin(radians(map_lat)))) AS distance", 
                        [$userLocation->getLatitude(), $userLocation->getLongitude(), $userLocation->getLatitude()])
            ->havingRaw('distance < ?', [$radiusInKm])
            ->orderBy('distance')
            ->get();
    }
   

}
