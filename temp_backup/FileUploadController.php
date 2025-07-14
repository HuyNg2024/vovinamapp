<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\FirebaseStorageService;
use Tymon\JWTAuth\Facades\JWTAuth;

class FileUploadController extends Controller
{
    protected $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->firebaseStorage = $firebaseStorage;
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $url = $this->firebaseStorage->uploadImage($request->file('avatar'), 'avatars');
            
            // Lưu URL vào database
            $user = JWTAuth::user();
            $user->avatar = $url;
            $user->save();

            return response()->json(['avatar' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadOtherFiles(Request $request)
    {
        // Xử lý upload các loại file khác
    }
}