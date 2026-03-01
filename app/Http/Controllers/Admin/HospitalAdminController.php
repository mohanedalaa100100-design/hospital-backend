<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;

class HospitalAdminController extends Controller
{
    // 1. جلب كل المستشفيات للإدارة
    public function index()
    {
        return response()->json(Hospital::all(), 200);
    }

    // 2. إضافة مستشفى جديد (مع دعم الصور واللوكيشن)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'image_url' => 'nullable|url', // رابط صورة المستشفى
            'lat' => 'nullable|numeric',    // خط العرض
            'lng' => 'nullable|numeric',    // خط الطول
            'is_featured' => 'boolean'
        ]);

        $hospital = Hospital::create($validated);
        
        return response()->json([
            'message' => 'Hospital added successfully',
            'hospital' => $hospital
        ], 201);
    }

    // 3. تعديل بيانات مستشفى موجودة
    public function update(Request $request, $id)
    {
        $hospital = Hospital::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'image_url' => 'nullable|url',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_featured' => 'boolean'
        ]);

        $hospital->update($validated);

        return response()->json([
            'message' => 'Hospital updated successfully',
            'hospital' => $hospital
        ], 200);
    }

    // 4. حذف مستشفى
    public function destroy($id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();
        
        return response()->json([
            'message' => 'Hospital deleted successfully'
        ], 200);
    }
}