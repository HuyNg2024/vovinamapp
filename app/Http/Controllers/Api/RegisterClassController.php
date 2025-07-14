<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RegisterClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
class RegisterClassController extends Controller
{

    public function get(Request $request)
    {
        $user = JWTAuth::user();
        $id_atg_members = $user->id; // Lấy id của thành viên từ người dùng hiện tại
        
        $registration = RegisterClass::where('id_atg_members', $id_atg_members)->first();
    
        if ($registration) {
            return response()->json($registration, Response::HTTP_OK);
        } else {
            return response()->json(null, Response::HTTP_NOT_FOUND);
        }
    }


  
}

