<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UploadService;
use Tymon\JWTAuth\Facades\JWTAuth;

class UploadController extends Controller
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $user = JWTAuth::user();
            $url = $this->uploadService->uploadMemberAvatar($request->file('avatar'), $user);
            
            $user->avatar = $url;
            $user->save();

            return response()->json(['avatar' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}