<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\Specialty; 
use App\Models\Doctor; 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB; 

class HospitalAdminController extends Controller
{
    public function index()
    {
        
        return response()->json(Hospital::with(['specialties', 'medicalServices', 'doctors'])->get(), 200);
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
                $validated['image_url'] = 'uploads/hospitals/' . $imageName;
            }

            
            $hospital = Hospital::create($validated);

            
            $maleAvatar = 'images/doctors/male_avatar.png';
            $femaleAvatar = 'images/doctors/female_avatar.png';

            
            if ($request->has('specialties')) {
                foreach ($request->specialties as $specialtyName) {
                    
                    $specialty = Specialty::firstOrCreate(
                        ['name' => $specialtyName],
                        ['icon_url' => 'default_icon.png']
                    );
                    
                    
                    $hospital->specialties()->syncWithoutDetaching([$specialty->id]);

                    
                    $autoDoctors = [
                        ['is_female' => false], 
                        ['is_female' => true],  
                    ];

                    foreach ($autoDoctors as $docType) {
                        Doctor::create([
                            'hospital_id'      => $hospital->id,
                            'specialty_id'     => $specialty->id, // الربط بالـ ID الجديد
                            'name'             => 'Dr. ' . fake()->name($docType['is_female'] ? 'female' : 'male'),
                            'title'            => 'Specialist ' . $specialtyName,
                            'experience_years' => rand(5, 15),
                            'bio'              => "Experienced specialist in {$specialtyName} at {$hospital->name}.",
                            'rating'           => 5.0,
                            'reviews_count'    => 0,
                            'consultation_fee' => rand(200, 500),
                            'image'            => $docType['is_female'] ? $femaleAvatar : $maleAvatar,
                        ]);
                    }
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
                'message' => 'تم إضافة المستشفى وتخصصاتها ودكاترتها بنجاح',
                'hospital' => $hospital->load(['specialties', 'medicalServices', 'doctors'])
            ], 201);
        });
    }
}