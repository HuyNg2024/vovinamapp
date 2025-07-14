<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\EducationGrades;
use App\Models\News;
use App\Models\Product;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Services\FirebaseStorageService;

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
            'avatar' => 'required|image|mimes:jpeg,jpg,png|max:7168',
        ]);

        if ($request->hasFile('avatar')) {
            $url = $this->firebaseStorage->uploadImage($request->file('avatar'), 'avatars');
            
            // Save URL to database
            $user = JWTAuth::user();
            $user->avatar = $url;
            $user->save();

            return response()->json(['avatar' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadOtherFiles(Request $request)
    {
        // Handle upload of other file types
    }

    public function uploadProductImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'product_id' => 'required|exists:products,ProductID'
        ]);

        if ($request->hasFile('image')) {
            $url = $this->firebaseStorage->uploadImage($request->file('image'), 'products');
            
            // Save URL to products table
            $product = Product::findOrFail($request->product_id);
            $product->link_image = $url;
            $product->save();

            return response()->json(['link_image' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadClubImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'id_club' => 'required|exists:table_club,id'
        ]);

        if ($request->hasFile('image')) {
            $url = $this->firebaseStorage->uploadImage($request->file('image'), 'clubs');
            
            // Save URL to clubs table
            $club = Club::findOrFail($request->id_club);
            $club->image = $url;
            $club->save();

            return response()->json(['link_image' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadBeltImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'id_belt' => 'required|exists:table_educationgrades,id'
        ]);

        if ($request->hasFile('image')) {
            $url = $this->firebaseStorage->uploadImage($request->file('image'), 'belts');
            
            // Save URL to clubs table
            $belt = EducationGrades::findOrFail($request->id_belt);
            $belt->hinhanh = $url;
            $belt->save();

            return response()->json(['link_image' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function uploadNewsVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,avi,flv|max:102400', // 100MB max
            'id_news' => 'required|exists:table_news,id'
        ]);

        if ($request->hasFile('video')) {
            $url = $this->firebaseStorage->uploadFile($request->file('video'), 'videos');
            
            // Save URL to database if needed
            // Save URL to table_news
            $news = News::findOrFail($request->id_belt);
            $news->link_video = $url;
            $news->save();

            return response()->json(['video_url' => $url]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
