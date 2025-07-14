<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
// use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use App\Services\UploadService; 
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth; // Thêm khai báo JWTAuth
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory; 
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use App\Models\News; 
use App\Models\Club;
use App\Models\District; 
use App\Models\table_atg_members;
use App\Models\EducationGrades; 
use App\Models\KetQuaThi;



class RegisterExamController extends Controller // Đăng ký thi lên đai
{
    protected $uploadService;

    public function __construct(UploadService $uploadService) 
    {
        $this->uploadService = $uploadService;
    }


    public function createExamRegistrationConfirmation(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['error' => 'Thiếu hoặc sai token JWT. Vui lòng đăng nhập lại.'], 401);
        }

        $club = $user->club;
        if (!$club) {
            return response()->json(['error' => 'Bạn chưa tham gia câu lạc bộ nào.'], 400);
        }

        $news = News::where('id_club', $club->id)
            ->where('id_district', $club->id_district)
            ->first();

        if (!$news) {
            return response()->json(['error' => 'Không tìm thấy thông tin kỳ thi tại câu lạc bộ của bạn hoặc kỳ thi không thuộc quận/huyện của câu lạc bộ.'], 404);
        }

        $hasRegisteredClass = $user->registeredClasses()->exists();
        if (!$hasRegisteredClass) {
            return response()->json(['error' => 'Bạn chưa đăng ký lớp học nào.'], 400);
        }

        $existingRegistration = KetQuaThi::where('id_member', $user->id)
            ->where('id_exam', $news->id)
            ->first();

        if (!$existingRegistration) {
            return response()->json(['error' => 'Bạn chưa đăng ký thi này.'], 400);
        }

        
        $district = $news->district;
        $city = $club->city;

        
        $examGrade = EducationGrades::where('type', $user->educationGrade->type)
            ->where('order', $user->educationGrade->order + 1)
            ->first();

        
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $footer = $section->addFooter();

        $this->generateWordContent($phpWord, $section, $footer, $user, $club, $news, $district, $city, $examGrade, $existingRegistration);
        $uploadService = new UploadService();

        
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007'); 
        $tempFilePath = tempnam(sys_get_temp_dir(), 'exam_registration_'); 
        $objWriter->save($tempFilePath);

        $file = new \Illuminate\Http\UploadedFile(
            $tempFilePath, 
            'exam_registration.docx', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
            null, 
            true 
        );

        $fileUrl = $uploadService->uploadFile($file, 'exam-registration');

        
        unlink($tempFilePath);
 
         return response()->json([
             'status' => 'success',
             'message' => 'Tạo file xác nhận đăng ký thi thành công',
             'file_url' => $fileUrl 
         ]);
    }

   

    public function getNewsByClubAndType(Request $request)
    {
        try {
            // 1. Xác thực và làm sạch dữ liệu từ request
            $validator = Validator::make($request->all(), [
                'id_club' => 'required|integer|exists:table_club,id',
                'type' => 'required|string|max:255', 
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422); 
            }

            $id_club = $request->input('id_club');
            $type = $request->input('type');

            // 2. Tìm câu lạc bộ (xem xét tải trước dữ liệu quận nếu sử dụng thường xuyên)
            $club = Club::with('district:id,ten')->find($id_club);

            if (!$club) {
                return response()->json(['error' => 'Không tìm thấy câu lạc bộ'], 404);
            }

            // 3. Xác thực người dùng
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Truy cập bị từ chối. Token xác thực bị thiếu hoặc không hợp lệ.'], 401);
            }

            // 4. Kiểm tra thành viên câu lạc bộ và đăng ký lớp học
            if ($user->id_club != $id_club) {
                return response()->json(['error' => "Bạn không thuộc về câu lạc bộ này."], 403); 
            }

            $hasRegisteredClassInClub = $user->registeredClasses()
                ->whereHas('class', function ($query) use ($id_club) {
                    $query->where('id_club', $id_club);
                })
                ->exists();

            if (!$hasRegisteredClassInClub) {
                return response()->json(['error' => "Bạn chưa đăng ký lớp học nào trong câu lạc bộ này."], 403);
            }

            // 5. Lấy danh sách tin tức với bộ lọc và chuyển đổi dữ liệu
            $twoWeeksFromNow = Carbon::now()->addWeeks(2)->startOfDay();

            $filteredNewsQuery = News::where('id_club', $id_club)
                ->where('type', $type);

            // Áp dụng bộ lọc theo quận nếu câu lạc bộ có quận
            if ($club->id_district) {
                $filteredNewsQuery->where(function ($query) use ($club) {
                    $query->whereNull('id_district')
                        ->orWhere('id_district', $club->id_district);
                });
            } else {
                $filteredNewsQuery->whereNull('id_district');
            }

            $news = $filteredNewsQuery->select(
                'id', 'tenvi', 'thoigian', 
                DB::raw("CASE 
                    WHEN thoigian < CURRENT_TIMESTAMP THEN 'Đã hết kỳ thi' 
                    WHEN thoigian BETWEEN CURRENT_TIMESTAMP AND '$twoWeeksFromNow' THEN 'Sắp diễn ra'
                    WHEN thoigian > CURRENT_TIMESTAMP THEN 'Đang diễn ra' 
                    ELSE NULL 
                    END AS notification"),
                'id_district' 
            )
            ->with('district:id,ten') 
            ->where(function ($query) use ($twoWeeksFromNow) {
                $query->where('thoigian', '>=', Carbon::now()->startOfDay()) 
                    ->orWhere('thoigian', '>', $twoWeeksFromNow); 
            })
            ->get();

            // 6. Xử lý trường hợp không tìm thấy tin tức nào
            if ($news->isEmpty()) {
                $errorMessage = 'Không tìm thấy tin tức cho câu lạc bộ và loại này';
                if ($club->id_district) {
                    $errorMessage .= ', hoặc quận của tin tức không khớp với quận của câu lạc bộ';
                } else {
                    $errorMessage .= ', và câu lạc bộ chưa được chỉ định quận';
                }
                return response()->json(['error' => $errorMessage], 404);
            }

            // 7. Trả về danh sách tin tức dưới dạng JSON
            return response()->json(
                $news->map(function ($item) { 
                    $districtName = $item->district ? $item->district->ten : 'Không rõ quận';
                    $item->tenvi .= " do " . $districtName . " tổ chức"; 
                    unset($item->id_district); 
                    unset($item->district); 
                    return $item;
                })
            );

        } catch (\Exception $e) {
            Log::error('Lỗi trong phương thức getNewsByClubAndType: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi'], 500); 
        }
    }

    public function registerForExam(Request $request)
    {
        $user = JWTAuth::user(); 
        if (!$user) {
            return response()->json(['error' => 'Thiếu hoặc sai token JWT. Vui lòng đăng nhập lại.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'chieucao' => 'required|numeric',
            'cannang' => 'required|numeric',
            'id_khoathi' => 'required|integer|exists:table_news,id', // Thêm id_khoathi
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Kiểm tra xem member có id_club không
        if (!$user->id_club) {
            return response()->json(['error' => 'Bạn chưa tham gia câu lạc bộ nào.'], 400);
        }

        // Kiểm tra xem member có trong register_class không
        $hasRegisteredClass = $user->registeredClasses()->exists();
        if (!$hasRegisteredClass) {
            return response()->json(['error' => 'Bạn chưa đăng ký lớp học nào.'], 400);
        }

        // Lấy thông tin câu lạc bộ của member
        $club = $user->club;

        // Tìm kiếm id_news (id_exam) từ câu lạc bộ, kiểm tra cả id_district
        $news = News::where('id', $request->id_khoathi) // Sử dụng id_khoathi từ request
            ->where('id_club', $club->id)
            ->where('id_district', $club->id_district) 
            ->first();

        if (!$news) {
            return response()->json(['error' => 'Không tìm thấy thông tin kỳ thi tại câu lạc bộ của bạn hoặc kỳ thi không thuộc quận/huyện của câu lạc bộ.'], 404);
        }

        // Lấy thông tin cấp đai hiện tại của member
        $currentGrade = $user->educationGrade;
        if (!$currentGrade) {
            return response()->json(['error' => 'Không tìm thấy thông tin cấp đai của bạn.'], 404);
        }

        // Tìm cấp đai dự thi, chính xác là cấp đai tiếp theo của cấp đai hiện tại
        $examGrade = EducationGrades::where('type', $currentGrade->type)
            ->where('order', $currentGrade->order + 1) 
            ->first();

        if (!$examGrade) {
            return response()->json(['error' => 'Không tìm thấy cấp đai dự thi phù hợp. Đảm bảo bạn đang đăng ký đúng cấp đai tiếp theo.'], 404);
        }

        // Kiểm tra thời gian học tối thiểu của cấp đai hiện tại
        $minStudyTime = $currentGrade->thoigianhoc; // Giả sử thời gian học tối thiểu được lưu trong cột 'thoigianhoc' của bảng 'table_educationgrades'

        // Lấy ngày đăng ký lớp học gần nhất của member
        $latestRegistrationDate = $user->registeredClasses()
            ->orderBy('begin_date', 'desc')
            ->first()
            ->begin_date;

        // Tính toán thời gian đã học
        $timeStudied = Carbon::now()->diffInDays($latestRegistrationDate);

        if ($timeStudied < $minStudyTime) {
            return response()->json(['error' => 'Bạn chưa đủ thời gian học tối thiểu để đăng ký thi lên đai này.'], 400);
        }

        // Kiểm tra xem member đã đăng ký thi này chưa
        $existingRegistration = KetQuaThi::where('id_member', $user->id)
            ->where('id_exam', $news->id)
            ->exists();

        if ($existingRegistration) {
            return response()->json(['error' => 'Bạn đã đăng ký thi này rồi.'], 400);
        }

        // Tạo bản ghi mới trong bảng kết quả thi
        $bien = KetQuaThi::create([
            'id_member' => $user->id,
            'chieucao' => $request->chieucao,
            'cannang' => $request->cannang,
            'id_exam' => $news->id, 
            'id_capdaiduthi' => $examGrade->id, 
        ]);

        $link = 'https://app.giasuthethao.com/qr_payment_dai?id=' . $bien->id;
        return response()->json([
            'message' => 'Đăng ký thi thành công!',
            'link' => $link,
        ], 201);
    }



    private function generateWordContent(PhpWord $phpWord, $section, $footer, $user, $club, $news, $district, $city, $examGrade, $existingRegistration)
    {
        // Định nghĩa font chữ mặc định
        $headerFont = new Font();
        $headerFont->setName('Times New Roman'); 
        $headerFont->setSize(12); 

        $titleFont = new Font();
        $titleFont->setName('Times New Roman'); 
        $titleFont->setSize(14); 
        $titleFont->setBold(true);

        $bodyFont = new Font();
        $bodyFont->setName('Times New Roman'); 
        $bodyFont->setSize(13); 
    
        // Tạo footer
        $this->createFooter($footer, $bodyFont);
        
        // Tạo header
        $this->createHeader($section,$news,$city, $club, $titleFont, $bodyFont);
   
 
        
        $this->createPersonalInfoTable($section, $user, $city, $news, $club, $bodyFont, $examGrade);
        
        // // Tạo nội dung chính
        $this->createMainContent($section, $user, $city, $news, $bodyFont);

        // Thêm phần đính kèm và lệ phí
        $this->createAttachmentsAndFeesSection($section, $news, $city);

        // Thêm phần phụ ghi và chứng thực
        $this->createNotesAndAuthenticationSection($section, $existingRegistration, $user, $city);

        // Kiểm tra tuổi và thêm phần phụ huynh xác nhận nếu cần
        $age = Carbon::now()->diffInYears(Carbon::createFromTimestamp($user->ngaysinh));
        if ($age < 18) {
            $section->addTextBreak(2); 
            $section->addText('Phụ huynh xác nhận', ['align' => 'right']);
            $section->addText('(Ký ghi rõ họ và tên)', ['align' => 'right']);
        }
        $this->createSignatureTable($section); 

    }
    private function createImageFrame($section) {
        $table = $section->addTable([
            'borderColor' => '000000',
            'borderSize' => 6,
            'width' => 2000 * 30,  
            'align' => 'left'
        ]);
    
        $table->addRow(3000);  
        $cell = $table->addCell(null, ['valign' => 'center']);
    
        // Tạo một TextRun để căn giữa văn bản theo chiều dọc
        $textRun = $cell->addTextRun(['align' => 'center']);
        $textRun->addText('(Dán ảnh 2x3 vào đây)');
    }
    

    private function createPersonalInfoTable($section, $user, $city, $news, $club, $bodyFont, $examGrade) {
        $infoTable = $section->addTable([
            'borderColor' => 'FFFFFF', 
            'borderSize' => 6, 
            'cellMargin' => 0, 
            'cellSpacing' => 0 
        ]);
    
        // Tạo các dòng thông tin cá nhân, thêm "Tôi tên:" vào đây
        $personalInfo = [
            "Tôi tên: " => $user->ten,
            "Ngày, tháng, năm sinh: " => Carbon::createFromTimestamp($user->ngaysinh)->format('d/m/Y'),
            "Địa chỉ thường trú: " => $user->diachi,
            "Đang tập luyện tại CLB: " => $club->ten,
            "Đẳng cấp hiện nay: " => $user->educationGrade->ten,
            "Dự thi thăng cấp lên: " => $examGrade->ten,
        ];
    
        // Tạo một row chứa 2 cell: khung ảnh và thông tin cá nhân
        $infoTable->addRow();
        $cell1 = $infoTable->addCell(3000, ['valign' => 'top']); 
        $this->createImageFrame($cell1); 
    
        // Tạo nested table cho thông tin cá nhân
        $cell2 = $infoTable->addCell(7000, ['valign' => 'center']);
        $nestedTable = $cell2->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0, 'cellSpacing' => 0]);
    
        // Thêm các dòng vào nested table
        foreach ($personalInfo as $label => $value) {
            $nestedTable->addRow();
            $cell = $nestedTable->addCell(null); 
            $textrun = $cell->addTextRun();
            
            
            $dots = str_repeat('.', 30 - strlen($value)); 
            $textrun->addText($label, $bodyFont, ['bold' => true]);
            $textrun->addText($value . $dots, $bodyFont); 
        }
        
    }

  
    private function createFooter($footer, $bodyFont) {
        $nestedTable = $footer->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0]);
        $nestedTable->addRow();
    
        $cell1 = $nestedTable->addCell(20000, ['valign' => 'bottom']);
        $textRun = $cell1->addTextRun();
        $cell1->addText('PHÁT SINH BỞI APP VOVINAM FASCON', ['size' => 12, 'name' => 'Times New Roman', 'color' => 'FF0000']);
    
        $cell2 = $nestedTable->addCell(null, ['valign' => 'bottom']);
        $innerTable = $cell2->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0, 'width' => 100 * 50]);
        $innerTable->addRow();
        $innerCell = $innerTable->addCell(5000, ['valign' => 'bottom']);
        $innerCell->addPreserveText('{PAGE}', $bodyFont, ['align' => 'right']);
    }
    
    private function createHeader($section,$news,$city, $club, $titleFont, $bodyFont) {
        $table = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 50]);
        $table->addRow();
    
        $cell1 = $table->addCell(4000);
        $innerTable = $cell1->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0]);
        $innerTable->addRow();
        $innerCell = $innerTable->addCell(null, ['valign' => 'center']);
    
        $textrun1 = $innerCell->addTextRun(['align' => 'center']);
        $textrun1->addText('TT.TDTT ' . mb_strtoupper($city->ten), ['size' => 12, 'name' => 'Times New Roman']);
        $textrun1->addTextBreak(1);
        $textrun1->addText('BỘ MÔN VOVINAM', ['bold' => true, 'underline' => 'single', 'name' => 'Times New Roman', 'size' => 12, 'align' => 'center']);
        $textrun1->addTextBreak(1);
        $textrun1->addText('CLB: ' . mb_strtoupper($club->ten), ['bold' => true, 'name' => 'Times New Roman', 'align' => 'center', 'size' => 12]);
    
        $cell2 = $table->addCell(6000);
        $innerTable2 = $cell2->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0]);
        $innerTable2->addRow();
        $innerCell2 = $innerTable2->addCell(null, ['valign' => 'center', 'cellMarginLeft' => 250]);
    
        $textrun2 = $innerCell2->addTextRun(['align' => 'center']);
        $textrun2->addText('CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM', ['bold' => true, 'size' => 12, 'name' => 'Times New Roman']);
        $textrun2->addTextBreak(1);
        $textrun2->addText('Độc lập - Tự do - Hạnh phúc', ['bold' => true, 'size' => 12, 'underline' => 'single', 'name' => 'Times New Roman']);
        $textrun2->addTextBreak(1);
        $textrun2->addTextBreak(1);
        $textrun2->addText($city->ten . ', ngày ' . Carbon::now()->format('d') . ' tháng ' . Carbon::now()->format('m') . ' năm ' . Carbon::now()->format('Y'), ['size' => 12, 'name' => 'Times New Roman', 'italic' => true]);
    
        $section->addTextBreak(1);
        $section->addText('ĐƠN XIN DỰ THI THĂNG CẤP', $titleFont, ['align' => 'center', 'size' => 14]);
        // $section->addText('VOVINAM - VIỆT VÕ ĐẠO THÀNH PHỐ THỦ ĐỨC', $titleFont, ['align' => 'center', 'size' => 14]);
        // Chỉnh sửa phần tên thành phố
        // $cityName = mb_strtoupper($city->ten);
        // if (strpos($cityName, 'TP.') === 0) {
        //     $cityName = 'THÀNH PHỐ ' . substr($cityName, 4); 
        // }
        $cityName = mb_strtoupper($city->ten);
            if (strpos($cityName, 'TP.') === 0 || strpos($cityName, 'Tp.') === 0) {
                $cityName = 'THÀNH PHỐ ' . substr($cityName, strpos($cityName, '.') + 1); 
            }
        $section->addText('VOVINAM - VIỆT VÕ ĐẠO ' . $cityName, $titleFont, ['align' => 'center', 'size' => 14]);

        $section->addText($news->tenvi, $titleFont, ['align' => 'center', 'size' => 14]);
        $section->addTextBreak(1);
    
        $textrun = $section->addTextRun(['align' => 'center']);
        $cityName = ucwords($city->ten);
        $textrun->addText('Kính gửi: Bộ Môn Vovinam - Việt Võ Đạo ' . $cityName, ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'italic' => true, 'color' => '000000']);

        // $textrun->addText('Kính gửi: Bộ Môn Vovinam - Việt Võ Đạo ' . $city->ten, ['name' => 'Times New Roman', 'size' => 14, 'bold' => true, 'italic' => true, 'color' => '000000']);
    }

    private function createMainContent($section, $user, $city, $news, $bodyFont) {
        $section->addTextBreak(1);
    
        // Tạo bảng mới để chứa khung ảnh và nội dung chính
        $contentTable = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 0]);
        $contentTable->addRow();
    
        // Cell chứa khung ảnh
        $cell1 = $contentTable->addCell(3000, ['valign' => 'top']); // Điều chỉnh kích thước và căn chỉnh nếu cần
        $this->createImageFrame($cell1); 
    
        // Cell chứa nội dung chính
        $cell2 = $contentTable->addCell(7000, ['valign' => 'top']); // Điều chỉnh kích thước và căn chỉnh nếu cần
        $textrun = $cell2->addTextRun();
        $textrun->addText("Kính xin Ban tổ chức khóa thi cho tôi được tham dự kỳ thi thăng cấp Vovinam - Việt Võ Đạo " . $city->ten . " " . $news->tenvi . " được tổ chức vào ngày " . $news->thoigian->format('d/m/Y') . " tại " . $news->event_location . ".", $bodyFont);
        $textrun->addTextBreak(1); 
        $textrun->addText("Tôi xin cam kết chấp hành đúng quy chế và nội quy khóa thi.", $bodyFont);
        $section->addText("Trân trọng kính chào !", $bodyFont);
        $section->addTextBreak(1);
    }
    
    
    private function createAttachmentsAndFeesSection($section, $news, $city) {
        $section->addTextBreak(1);
        $section->addTextBreak(1);
        $section->addText('Đính kèm:', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true,'bold' => true]);
        $section->addText('- Thẻ đẳng cấp hiện tại.', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true]);
        $section->addText('- 02 ảnh 2 x 3 (Dán ảnh vào khung ).', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true]);
        $section->addText('- Lệ phí thi: ' . $news->lephithi . ' đồng (Lệ phí khóa thi + phí thẻ đẳng cấp của Liên đoàn Vovinam - Việt Võ Đạo ' . $city->ten . ').', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true]);
        $section->addTextBreak(1);
    }


    private function createNotesAndAuthenticationSection($section, $existingRegistration, $user, $city) {
        $section->addText('Phụ ghi:', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true,'bold' => true]);
        $section->addText('- Chiều cao: ' . $existingRegistration->chieucao . ', Cân nặng: ' . $existingRegistration->cannang, ['name' => 'Times New Roman', 'size' => 13, 'italic' => true]); 
        $section->addText('Chứng thực:', ['name' => 'Times New Roman', 'size' => 13, 'italic' => true,'bold' => true]);
        $section->addText('Cho phép môn sinh: ' . $user->ten . ' được dự thi khóa thi lên đai ' . $city->ten, ['name' => 'Times New Roman', 'size' => 13, 'italic' => true]); 
    }

    private function createSignatureTable($section) {
        $table = $section->addTable(['borderColor' => 'FFFFFF', 'borderSize' => 6, 'cellMargin' => 50]);
        $table->addRow();
    
        // Ô bên trái
        $cell1 = $table->addCell(6000);
        $cell1->addText('NGƯỜI DỰ THI', ['name' => 'Times New Roman', 'size' => 13, 'bold' => true, 'italic' => true]);
        $cell1->addText('(Ký ghi rõ họ và tên)', ['name' => 'Times New Roman', 'size' => 13]);
    
        // Ô bên phải
        $cell2 = $table->addCell(7000);
        $cell2->addText('XÁC NHẬN CỦA HLV TRƯỞNG CLB', ['name' => 'Times New Roman', 'size' => 13, 'bold' => true, 'align' => 'right']);
        $cell2->addText('(Ký ghi rõ họ và tên)', ['name' => 'Times New Roman', 'size' => 13, 'align' => 'right']);
    }

    public function checkRegistrationStatus(Request $request)
    {
        try {
            // 1. Xác thực người dùng
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Truy cập bị từ chối. Token xác thực bị thiếu hoặc không hợp lệ.'], 401);
            }

            // 2. Tìm đơn đăng ký thi gần nhất của người dùng
            $latestRegistration = KetQuaThi::where('id_member', $user->id)
                ->latest() // Lấy đơn đăng ký gần nhất
                ->first();

            if (!$latestRegistration) {
                return response()->json(['error' => 'Không tìm thấy đơn đăng ký thi nào.'], 404);
            }

            // 3. Kiểm tra trạng thái và trả về kết quả
            $status = $latestRegistration->tinhtrang; 
            $message = $status ? 'Đơn đăng ký đã được duyệt.' : 'Đơn đăng ký chưa được duyệt.';

            return response()->json([
                // 'status' => $status,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Lỗi trong phương thức checkRegistrationStatus: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi'], 500); 
        }
    }

    public function getAllExamsByClub(Request $request)
    {
        try {
            // 1. Xác thực và làm sạch dữ liệu từ request
            $validator = Validator::make($request->all(), [
                'id_club' => 'required|integer|exists:table_club,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422); 
            }

            $id_club = $request->input('id_club');

            // 2. Tìm câu lạc bộ
            $club = Club::with('district:id,ten')->find($id_club);

            if (!$club) {
                return response()->json(['error' => 'Không tìm thấy câu lạc bộ'], 404);
            }

            // 3. Xác thực người dùng
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Truy cập bị từ chối. Token xác thực bị thiếu hoặc không hợp lệ.'], 401);
            }

            // 4. Kiểm tra thành viên câu lạc bộ và đăng ký lớp học
            if ($user->id_club != $id_club) {
                return response()->json(['error' => "Bạn không thuộc về câu lạc bộ này."], 403); 
            }

            $hasRegisteredClassInClub = $user->registeredClasses()
                ->whereHas('class', function ($query) use ($id_club) {
                    $query->where('id_club', $id_club);
                })
                ->exists();

            if (!$hasRegisteredClassInClub) {
                return response()->json(['error' => "Bạn chưa đăng ký lớp học nào trong câu lạc bộ này."], 403);
            }

            // 5. Lấy tất cả các kỳ thi thuộc câu lạc bộ
            $twoWeeksFromNow = Carbon::now()->addWeeks(2)->startOfDay();

            $examsQuery = News::where('id_club', $id_club)
                ->where('type', 'khoa-thi'); // Giả sử type "khoa-thi" là loại tin tức về kỳ thi

            // Áp dụng bộ lọc theo quận nếu câu lạc bộ có quận
            if ($club->id_district) {
                $examsQuery->where(function ($query) use ($club) {
                    $query->whereNull('id_district')
                        ->orWhere('id_district', $club->id_district);
                });
            } else {
                $examsQuery->whereNull('id_district');
            }

            $exams = $examsQuery->select(
                'id', 'tenvi', 'thoigian', 
                DB::raw("CASE 
                    WHEN thoigian < CURRENT_TIMESTAMP THEN 'Đã hết kỳ thi' 
                    WHEN thoigian BETWEEN CURRENT_TIMESTAMP AND '$twoWeeksFromNow' THEN 'Sắp diễn ra'
                    WHEN thoigian > CURRENT_TIMESTAMP THEN 'Đang diễn ra' 
                    ELSE NULL 
                    END AS notification"),
                'id_district' 
            )
            ->with('district:id,ten') 
            ->where(function ($query) use ($twoWeeksFromNow) {
                $query->where('thoigian', '>=', Carbon::now()->startOfDay()) 
                    ->orWhere('thoigian', '>', $twoWeeksFromNow); 
            })
            ->get();

            // 6. Xử lý trường hợp không tìm thấy kỳ thi nào
            if ($exams->isEmpty()) {
                $errorMessage = 'Không tìm thấy kỳ thi cho câu lạc bộ này';
                if ($club->id_district) {
                    $errorMessage .= ', hoặc quận của kỳ thi không khớp với quận của câu lạc bộ';
                } else {
                    $errorMessage .= ', và câu lạc bộ chưa được chỉ định quận';
                }
                return response()->json(['error' => $errorMessage], 404);
            }

            // 7. Trả về danh sách kỳ thi dưới dạng JSON
            return response()->json(
                $exams->map(function ($item) { 
                    $districtName = $item->district ? $item->district->ten : 'Không rõ quận';
                    $item->tenvi .= " do " . $districtName . " tổ chức"; 
                    unset($item->id_district); 
                    unset($item->district); 
                    return $item;
                })
            );

        } catch (\Exception $e) {
            Log::error('Lỗi trong phương thức getAllExamsByClub: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi'], 500); 
        }
    }

    public function cancelRegistration(Request $request)
    {
        try {
          
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['error' => 'Truy cập bị từ chối. Token xác thực bị thiếu hoặc không hợp lệ.'], 401);
            }

           
            $validator = Validator::make($request->all(), [
                'id_exam' => 'required|integer|exists:table_news,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            
            $registration = KetQuaThi::where('id_member', $user->id)
                ->where('id_exam', $request->id_exam)
                ->first();

            if (!$registration) {
                return response()->json(['error' => 'Không tìm thấy đăng ký thi nào của bạn cho kỳ thi này.'], 404);
            }

           
            $registration->delete();

            return response()->json(['message' => 'Hủy thành công.'], 200);

        } catch (\Exception $e) {
            Log::error('Lỗi trong phương thức cancelRegistration: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi trong quá trình hủy đăng ký.'], 500);
        }
    }






    
   
}
