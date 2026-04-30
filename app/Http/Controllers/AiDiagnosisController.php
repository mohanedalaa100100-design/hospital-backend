<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use Illuminate\Support\Facades\Http;

class AiDiagnosisController extends Controller
{
    public function diagnose(Request $request)
    {
        $request->validate([
            'lat'                => 'required|numeric',
            'lng'                => 'required|numeric',
            'symptoms'           => 'required|array',
            'pain_duration'      => 'nullable|string',
            'pain_intensity'     => 'nullable|integer|min:1|max:10',
            'pain_location'      => 'nullable|string',
            'accompanying'       => 'nullable|array',
            
            'blood_type'         => 'nullable|string',
            'chronic_diseases'   => 'nullable|array',
            'allergies'          => 'nullable|array',
            'special_condition'  => 'nullable|string',
        ]);

        try {
        
            $aiResult = $this->callPythonModel($request);

            
            $hospital = $this->getNearestHospitalBySpecialty(
                $request->lat,
                $request->lng,
                $aiResult['specialty']
            );

         
            return response()->json([
                'status' => true,
                'data'   => [
                    'urgency_level'        => $aiResult['urgency_level'],  
                    'urgency_label'        => $aiResult['urgency_label'],  
                    'specialty'            => $aiResult['specialty'],       
                    'recommendation'       => $aiResult['recommendation'],  
                    'immediate_actions'    => $aiResult['immediate_actions'],
                    'call_ambulance'       => $aiResult['call_ambulance'],   
                    'nearest_hospital'     => $hospital,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'حدث خطأ أثناء التشخيص',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

  
    private function callPythonModel(Request $request)
    {
      
      
        $pythonApiUrl = env('AI_MODEL_URL', null);

        if ($pythonApiUrl) {
          
            $response = Http::post($pythonApiUrl . '/diagnose', [
                'symptoms'          => $request->symptoms,
                'pain_duration'     => $request->pain_duration,
                'pain_intensity'    => $request->pain_intensity,
                'pain_location'     => $request->pain_location,
                'accompanying'      => $request->accompanying,
                'chronic_diseases'  => $request->chronic_diseases,
                'allergies'         => $request->allergies,
            ]);

            return $response->json();
        }

       
        return $this->basicDiagnosis($request->symptoms);
    }

  
    private function basicDiagnosis(array $symptoms)
    {
        $criticalSymptoms = [
            'severe chest pain',
            'difficulty breathing',
            'loss of consciousness',
            'severe bleeding',
            'stroke symptoms'
        ];

        $isCritical = !empty(array_intersect(
            array_map('strtolower', $symptoms),
            $criticalSymptoms
        ));

        if ($isCritical) {
            return [
                'urgency_level'     => 'HIGH',
                'urgency_label'     => 'HIGH ALERT',
                'specialty'         => 'Cardiology',
                'recommendation'    => 'Immediate medical attention is required for your symptoms.',
                'immediate_actions' => [
                    'Sit down and try to remain calm.',
                    'Do not engage in physical activity.',
                    'If you have aspirin and are not allergic, chew one full-strength tablet.',
                    'Keep your door unlocked if you are alone so help can enter.',
                ],
                'call_ambulance' => true,
            ];
        }

        return [
            'urgency_level'     => 'MEDIUM',
            'urgency_label'     => 'MEDICAL ATTENTION NEEDED',
            'specialty'         => 'Internal Medicine',
            'recommendation'    => 'Please visit a doctor as soon as possible.',
            'immediate_actions' => [
                'Monitor your symptoms carefully.',
                'Rest and avoid physical exertion.',
                'Stay hydrated.',
            ],
            'call_ambulance' => false,
        ];
    }

    private function getNearestHospitalBySpecialty($lat, $lng, $specialty)
    {
        $hospital = Hospital::selectRaw("*,
            (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$lat, $lng, $lat])
            ->where('is_active', true)
            ->whereHas('specialties', function($q) use ($specialty) {
                $q->where('name', 'LIKE', "%{$specialty}%");
            })
            ->with(['specialties', 'medicalServices'])
            ->orderBy('distance')
            ->first();

        if (!$hospital) {
        
            $hospital = Hospital::selectRaw("*,
                (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [$lat, $lng, $lat])
                ->where('is_active', true)
                ->with(['specialties', 'medicalServices'])
                ->orderBy('distance')
                ->first();
        }

        if (!$hospital) return null;

        $hospital->distance_km  = round($hospital->distance, 1) . ' km';
        $hospital->eta_minutes  = round(($hospital->distance / 30) * 60) . ' min drive';

        return $hospital;
    }
}