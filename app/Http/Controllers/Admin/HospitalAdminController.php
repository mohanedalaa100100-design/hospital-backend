<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
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
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('uploads/hospitals'), $imageName);
                $validated['image_url'] = asset('uploads/hospitals/' . $imageName);
            }

            
            $hospital = Hospital::create($validated);

            
            if ($request->has('specialties')) {
                foreach ($request->specialties as $specialtyName) {
                    $hospital->specialties()->create([
                        'name' => $specialtyName,
                    
                    ]);
                }
            }

            
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

  
}