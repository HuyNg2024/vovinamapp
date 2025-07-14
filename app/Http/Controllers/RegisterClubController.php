<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UploadService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache; 
use Illuminate\Support\Facades\Mail;
use App\Services\FirebaseStorageService;
use Stichoza\GoogleTranslate\GoogleTranslate;
use App\Rules\UniqueClubName;
use App\Mail\OTP;
use App\Models\City;
use App\Models\Club;
use App\Models\Country;
use App\Models\District;
use App\Models\ClubRegisterTracker;
use Illuminate\Support\Facades\Validator;
use Geocoder\StatefulGeocoder;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Provider\OpenCage\OpenCage;
use Http\Adapter\Guzzle7\Client as GuzzleAdapter;
use Tymon\JWTAuth\Facades\JWTAuth; 


class RegisterClubController extends Controller
{
           
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function uploadClubImage($photoFile, Club $club) 
    {
        if ($photoFile) { 
           
            $tenkhongdau = $this->formatNameNoAccent($club->ten); 

            $url = $this->uploadService->uploadImageClub($photoFile, $tenkhongdau, $club);
            return $url; 
        }

        return null;
    }
    
    public function getCoordinates($latitude, $longitude)
    {
        try {
           
            $country = Country::whereRaw("ST_Distance_Sphere(point(map_long, map_lat), point(?, ?)) < 50000", [$longitude, $latitude])->first();
            $city = City::whereRaw("ST_Distance_Sphere(point(map_long, map_lat), point(?, ?)) < 50000", [$longitude, $latitude])->first();
    
           
            $district = District::whereRaw("ST_Distance_Sphere(point(map_long, map_lat), point(?, ?)) < 10000", [$longitude, $latitude])->orderByRaw("ST_Distance_Sphere(point(map_long, map_lat), point(?, ?)) ASC", [$longitude, $latitude])->first();
    
            return [
                'id_country' => $country ? $country->id : null,
                'id_city' => $city ? $city->id : null,
                'id_district' => $district ? $district->id : null,
            ];
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }
    public function registerClub(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['error' => 'Chưa được phép'], 401);
        }

