<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialty; 
use Illuminate\Support\Facades\Storage;

class SpecialtyAdminController extends Controller
{
    /**
     * عرض التخصصات بدون تكرار مع المستشفيات التابعة لها
     */
    public function index()
    {
        // بنجيب التخصصات الفريدة وكل تخصص معاه قائمة مستشفياته
        $specialties = Specialty::with('hospitals')->get()->map(function ($item) {
            // تظبيط رابط الصورة لو مش URL كامل
            if ($item->icon_url && !filter_var($item->icon_url, FILTER_VALIDATE_URL)) {
                $item->icon_url = asset('storage/' . $item->icon_url);
            }
            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'Specialties retrieved successfully',
            'data' => $specialties
        ], 200);
    }

    /**
     * إضافة تخصص (أو استخدامه لو موجود) وربطه بمستشفى
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name'        => 'required|string',
            'icon'        => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        // 1. بندور لو التخصص موجود بنفس الاسم قبل كدة
        $specialty = Specialty::where('name', $request->name)->first();

        if (!$specialty) {
            // لو مش موجود، بنكريت واحد جديد ونخزن الصورة
            $path = $request->file('icon')->store('uploads/specialties', 'public');
            $specialty = Specialty::create([
                'name'     => $request->name,
                'icon_url' => $path // هنخزن المسار بس والـ Asset نظبطها في الـ Index أو الـ Model
            ]);
        }

        // 2. الربط في الجدول الوسيط (استخدمنا syncWithoutDetaching عشان ميحصلش تكرار للربط)
        $specialty->hospitals()->syncWithoutDetaching([$request->hospital_id]);

        return response()->json([
            'status' => true,
            'message' => 'Specialty linked to hospital successfully',
            'data' => $specialty->load('hospitals') 
        ], 201);
    }

    /**
     * حذف التخصص (فك الارتباط بالمستشفيات)
     */
    public function destroy($id)
    {
        $specialty = Specialty::find($id);
        if (!$specialty) {
            return response()->json(['status' => false, 'message' => 'Specialty not found'], 404);
        }

        // لو التخصص له صورة، بنمسحها
        if ($specialty->icon_url && !filter_var($specialty->icon_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($specialty->icon_url);
        }
        
        // حذف التخصص (الـ cascade في الداتابيز هيمسح الارتباطات في الجدول الوسيط)
        $specialty->delete();
        
        return response()->json(['status' => true, 'message' => 'Specialty deleted successfully'], 200);
    }
}