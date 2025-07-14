<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Club;
use App\Services\FirebaseStorageService;
use Illuminate\Support\Facades\Log;

class ClubFileUploadController extends Controller
{
    protected $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->firebaseStorage = $firebaseStorage;
    }

    public function uploadClubImage(Request $request, $clubId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $club = Club::find($clubId);

        if (!$club) {
            return response()->json(['error' => 'Không tìm thấy câu lạc bộ'], 404);
        }

        if ($request->hasFile('image')) {
            try {
                // Upload ảnh lên Firebase Storage
                $url = $this->firebaseStorage->uploadImage($request->file('image'), 'club_images');

                // Lưu URL ảnh vào database
                $club->img = $url;
                $club->save();

                return response()->json(['image_url' => $url, 'message' => 'Hình ảnh của câu lạc bộ đã được tải lên thành công!']);
            } catch (\Exception $e) {
                Log::error('Lỗi khi tải lên hình ảnh câu lạc bộ', ['error' => $e->getMessage()]);
                return response()->json(['error' => 'Lỗi khi tải lên hình ảnh'], 500);
            }
        }

        return response()->json(['error' => 'Không có tệp nào được tải lên'], 400);
    }
}
