<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmergencyRequest;
use App\Models\Hospital;
use App\Models\MedicalProfile; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EmergencyRequestController extends Controller
{
    public function __construct()
    {
        
        config(['app.timezone' => 'Africa/Cairo']);
    }

    
    public function quickSend(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'note' => 'nullable|string'
        ]);

        $userLat = $request->lat;
        $userLng = $request->lng;
        $currentDay = Carbon::now()->format('l'); 
        
       
        $user = Auth::guard('sanctum')->user(); 

        // البحث عن أقرب مستشفى
        $nearestHospital = Hospital::select('id', 'name', 'lat', 'lng', 'address', 'type')
            ->selectRaw("(6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", 
            [$userLat, $userLng, $userLat])
            ->where('is_active', true)
            ->where(function($q) use ($currentDay) {
                $q->where('type', 'private')
                  ->orWhere(function($sub) use ($currentDay) {
                      $sub->where('type', 'government')
                          ->where('emergency_days', 'LIKE', "%$currentDay%");
                  });
            })
            ->orderBy('distance', 'asc')
            ->first();

        if (!$nearestHospital) {
            return response()->json([
                'status' => false, 
                'message' => 'عذراً، لا توجد مستشفيات طوارئ متاحة في نطاقك حالياً'
            ], 404);
        }

        
        $medicalProfile = $user ? $user->medicalProfile : null;

        // إنشاء طلب الاستغاثة
        $emergency = EmergencyRequest::create([
            'user_id'     => $user ? $user->id : null, 
            'hospital_id' => $nearestHospital->id,
            'user_lat'    => $userLat,
            'user_lng'    => $userLng,
            'status'      => 'pending',
            'note'        => $request->note ?? 'استغاثة طارئة',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إرسال الاستغاثة لأقرب مستشفى بنجاح',
            'data' => [
                'request_id'    => $emergency->id,
                'hospital_name' => $nearestHospital->name,
                'distance'      => round($nearestHospital->distance, 2) . ' KM',
                'user_profile'  => $medicalProfile, 
                'eta'           => round(($nearestHospital->distance / 30) * 60) . ' mins' 
            ]
        ], 201);
    }

    
    public function userRequests()
    {
        $requests = EmergencyRequest::with('hospital')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $requests
        ], 200);
    }
}