<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialty; 
use Illuminate\Support\Facades\Storage;

class SpecialtyAdminController extends Controller
{
    /**
     * عرض كل التخصصات مع المستشفيات التابعة لها
     */
    public function index()
    {
    
        return response()->json(Specialty::with('hospitals')->get());
    }

    /**
     * إضافة تخصص جديد وربطه بمستشفى
     */
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name'        => 'required|string',
            'icon'        => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        
        $path = $request->file('icon')->store('uploads/specialties', 'public');

        
        $specialty = Specialty::create([
            'name'     => $request->name,
            'icon_url' => asset('storage/' . $path)
        ]);

        $specialty->hospitals()->attach($request->hospital_id);

        return response()->json([
            'status' => true,
            'message' => 'Specialty added and linked to hospital successfully',
            'data' => $specialty->load('hospitals') 
        ], 201);
    }

    /**
     * حذف تخصص
     */
    public function destroy($id)
    {
        $specialty = Specialty::find($id);
        if (!$specialty) {
            return response()->json(['message' => 'Specialty not found'], 404);
        }

        // حذف الصورة من التخزين
        $relativeContext = str_replace(asset('storage/'), '', $specialty->icon_url);
        Storage::disk('public')->delete($relativeContext);
        
        // الحذف من الجدول (Laravel هيمسح الروابط في الجدول الوسيط تلقائياً لو عامل cascade)
        $specialty->delete();
        
        return response()->json(['message' => 'Specialty deleted successfully']);
    }
}