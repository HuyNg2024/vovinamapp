<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\KhoaThi;
use App\Models\KetQuaThi;
use App\Models\table_atg_members;
use App\Models\EducationGrades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class KhoaThiController extends Controller
{

    public function dangKyThi(Request $request, $id)
    {
        // 1. Validate Input (Security)
        $validator = Validator::make($request->all(), [
            'id_capdaiduthi' => 'required|exists:table_educationgrades,id_educationgrades', // Assuming your EducationGrades table has id_educationgrades as PK
            // Add more validation rules as needed (e.g., for other fields)
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // 422 Unprocessable Entity
        }

        // 2. Check if Exam Exists
        $khoaThi = KhoaThi::findOrFail($id);

        // 3. Check if User is Authenticated (Optional)
        $user = JWTAuth::user();
        if (!$user) {
            return response()->json(['message' => 'Vui lòng đăng nhập để đăng ký thi'], 401); // 401 Unauthorized
        }

        // 4. Check Eligibility (Custom Logic)
        // ... (Add your logic to check if the user is eligible to register) ...

        // 5. Check if User is Already Registered
        $daDangKy = KetQuaThi::where('id_exam', $id)
            ->where('id_member', $user->id_atg_members)
            ->exists();

        if ($daDangKy) {
            return response()->json(['message' => 'Bạn đã đăng ký kỳ thi này rồi'], 400); // 400 Bad Request
        }

        // 6. Create and Save the Registration (Complete Fields)
        $ketQuaThi = new KetQuaThi([
            'id_exam' => $id,
            'id_member' => $user->id_atg_members,
            'id_capdaiduthi' => $request->input('id_capdaiduthi'),
            'ketqua' => null, // Hoặc giá trị mặc định phù hợp
            'tinhtrang' => 0,  // Hoặc giá trị mặc định phù hợp
            // Add more fields as needed (e.g., 'ngaythi' if applicable)
        ]);

        $ketQuaThi->save();

        // return response()->json(['message' => 'Đăng ký thi thành công'], 201); // 201 Created
        return response()->json([
            'message' => 'Đăng ký thi thành công',
            'data' => $ketQuaThi->load('member')  // Include member details in response (optional)
        ], 201); 
    }
    public function index2()
    {
        $khoaThis = KhoaThi::with('club')->get(); 
        return response()->json($khoaThis);
    }

    public function index()
    {
        $khoaThi = KhoaThi::all();
        return response()->json($khoaThi);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'ngaythi' => 'required|date',
            'diadiem' => 'required|string|max:255',
            'mota' => 'nullable|string', 
            'trangthai' => 'nullable|string|max:50',
            'isactive' => 'boolean',
            'hinhanh' => 'nullable|string|max:255',
            'seotitle' => 'nullable|string|max:255',
            'seodescription' => 'nullable|string',
            'createdby' => 'nullable|string|max:50', 
            'modifiedby' => 'nullable|string|max:50', 
            'noidungthi' => 'nullable|string',
            'duongdan' => 'nullable|string|max:255',
            'id_club' => 'required|exists:clubs,id', // Ensure the club exists
        ]);

        $khoaThi = KhoaThi::create($validatedData);
        return response()->json($khoaThi, 201);
    }

    public function show($id)
    {
        $khoaThi = KhoaThi::with('club')->findOrFail($id);
        return response()->json($khoaThi);
    }

    public function show2($id)
    {
        $khoaThi = KhoaThi::findOrFail($id);
        return response()->json($khoaThi);
    }


    public function update(Request $request, $id)
    {
        $khoaThi = KhoaThi::findOrFail($id);

        // Validation rules (similar to store, but adjusted for updates)
        $validatedData = $request->validate([
            'title' => 'sometimes|string|max:255', // Allow updates if provided
            'ngaythi' => 'sometimes|date',
            'diadiem' => 'sometimes|string|max:255',
            'mota' => 'nullable|string',  // No max length restriction for text fields
            'trangthai' => 'sometimes|string|max:50',
            'isactive' => 'sometimes|boolean',
            'hinhanh' => 'sometimes|string|max:255',
            'seotitle' => 'nullable|string|max:255',
            'seodescription' => 'nullable|string', 
            'modifiedby' => 'nullable|string|max:50', 
            'noidungthi' => 'nullable|string',
            'duongdan' => 'nullable|string|max:255',
            'id_club' => 'sometimes|exists:clubs,id', 
        ]);

        $khoaThi->update($validatedData);

        // Optionally, refresh the model to get updated relationships (if any)
        $khoaThi->refresh();

        return response()->json($khoaThi);
    }


    public function destroy($id)
    {
        $khoaThi = KhoaThi::findOrFail($id);
        $khoaThi->delete();
        return response()->json(null, 204);
    }
}