        if ($user->hlv !== 1) {
            return response()->json(['error' => 'Truy cập bị từ chối. Chỉ có huấn luyện viên (HLV) mới có thể đăng ký câu lạc bộ'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', new UniqueClubName], 
            'owner' => 'required|string',
            'address' => 'required|string|unique:table_club_register_tracker,address|unique:table_club,diachi',
            'phone' => 'required|string',
            'email' => 'required|email|unique:table_club_register_tracker,email|unique:table_club,email',
            'timeopen' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'map_lat' => 'required|numeric', 
            'map_long' => 'required|numeric',
        ], [
            'name.required' => 'The club name is required.',
            'name.unique' => 'The club name has already been registered.',
            'address.required' => 'The address is required.',
            'address.unique' => 'The address has already been registered.',
            'email.required' => 'The email address is required.',
            'email.unique' => 'The email address has already been registered.',
            'photo.required' => 'A club photo is required.',
            'photo.image' => 'The photo must be an image file.',
            'photo.mimes' => 'The photo must be a file of type: jpeg, jpg, png.',
            'photo.max' => 'The photo may not be greater than 2048 kilobytes.',
            'map_lat.required' => 'The latitude is required.',
            'map_long.required' => 'The longitude is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $club = new Club(); 
        $club->ten = $request->name; 

        $imageUrl = $this->uploadClubImage($request->file('photo'), $club);
        $locationIds = $this->getCoordinates($request->map_lat, $request->map_long);

        if (!$imageUrl || !$locationIds) {
            return response()->json(['error' => !$imageUrl ? 'Tải lên hình ảnh không thành công' : 'Không tìm thấy địa chỉ'], 500);
        }

        $registrationData = [
            'name' => $request->name,
            'owner' => $request->owner,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'timeopen' => $request->timeopen,
            'desc' => $request->desc,
            'map_lat' => $request->map_lat,
            'map_long' => $request->map_long,
            'id_country' => $locationIds['id_country'], 
            'id_city' => $locationIds['id_city'],
            'id_district' => $locationIds['id_district'],
            'photo' => $imageUrl,
            'id_user' => $user->id,
            'desc' => $request->timeopen,
        ];

        Cache::put('registration_data_' . $request->email, $registrationData, now()->addMinutes(5));

        return $this->sendOTP($request->email);
    }

 
    public function sendOTP($email) 
    {
        try {
            $otp = rand(100000, 999999); 

            Cache::put('otp_' . $email, $otp, 300); // OTP valid trong 5 phut

            Mail::to($email)->send(new OTP($otp));

            return response()->json(['success' => 'Mã OTP đã được gửi đến email của bạn'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gửi mã OTP không thành công'], 500);
        }
    }

    private function generateSN($clubName) {
        $sn = 'VVN-';
        $words = explode(' ', $clubName);
        foreach ($words as $word) {
            $sn .= strtoupper(substr($word, 0, 1));
        }
        return $sn;
    }

    public function verifyOTP(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (!$user) {
            return response()->json(['error' => 'Không được phép'], 401);
        }

        if ($user->hlv !== 1) {
            return response()->json(['error' => 'Từ chối truy cập. Chỉ có huấn luyện viên (HLV) mới có thể xác minh mã OTP'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

       
        $registrationData = Cache::get('registration_data_' . $request->email);

        if (!$registrationData) {
            return response()->json(['error' => 'Dữ liệu đăng ký không tìm thấy. Vui lòng đăng ký lại'], 404);
        }

        
        $cachedOtp = Cache::get('otp_' . $request->email);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            try {
                $sn = $this->generateSN($registrationData['name']);
                $tenkhongdau = $this->formatNameNoAccent($registrationData['name']);
                // $tenen = TextTranslationHelper::translateToEnglish($registrationData['name']);
                // $diachien = TextTranslationHelper::translateToEnglish($registrationData['address']);
                $clubData = [
                    'ten' => $registrationData['name'],
                    'diachi' => $registrationData['address'],
                    'thoigianhoc' => $registrationData['timeopen'],
                    'map_lat' => $registrationData['map_lat'],
                    'map_long' => $registrationData['map_long'],
                    'image' => $registrationData['photo'],
                    'id_city' => $registrationData['id_city'],
                    'id_district' => $registrationData['id_district'],
                    'id_atg_members' => $user->id,
                    'dienthoai' => $registrationData['phone'],
                    'email' => $registrationData['email'],
                    'sn' => $sn, 
                    'tenen' => $this->translateToEnglish($registrationData['name']), 
                    'diachien' => $this->translateToEnglish($registrationData['address']), 
                    'tenkhongdau' => $tenkhongdau

                ];

                $club = Club::create($clubData);

                
                $registrationData['id_club'] = $club->id;

                
                ClubRegisterTracker::create($registrationData);

               
                Cache::forget('otp_' . $request->email);
                Cache::forget('registration_data_' . $request->email);

                return response()->json(['success' => 'Đăng ký câu lạc bộ thành công'], 200);

            } catch (\Exception $e) {
                
                Log::error('Error creating Club or ClubRegisterTracker: ' . $e->getMessage());

               
                return response()->json(['error' => 'Đăng ký câu lạc bộ không thành công. Vui lòng thử lại sau'], 500);
            }
        } else {
            return response()->json(['error' => 'Mã OTP không hợp lệ'], 400);
        }
    }

    private function translateToEnglish($text)
    {
        $tr = new GoogleTranslate();
        $tr->setSource('vi'); 
        $tr->setTarget('en'); 
        return $tr->translate($text);
    }
    
    private function formatNameNoAccent($name)
    {
       
        $accentedCharacters = [
            'à', 'á', 'ả', 'ã', 'ạ', 'â', 'ầ', 'ấ', 'ẩ', 'ẫ', 'ậ', 'ă', 'ằ', 'ắ', 'ẳ', 'ẵ', 'ặ',
            'è', 'é', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ề', 'ế', 'ể', 'ễ', 'ệ',
            'ì', 'í', 'ỉ', 'ĩ', 'ị',
            'ò', 'ó', 'ỏ', 'õ', 'ọ', 'ô', 'ồ', 'ố', 'ổ', 'ỗ', 'ộ', 'ơ', 'ờ', 'ớ', 'ở', 'ỡ', 'ợ',
            'ù', 'ú', 'ủ', 'ũ', 'ụ', 'ư', 'ừ', 'ứ', 'ử', 'ữ', 'ự',
            'ỳ', 'ý', 'ỷ', 'ỹ', 'ỵ',
            'đ',
            'À', 'Á', 'Ả', 'Ã', 'Ạ', 'Â', 'Ầ', 'Ấ', 'Ẩ', 'Ẫ', 'Ậ', 'Ă', 'Ằ', 'Ắ', 'Ẳ', 'Ẵ', 'Ặ',
            'È', 'É', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ề', 'Ế', 'Ể', 'Ễ', 'Ệ',
            'Ì', 'Í', 'Ỉ', 'Ĩ', 'Ị',
            'Ò', 'Ó', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ồ', 'Ố', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ờ', 'Ớ', 'Ở', 'Ỡ', 'Ợ',
            'Ù', 'Ú', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ừ', 'Ứ', 'Ử', 'Ữ', 'Ự',
            'Ỳ', 'Ý', 'Ỷ', 'Ỹ', 'Ỵ',
            'Đ'
        ];
        $nonAccentedCharacters = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y',
            'D'
        ];
        $name = str_replace($accentedCharacters, $nonAccentedCharacters, $name);
        return strtolower(str_replace(' ', '-', $name)); 
    }
    
}
