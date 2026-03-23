<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use App\Models\herosection;
use App\Models\QuickAction;

class HomeController extends Controller
{
    
    public function index()
    {
        return response()->json([
            'status' => true,
            'message' => 'Home page data retrieved successfully',
            'data' => [
                'hero_section'       => herosection::all(), // جلب كل السلايدز للـ Carousel
                'quick_actions'      => QuickAction::all(),
                'featured_hospitals' => Hospital::with(['specialties'])
                                        ->where('is_featured', true)
                                        ->take(6)
                                        ->get()
            ]
        ], 200);
    }

    
    public function show($id)
    {
        // بنجيب المستشفى بكل العلاقات اللي الفرونت محتاجها في شاشة واحدة
        $hospital = Hospital::with(['specialties', 'medicalServices', 'doctors'])
                            ->find($id);

        if (!$hospital) {
            return response()->json([
                'status' => false, 
                'message' => 'المستشفى غير موجودة'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Hospital details retrieved successfully',
            'data' => $hospital
        ], 200);
    }

    
    public function search(Request $request)
    {
        $query = $request->get('query');

        if (!$query) {
            return response()->json(['status' => false, 'message' => 'برجاء إدخال كلمة للبحث'], 400);
        }

        $hospitals = Hospital::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%")
                    ->orWhereHas('specialties', function($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    })
                    ->with(['specialties', 'medicalServices'])
                    ->get();

        return response()->json([
            'status' => true,
            'count'  => $hospitals->count(),
            'data'   => $hospitals
        ], 200);
    }

    
    public function findNearest(Request $request)
    {
        $userLat = $request->lat;
        $userLng = $request->lng;

        if (!$userLat || !$userLng) {
            return response()->json([
                'status' => false, 
                'message' => 'Coordinates (lat, lng) are required'
            ], 400);
        }

        
        $nearestHospitals = Hospital::selectRaw("*, 
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->with(['specialties']) 
            ->orderBy('distance')
            ->take(50) 
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Nearest hospitals retrieved successfully',
            'data' => $nearestHospitals
        ], 200);
    }

    
    public function allHospitals()
    {
        $hospitals = Hospital::with(['specialties', 'medicalServices'])->get();
        
        return response()->json([
            'status' => true,
            'count' => $hospitals->count(),
            'data' => $hospitals
        ], 200);
    }
}