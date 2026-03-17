<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB; // ضفنا دي عشان نضمن إن الداتا تسجل صح

class HospitalAdminController extends Controller
{
    public function index()
    {
        // بنرجع المستشفيات ومعاها التخصصات والخدمات عشان الأدمن يشوف كل حاجة
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
            // التعديل هنا: بنستقبل التخصصات والخدمات كـ مصفوفة (Array)
            'specialties' => 'nullable|array',
            'services'    => 'nullable|array'
        ]);

        // استخدام Transaction عشان نضمن لو حاجة فشلت مفيش داتا تبوظ
        return DB::transaction(function () use ($request, $validated) {
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/hospitals'), $imageName);
                $validated['image_url'] = asset('uploads/hospitals/' . $imageName);
            }

            // 1. حفظ المستشفى
            $hospital = Hospital::create($validated);

            // 2. ربط التخصصات (لو مبعوتة)
            if ($request->has('specialties')) {
                foreach ($request->specialties as $specialtyName) {
                    $hospital->specialties()->create([
                        'name' => $specialtyName,
                        // هنا ممكن تحط Icon افتراضية لو عايز
                    ]);
                }
            }

            // 3. ربط الخدمات (لو مبعوتة)
            if ($request->has('services')) {
                foreach ($request->services as $serviceName) {
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

    // ... باقي الدوال (update و destroy) ممكن تسيبهم زي ما هما 
    // أو نعدل الـ update لو عايز الأدمن يضيف تخصصات جديدة لمستشفى موجودة فعلاً
}