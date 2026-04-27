<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\HeroSection;
use App\Models\QuickAction;
use App\Models\Specialty;
use App\Models\Doctor;

class HomeController extends Controller
{
    // 1. الصفحة الرئيسية
    public function index()
    {
        try {
            $specialties = Specialty::withCount('hospitals')->get();

            $data = [
                'hero_section'       => HeroSection::all(),
                'quick_actions'      => QuickAction::all(),
                'specialties'        => $specialties,
                'featured_hospitals' => Hospital::with(['specialties', 'medicalServices'])
                                            ->where('is_featured', true)
                                            ->where('is_active', true)
                                            ->take(10) 
                                            ->get()
            ];

            return response()->json([
                'status'  => true,
                'message' => 'Home page data retrieved successfully',
                'data'    => $data
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error loading home page',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 2. عرض كل التخصصات
    public function allSpecialties()
    {
        try {
            $specialties = Specialty::withCount('hospitals')
                ->with(['doctors' => function($query) {
                    $query->where('is_available', true);
                }])->get();

            return response()->json([
                'status'  => true,
                'message' => 'Specialties with doctors and hospital counts retrieved successfully',
                'data'    => $specialties
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching specialties',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 3. عرض تخصص محدد مع بيانات المستشفى لكل دكتور
    public function showSpecialty($id)
    {
        try {
            $specialty = Specialty::withCount('hospitals')
                ->with(['doctors' => function($query) {
                    // تم التعديل لـ image_url ليتطابق مع الـ Migration الخاص بك
                    $query->where('is_available', true)->with('hospital:id,name,image_url');
                }])
                ->find($id);

            if (!$specialty) {
                return response()->json([
                    'status'  => false,
                    'message' => 'التخصص غير موجود'
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Specialty details retrieved successfully',
                'data'    => $specialty
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error fetching specialty',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // 4. البحث عن المستشفيات
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json([
                'status'  => false,
                'message' => 'برجاء إدخال كلمة للبحث'
            ], 400);
        }

        $hospitals = Hospital::where('is_active', true)
                    ->where(function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('address', 'LIKE', "%{$query}%")
                          ->orWhereHas('specialties', function($sq) use ($query) {
                              $sq->where('name', 'LIKE', "%{$query}%");
                          });
                    })
                    ->with(['specialties', 'medicalServices'])
                    ->get();

        return response()->json([
            'status' => true,
            'count'  => $hospitals->count(),
            'data'   => $hospitals
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 5. عرض كل المستشفيات
    public function allHospitals()
    {
        try {
            $hospitals = Hospital::where('is_active', true)
                                ->with(['specialties', 'medicalServices'])
                                ->get();

            return response()->json([
                'status' => true,
                'data'   => $hospitals
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    // 6. تفاصيل مستشفى محددة
    public function show($id)
    {
        $hospital = Hospital::with(['specialties', 'medicalServices', 'doctors'])->find($id);

        if (!$hospital) {
            return response()->json([
                'status'  => false,
                'message' => 'المستشفى غير موجودة'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $hospital
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // 7. أقرب المستشفيات
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

        if (!$userLat || !$userLng) {
            return response()->json([
                'status'  => false,
                'message' => 'Coordinates required'
            ], 400);
        }

        $nearestHospitals = Hospital::selectRaw("*,
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->with(['specialties', 'medicalServices'])
            ->orderBy('distance')
            ->take(10)
            ->get()
            ->map(function($hospital) {
                $hospital->distance_km     = round($hospital->distance, 1) . ' km';
                $hospital->distance_meters = round($hospital->distance * 1000) . ' m';
                $hospital->eta_minutes     = round(($hospital->distance / 30) * 60) . ' min';
                return $hospital;
            });

        return response()->json([
            'status' => true,
            'data'   => $nearestHospitals
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}