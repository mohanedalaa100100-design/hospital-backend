<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\Specialty; 
use App\Models\Doctor; 
use App\Models\Clinic; 
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB; 

class HospitalAdminController extends Controller
{
    
    public function index(Request $request)
    {
        
        $query = Hospital::with(['clinics.specialty', 'medicalServices', 'clinics.doctors']);

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
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    
    public function show($id)
    {
        $hospital = Hospital::with(['clinics.specialty', 'medicalServices', 'clinics.doctors'])->find($id);

        if (!$hospital) {
            return response()->json([
                'status'  => false,
                'message' => 'Hospital not found'
            ], 404, [], JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'status' => true,
            'data'   => $hospital
        ], 200, [], JSON_UNESCAPED_SLASHES);
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
                        ['icon_url' => 'images/specialties/default.png']
                    );
                    
                
                    $hospital->specialties()->syncWithoutDetaching([$specialty->id]);

                    
                    $clinic = Clinic::create([
                        'hospital_id'  => $hospital->id,
                        'specialty_id' => $specialty->id,
                        'name'         => "{$specialtyName} Clinic - {$hospital->name}",
                        'address'      => $hospital->address,
                        'phone'        => $hospital->phone,
                        'is_active'    => true
                    ]);

                    
                    $autoDoctors = [['is_female' => false], ['is_female' => true]];

                    foreach ($autoDoctors as $docType) {
                        Doctor::create([
                            'clinic_id'        => $clinic->id,
                            'specialty_id'     => $specialty->id,
                            'name'             => 'Dr. ' . fake()->name($docType['is_female'] ? 'female' : 'male'),
                            'title'            => 'Consultant ' . $specialtyName,
                            'experience_years' => rand(5, 20),
                            'bio'              => "Expert professional in {$specialtyName} with extensive experience at {$clinic->name}.",
                            'rating'           => 5.0,
                            'reviews_count'    => 0,
                            'consultation_fee' => rand(200, 600),
                            'image'            => $docType['is_female'] ? $femaleAvatar : $maleAvatar,
                            'available_slots'  => ['09:00 AM', '11:30 AM', '03:00 PM', '06:00 PM', '08:00 PM'],
                            'working_days'     => ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                            'is_available'     => true
                        ]);
                    }
                }
            }

            
            if ($request->has('services')) {
                foreach ($request->services as $serviceName) {
                    $hospital->medicalServices()->create(['name' => $serviceName]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Hospital, Clinics, and Doctors created successfully',
                'hospital' => $hospital->load(['clinics.specialty', 'medicalServices', 'clinics.doctors'])
            ], 201, [], JSON_UNESCAPED_SLASHES);
        });
    }

    
    public function update(Request $request, $id)
    {
        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response()->json(['status' => false, 'message' => 'Hospital not found'], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $validated = $request->validate([
            'name'        => 'nullable|string|unique:hospitals,name,' . $id,
            'address'     => 'nullable|string',
            'phone'       => 'nullable|string',
            'whatsapp'    => 'nullable|string',
            'rating'      => 'nullable|numeric',
            'is_featured' => 'nullable|boolean',
        ]);

        $hospital->update($validated);

        return response()->json([
            'status'  => true,
            'message' => 'Hospital updated successfully',
            'data'    => $hospital->load(['clinics.specialty', 'medicalServices'])
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    
    public function destroy($id)
    {
        $hospital = Hospital::find($id);

        if (!$hospital) {
            return response()->json(['status' => false, 'message' => 'Hospital not found'], 404, [], JSON_UNESCAPED_SLASHES);
        }

        $hospital->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Hospital and all related data deleted successfully'
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}