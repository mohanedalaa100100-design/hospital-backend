<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use Illuminate\Support\Facades\File;

class HospitalAdminController extends Controller
{
    // 1. جلب كل المستشفيات للإدارة
    public function index()
    {
        return response()->json(Hospital::all(), 200);
    }

    // 2. إضافة مستشفى جديد مع دعم رفع الصور
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // بنستقبل ملف صورة
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_featured' => 'boolean'
        ]);

        // التعامل مع رفع الصورة
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/hospitals'), $imageName);
            $validated['image_url'] = asset('uploads/hospitals/' . $imageName);
        }

        $hospital = Hospital::create($validated);
        
        return response()->json([
            'status' => true,
            'message' => 'Hospital added with image successfully',
            'hospital' => $hospital
        ], 201);
    }

    // 3. تعديل بيانات مستشفى (مع إمكانية تغيير الصورة وحذف القديمة)
    public function update(Request $request, $id)
    {
        $hospital = Hospital::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_featured' => 'boolean'
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة من السيرفر لو موجودة عشان ما نملى المساحة ع الفاضي
            if ($hospital->image_url) {
                $oldPath = public_path(str_replace(asset(''), '', $hospital->image_url));
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // رفع الصورة الجديدة
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/hospitals'), $imageName);
            $validated['image_url'] = asset('uploads/hospitals/' . $imageName);
        }

        $hospital->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Hospital updated successfully',
            'hospital' => $hospital
        ], 200);
    }

    // 4. حذف مستشفى وحذف صورتها من السيرفر
    public function destroy($id)
    {
        $hospital = Hospital::findOrFail($id);
        
        if ($hospital->image_url) {
            $path = public_path(str_replace(asset(''), '', $hospital->image_url));
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $hospital->delete();
        
        return response()->json([
            'message' => 'Hospital and its image deleted successfully'
        ], 200);
    }
}