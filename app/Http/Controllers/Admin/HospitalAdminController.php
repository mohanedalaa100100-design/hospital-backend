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
    
    public function index(Request $request)
    {
        $query = Hospital::with(['specialties', 'medicalServices', 'doctors']);

        
        if ($request->has('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        
        if ($request->has('featured')) {
            $query->where('is_featured', $request->featured);
        }

        return response()->json([
            'status' => true,
            'total'  => Hospital::count(),
            'data'   => $query->paginate(10)
        ], 200);
    }

    
    public function show($id)
    {
        $hospital = Hospital::with(['specialties', 'medicalServices', 'doctors'])->find($id);

        if (!$hospital) {
            return response()->json([
                'status'  => false,
                'message' => 'Hospital not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $hospital
        ], 200);
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:hospitals',
            'address'     => 'required|string',
            'phone'       => 'nullable|string',
            'whatsapp'    => 'nullable|string',
            'type'        => 'nullable|string',
            'rating'      => 'nullable|numeric',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'lat'         => 'nullable|numeric',
            'lng'         => 'nullable|numeric',
            'is_featured' => 'nullable|boolean', 
            'specialties' => 'nullable|array', 
            'services'    => 'nullable|array'
        ]);

        return DB::transaction(function () use ($request, $validated) {
            
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/hospitals'), $imageName);  
                $validated['image_url'] = 'images/hospitals/' . $imageName;
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
                            'specialty_id'     => $specialty->id,
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
                'message' => 'Hospital created successfully',
                'hospital' => $hospital->load(['specialties', 'medicalServices', 'doctors'])
            ], 201);
        });
    }

    
    public function update(Request $request, $id)
    {
        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response()->json(['status' => false, 'message' => 'Hospital not found'], 404);
        }

        $validated = $request->validate([
            'name'        => 'nullable|string|unique:hospitals,name,' . $id,
            'address'     => 'nullable|string',
            'phone'       => 'nullable|string',
            'whatsapp'    => 'nullable|string',
            'rating'      => 'nullable|numeric',
            'is_featured' => 'nullable|boolean',
            'specialties' => 'nullable|array',
        ]);

        $hospital->update($validated);

        if ($request->has('specialties')) {
            $hospital->specialties()->sync($request->specialties);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Hospital updated successfully',
            'data'    => $hospital->load(['specialties', 'medicalServices'])
        ], 200);
    }

    
    public function destroy($id)
    {
        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response()->json(['status' => false, 'message' => 'Hospital not found'], 404);
        }

        $hospital->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Hospital deleted successfully'
        ], 200);
    }
}