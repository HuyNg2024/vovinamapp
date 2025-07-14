<?php

namespace App\Http\Controllers\Api;

use App\Models\EducationGrades;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EducationGradesController extends Controller
{
    public function index2()
    {
        $educationGrades = EducationGrades::all();
        return response()->json($educationGrades);
    }

    public function index3(Request $request)
    {
        $lang = $request->query('lang', 'vi'); 

        $educationGrades = EducationGrades::all();

        
        $data = $educationGrades->map(function ($grade) use ($lang) {
            return [
                'code' => $grade->code,
                'ten' => ($lang === 'en') ? $grade->tenen : $grade->ten,
                'order' => $grade->order,
                'type' => $grade->type,
                'level' => $grade->level,
                'column1' => $grade->column1,
                'column2' => $grade->column2,
                'column3' => $grade->column3,
                'date' => $grade->date,
                'mau_dai' => $grade->mau_dai,
                'danh_xung' => $grade->danh_xung,
                'hinhanh' => $grade->hinhanh,
                'mo_ta_chi_tiet' => $grade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($data);
    }

    public function index4(Request $request)
    {
        $lang = $request->query('lang', 'vi'); 

        $educationGrades = EducationGrades::all();

        $data = $educationGrades->map(function ($grade) use ($lang) {
            return [
                'code' => $grade->code,
                'ten' => ($lang === 'en') ? $grade->tenen : $grade->ten,
                'order' => $grade->order,
                'type' => $grade->type,
                'level' => $grade->level,
                'column1' => $grade->column1,
                'column2' => $grade->column2,
                'column3' => $grade->column3,
                'date' => $grade->date,
                'mau_dai' => $grade->mau_dai,
                'danh_xung' => $grade->danh_xung,
                'hinhanh' => $grade->hinhanh,
                'mo_ta_chi_tiet' => ($lang === 'en' && $grade->mo_ta_chi_tiet_en) ? $grade->mo_ta_chi_tiet_en : $grade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($data);
    }

    public function index(Request $request)
    {
        $lang = $request->query('lang', 'vi'); 

        $educationGrades = EducationGrades::all();

        $data = $educationGrades->map(function ($grade) use ($lang) {
            return [
                'id' => $grade->id,
                'code' => $grade->code,
                'ten' => ($lang === 'en') ? $grade->tenen : $grade->ten,
                'order' => $grade->order,
                'type' => $grade->type,
                'level' => $grade->level,
                'column1' => $grade->column1,
                'column2' => $grade->column2,
                'column3' => $grade->column3,
                'date' => $grade->date,
                'mau_dai' => ($lang === 'en' && $grade->mau_dai_en) ? $grade->mau_dai_en : $grade->mau_dai,
                'danh_xung' => ($lang === 'en' && $grade->danh_xung_en) ? $grade->danh_xung_en : $grade->danh_xung,
                'hinhanh' => $grade->hinhanh,
                'mo_ta_chi_tiet' => ($lang === 'en' && $grade->mo_ta_chi_tiet_en) ? $grade->mo_ta_chi_tiet_en : $grade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($data);
    }


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string',
            'ten' => 'required|string',
            'order' => 'nullable|integer',
            'type' => 'nullable|string',
            'level' => 'nullable|string',
            'date' => 'nullable|date',
        ]);

        $educationGrade = EducationGrades::create($validatedData);
        return response()->json($educationGrade, 201);
    }

    public function show($id)
    {
        $educationGrade = EducationGrades::findOrFail($id);
        return response()->json($educationGrade);
    }

    public function update(Request $request, $id)
    {
        $educationGrade = EducationGrades::findOrFail($id);

        $validatedData = $request->validate([
            'code' => 'string',
            'ten' => 'string',
            'order' => 'integer',
            'type' => 'string',
            'level' => 'string',
            'date' => 'date',
        ]);

        $educationGrade->update($validatedData);
        return response()->json($educationGrade);
    }

    public function destroy($id)
    {
        $educationGrade = EducationGrades::findOrFail($id);
        $educationGrade->delete();
        return response()->json(null, 204);
    }

    public function showBeltInfo(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',
            'code' => 'nullable|string',
            'ten' => 'nullable|string',
            'level' => 'nullable|string', 
            'mau_dai' => 'nullable|string',
            'danh_xung' => 'nullable|string',
            'hinhanh' => 'nullable|string',
            'mo_ta_chi_tiet' => 'nullable|string',
        ]);

        $lang = $request->query('lang', 'vi'); 

        $query = EducationGrades::query();

        foreach ($validatedData as $field => $value) {
            if ($value) {
                if($field == 'id'){
                    $query->where($field, $value); 
                }else{
                    $query->where($field, 'like' , '%' . $value . '%');
                }
            }
        }

        $educationGrades = $query->get();

        $beltInfo = $educationGrades->map(function ($educationGrade) use ($lang) {
            return [
                'id' => $educationGrade->id,
                'code' => $educationGrade->code,
                'ten' => ($lang === 'en') ? $educationGrade->tenen : $educationGrade->ten,
                'level' => $educationGrade->level,
                'mau_dai' => ($lang === 'en' && $educationGrade->mau_dai_en) ? $educationGrade->mau_dai_en : $educationGrade->mau_dai,
                'danh_xung' => ($lang === 'en' && $educationGrade->danh_xung_en) ? $educationGrade->danh_xung_en : $educationGrade->danh_xung,
                'hinhanh' => $educationGrade->hinhanh,
                'mo_ta_chi_tiet' => ($lang === 'en' && $educationGrade->mo_ta_chi_tiet_en) ? $educationGrade->mo_ta_chi_tiet_en : $educationGrade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($beltInfo);
    }
    public function showBeltInfo4(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',
            'code' => 'nullable|string',
            'ten' => 'nullable|string',
            'level' => 'nullable|string', 
            'mau_dai' => 'nullable|string',
            'danh_xung' => 'nullable|string',
            'hinhanh' => 'nullable|string',
            'mo_ta_chi_tiet' => 'nullable|string',
        ]);
    
        $lang = $request->query('lang', 'vi'); 
    
        $query = EducationGrades::query();
    
        foreach ($validatedData as $field => $value) {
            if ($value) {
                if($field == 'id'){
                    $query->where($field, $value); 
                }else{
                    $query->where($field, 'like' , '%' . $value . '%');
                }
            }
        }
    
        $educationGrades = $query->get();
    
        $beltInfo = $educationGrades->map(function ($educationGrade) use ($lang) {
            return [
                'code' => $educationGrade->code,
                'ten' => ($lang === 'en') ? $educationGrade->tenen : $educationGrade->ten,
                'level' => $educationGrade->level,
                'mau_dai' => $educationGrade->mau_dai,
                'danh_xung' => $educationGrade->danh_xung,
                'hinhanh' => $educationGrade->hinhanh,
                'mo_ta_chi_tiet' => ($lang === 'en' && $educationGrade->mo_ta_chi_tiet_en) ? $educationGrade->mo_ta_chi_tiet_en : $educationGrade->mo_ta_chi_tiet,
            ];
        });
    
        return response()->json($beltInfo);
    }

    public function showBeltInfo3(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',
            'code' => 'nullable|string',
            'ten' => 'nullable|string',
            'level' => 'nullable|string', 
            'mau_dai' => 'nullable|string',
            'danh_xung' => 'nullable|string',
            'hinhanh' => 'nullable|string',
            'mo_ta_chi_tiet' => 'nullable|string',
        ]);

        $lang = $request->query('lang', 'vi'); 

        $query = EducationGrades::query();

        
        foreach ($validatedData as $field => $value) {
            if ($value) {
                if($field == 'id'){
                    $query->where($field, $value); 
                }else{
                    $query->where($field, 'like' , '%' . $value . '%');
                }
            }
        }

        
        $educationGrades = $query->get();

       
        $beltInfo = $educationGrades->map(function ($educationGrade) use ($lang) {
            return [
                'code' => $educationGrade->code,
                'ten' => ($lang === 'en') ? $educationGrade->tenen : $educationGrade->ten,
                'level' => $educationGrade->level,
                'mau_dai' => $educationGrade->mau_dai,
                'danh_xung' => $educationGrade->danh_xung,
                'hinhanh' => $educationGrade->hinhanh,
                'mo_ta_chi_tiet' => $educationGrade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($beltInfo);
    }

    public function showBeltInfo2(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer',  
            'code' => 'nullable|string',  
            'ten' => 'nullable|string',   
            'level' => 'nullable|string', 
            'mau_dai' => 'nullable|string',
            'danh_xung' => 'nullable|string',
            'hinhanh' => 'nullable|string',
            'mo_ta_chi_tiet' => 'nullable|string',
        ]);

        $query = EducationGrades::query();

        // Filter by parameters if provided
        foreach ($validatedData as $field => $value) {
            if ($value) {
                if($field == 'id'){
                    $query->where($field, $value); 
                }else{
                    $query->where($field, 'like' , '%' . $value . '%');
                }
            }
        }

        // Retrieve the matching records
        $educationGrades = $query->get();

        // Map results to the desired format
        $beltInfo = $educationGrades->map(function ($educationGrade) {
            return [
                'code' => $educationGrade->code,
                'ten' => $educationGrade->ten,
                'level' => $educationGrade->level,
                'mau_dai' => $educationGrade->mau_dai,
                'danh_xung' => $educationGrade->danh_xung,
                'hinhanh' => $educationGrade->hinhanh,
                'mo_ta_chi_tiet' => $educationGrade->mo_ta_chi_tiet,
            ];
        });

        return response()->json($beltInfo);
    }

   
}
