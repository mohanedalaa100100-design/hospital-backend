<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\HeroSection;
use App\Models\QuickAction;
use App\Models\Specialty;

class HomeController extends Controller
{
   
    public function index()
    {
        try {
            $specialties = Specialty::withCount('clinics')->get();

            $data = [
                'hero_section'       => HeroSection::all(),
                'quick_actions'      => QuickAction::all(),
                'specialties'        => $specialties,
                'featured_hospitals' => Hospital::where('is_featured', true)
                                            ->where('is_active', true)
                                            ->with(['clinics.specialty', 'medicalServices']) 
                                            ->take(10) 
                                            ->get()
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Home page data retrieved successfully',
                'data'    => $data
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    
    public function allHospitals()
    {
        try {
            $hospitals = Hospital::where('is_active', true)
                                ->with(['clinics.specialty', 'medicalServices']) 
                                ->paginate(10); 

            return response()->json([
                'status' => true, 
                'data'   => $hospitals
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    
    public function search(Request $request)
    {
        try {
            $queryText = $request->get('query');

            if (!$queryText) {
                return response()->json(['status' => false, 'message' => 'برجاء إدخال كلمة للبحث'], 400, [], JSON_UNESCAPED_SLASHES);
            }

            $hospitals = Hospital::where('is_active', true)
                        ->where(function($q) use ($queryText) {
                            $q->where('name', 'LIKE', "%{$queryText}%")
                              ->orWhere('address', 'LIKE', "%{$queryText}%")
                              ->orWhereHas('clinics.specialty', function($sq) use ($queryText) {
                                  $sq->where('name', 'LIKE', "%{$queryText}%");
                              });
                        })
                        ->with(['clinics.specialty', 'medicalServices'])
                        ->paginate(10);

            return response()->json([
                'status' => true, 
                'data'   => $hospitals
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

   
    public function show($id)
    {
        try {
            $hospital = Hospital::with(['clinics.specialty', 'clinics.doctors', 'medicalServices'])->find($id);

            if (!$hospital) {
                return response()->json(['status' => false, 'message' => 'المستشفى غير موجودة'], 404, [], JSON_UNESCAPED_SLASHES);
            }

            return response()->json([
                'status' => true, 
                'data'   => $hospital
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

   
    public function allSpecialties()
    {
        try {
            $specialties = Specialty::withCount('clinics')->get();
            return response()->json([
                'status' => true, 
                'data'   => $specialties
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    
    public function showSpecialty($id)
    {
        try {
            $specialty = Specialty::with(['clinics.hospital' => function($q) {
                $q->where('is_active', true);
            }, 'clinics.doctors'])->find($id);

            if (!$specialty) {
                return response()->json(['status' => false, 'message' => 'التخصص غير موجود'], 404, [], JSON_UNESCAPED_SLASHES);
            }

            return response()->json([
                'status' => true, 
                'data'   => $specialty
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }

    
    public function findNearest(Request $request)
    {
        try {
            $userLat = $request->lat;
            $userLng = $request->lng;

            if (!$userLat || !$userLng) {
                return response()->json(['status' => false, 'message' => 'Coordinates required'], 400, [], JSON_UNESCAPED_SLASHES);
            }

            $nearestHospitals = Hospital::selectRaw("*,
                (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$userLat, $userLng, $userLat])
                ->where('is_active', true)
                ->with(['clinics.specialty', 'medicalServices'])
                ->orderBy('distance')
                ->paginate(10)
                ->through(function($hospital) {
                    $hospital->distance_km = round($hospital->distance, 1) . ' km';
                    $hospital->eta_minutes = round(($hospital->distance / 30) * 60) . ' min';
                    return $hospital;
                });

            return response()->json([
                'status' => true, 
                'data'   => $nearestHospitals
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500, [], JSON_UNESCAPED_SLASHES);
        }
    }
}