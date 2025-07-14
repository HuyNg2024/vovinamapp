<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\table_atg_members;
use App\Models\Club;
use App\Models\ClubRegisterTracker;
class UploadService
{
    public function uploadMemberAvatar($file, table_atg_members $user)
    {
        //slug:xóa các ký tự đặc biệt or khoảng trắng
        $safeName = Str::slug($user->username) . '-' . $user->id;
        $fileName = $safeName . '.' . $file->getClientOriginalExtension();
        
        $file->storeAs('', $fileName, 'member_avatars');
        return env('APP_URL') . "/public/upload/member/{$fileName}";
        //return 'https://app.giasuthethao.com' . "/upload/member/{$fileName}";
    }

    public function uploadImageClub($photoFile, $tenkhongdau, Club $club) 
    {
        if ($photoFile) { 
            $safeName = Str::slug($tenkhongdau) . '-' . $club->id; 
            $fileName = $safeName . '.' . $photoFile->getClientOriginalExtension();

            $photoFile->storeAs('', $fileName, 'club_images');
            return env('APP_URL') . "/public/upload/club/{$fileName}"; 
        }

        return null;
    }

    public function uploadFile($file, $directory)
    {
        
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $safeName . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($directory, $fileName, 'public');

        return env('APP_URL') . "/public/upload/{$directory}/{$fileName}";
    }

    public function uploadExamRegistrationFile($file)
    {
       
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = $safeName . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        
        $file->storeAs('', $fileName, 'exam-registration');
        
        
        return env('APP_URL') . "/public/upload/exam-registration/{$filename}";
    }



}
