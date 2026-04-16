<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\Specialty; 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB; 

class HospitalAdminController extends Controller
{
    public function index()
    {
        return response()->json(Hospital::with(['specialties', 'medicalServices'])->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_featured' => 'boolean',
            'specialties' => 'nullable|array',
            'services'    => 'nullable|array'
        ]);

        return DB::transaction(function () use ($request, $validated) {
            
            // رفع الصورة وتخزين المسار
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/hospitals'), $imageName);
                $validated['image_url'] = 'uploads/hospitals/' . $imageName;
            }

            // إنشاء المستشفى
            $hospital = Hospital::create($validated);

            // 1. ربط التخصصات (نظام Many-to-Many صح)
            if ($request->has('specialties')) {
                foreach ($request->specialties as $specialtyName) {
                    // ابحث عن التخصص بالاسم، لو موجود هاته، لو مش موجود كريته
                    $specialty = Specialty::firstOrCreate(
                        ['name' => $specialtyName],
                        ['icon_url' => 'default_icon.png'] // أيقونة افتراضية
                    );
                    
                    // اربط المستشفى بالتخصص في الجدول الوسيط (البيفوت)
                    $hospital->specialties()->syncWithoutDetaching([$specialty->id]);
                }
            }

            // 2. إضافة الخدمات الطبية
            if ($request->has('services')) {
                foreach ($request->services as $serviceName) {
                    // بنفترض هنا إن الخدمات تابعة لكل مستشفى لوحدها One-to-Many
                    $hospital->medicalServices()->create([
                        'name' => $serviceName,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'تم إضافة المستشفى وتخصصاتها وخدماتها بنجاح',
                'hospital' => $hospital->load(['specialties', 'medicalServices'])
            ], 201);
        });
    }
}