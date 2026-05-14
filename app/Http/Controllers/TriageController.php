<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Hospital;

class TriageController extends Controller
{
    /**
     * POST /api/triage
     * 
     * Request body:
     * {
     *   "lat": 30.0444,
     *   "lng": 31.2357,
     *   "conditions": ["Severe chest pain"],
     *   "age": "45",
     *   "chronic_diseases": ["Heart Disease"],
     *   "answers": {
     *     "intensity": 8,
     *     "radiating": "yes",
     *     "sweating": "yes"
     *   }
     * }
     */
    public function assess(Request $request): JsonResponse
    {
        $request->validate([
            'lat'                => 'required|numeric',
            'lng'                => 'required|numeric',
            'conditions'         => 'required|array',
            'age'                => 'nullable|string',
            'gender'             => 'nullable|string',
            'chronic_diseases'   => 'nullable|array',
            'special_conditions' => 'nullable|array',
            'answers'            => 'nullable|array',
        ]);

        $conditions        = array_map('trim', $request->input('conditions', []));
        $age               = (int) $request->input('age', 30);
        $lat               = (float) $request->input('lat');
        $lng               = (float) $request->input('lng');
        $chronicDiseases   = array_map('trim', $request->input('chronic_diseases', []));
        $specialConditions = array_map('trim', $request->input('special_conditions', []));
        $answers           = $request->input('answers', []);

        $isElderly    = $age >= 65;
        $isChild      = $age <= 12;
        $isPregnant   = $this->hasCondition($specialConditions, 'pregnant');
        $hasHeart     = $this->hasCondition($chronicDiseases, 'heart');

        
        $triageResult = $this->runTriage(
            $conditions, $answers,
            $isElderly, $isChild, $isPregnant, $hasHeart
        );

        
        $hospital = $this->getNearestHospitalBySpecialty(
            $lat, $lng, $triageResult['recommended_specialty']
        );

        if (!$hospital) {
            $hospital = $this->getNearestHospital($lat, $lng);
        }

        $triageResult['nearest_hospital'] = $hospital;

        if (!$hospital) {
            $triageResult['warning'] = 'لم نتمكن من العثور على مستشفى قريبة. تواصل مباشرة مع الإسعاف.';
        }

        return response()->json([
            'status' => true,
            'data'   => $triageResult
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    // ─────────────────────────────────────────────────────────
    //  Helper Methods
    // ─────────────────────────────────────────────────────────

   
    private function hasCondition(array $conditions, string $keyword): bool
    {
        foreach ($conditions as $condition) {
            if (stripos(strtolower($condition), strtolower($keyword)) !== false) {
                return true;
            }
        }
        return false;
    }

   
    private function findCondition(array $conditions, array $keywords): bool
    {
        foreach ($conditions as $condition) {
            $conditionLower = strtolower(trim($condition));
            foreach ($keywords as $keyword) {
                if (stripos($conditionLower, strtolower($keyword)) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    // ─────────────────────────────────────────────────────────
    //  Main Triage Engine
    // ─────────────────────────────────────────────────────────

    private function runTriage(
        array $conditions, array $answers,
        bool $isElderly, bool $isChild,
        bool $isPregnant, bool $hasHeart
    ): array {

        // ── Chest Pain ──
        if ($this->findCondition($conditions, ['chest pain', 'chest'])) {
            $intensity  = $answers['intensity'] ?? 5;
            $radiating  = ($answers['radiating'] ?? '') === 'yes';
            $sweating   = ($answers['sweating']  ?? '') === 'yes';
            $duration   = $answers['duration'] ?? '';

            $isHeartAttack = $radiating || $sweating || $intensity >= 8 || $hasHeart || $isElderly;

            if ($isHeartAttack) {
                return $this->result(
                    'Cardiology',
                    'Cardiac Center',
                    'critical',
                    true,
                    [
                        'Sit down and stay calm. Do not move or exert yourself.',
                        'Chew one aspirin tablet if you are not allergic.',
                        'Loosen tight clothing around your chest.',
                        'Keep your door unlocked so help can reach you.',
                        'Call ambulance immediately — do not drive yourself.',
                    ]
                );
            }

            return $this->result(
                'Cardiology',
                'General Hospital with Cardiac Unit',
                'urgent',
                in_array($duration, ['4-12 hours', '12+ hours']),
                [
                    'Rest and avoid physical activity.',
                    'Monitor your symptoms closely.',
                    'Head to the nearest hospital with a cardiac unit.',
                ]
            );
        }

        // ── Stroke ──
        if ($this->findCondition($conditions, ['stroke', 'weakness', 'face drooping', 'speech problem'])) {
            return $this->result(
                'Neurology',
                'Stroke Center / Neurology Hospital',
                'critical',
                true,
                [
                    'Call ambulance IMMEDIATELY — every minute matters for stroke.',
                    'Do not give the patient anything to eat or drink.',
                    'Note the exact time symptoms started — tell the doctor.',
                    'Keep the patient still and calm.',
                    'Do not give aspirin for stroke symptoms.',
                ]
            );
        }

        // ── Breathing ──
        if ($this->findCondition($conditions, ['breathing', 'shortness', 'asthma', 'suffocation'])) {
            $canSpeak = ($answers['speaking'] ?? 'yes') === 'yes';

            if (!$canSpeak || $isChild || $isElderly || $isPregnant) {
                return $this->result(
                    'Pulmonology / Emergency',
                    'Hospital with ICU',
                    'critical',
                    true,
                    [
                        'Call ambulance immediately.',
                        'Sit upright — do not lie down.',
                        'Use your inhaler if you have asthma.',
                        'Loosen tight clothing.',
                    ]
                );
            }

            return $this->result(
                'Pulmonology',
                'General Hospital',
                'urgent',
                false,
                [
                    'Sit upright and try to breathe slowly.',
                    'Avoid dusty or smoky environments.',
                    'Head to the nearest hospital.',
                ]
            );
        }

        // ── Severe Bleeding ──
        if ($this->findCondition($conditions, ['bleeding', 'hemorrhage', 'blood loss'])) {
            $controlled = ($answers['controlled'] ?? 'yes') === 'yes';
            $location   = $answers['location'] ?? '';
            $dangerous  = str_contains(strtolower($location), 'internal') || 
                         str_contains(strtolower($location), 'chest');

            if (!$controlled || $dangerous) {
                return $this->result(
                    'Emergency Surgery',
                    'Trauma Center',
                    'critical',
                    true,
                    [
                        'Apply firm pressure to the wound with a clean cloth.',
                        'Do not remove the cloth — add more on top if soaked.',
                        'Keep the injured area elevated above heart level if possible.',
                        'Call ambulance immediately.',
                    ]
                );
            }

            return $this->result(
                'Emergency',
                'General Hospital',
                'urgent',
                false,
                [
                    'Keep applying pressure to the wound.',
                    'Go to the nearest emergency room.',
                ]
            );
        }

        // ── Seizures ──
        if ($this->findCondition($conditions, ['seizure', 'convulsion', 'fit', 'epilepsy'])) {
            $ongoing    = in_array($answers['duration'] ?? '', ['Still ongoing', '5+ minutes']);
            $firstTime  = ($answers['first_time'] ?? 'no') === 'yes';

            return $this->result(
                'Neurology',
                'Neurology Hospital',
                $ongoing ? 'critical' : 'urgent',
                $ongoing || $firstTime,
                [
                    'Do not hold the person down or put anything in their mouth.',
                    'Clear the area of hard objects.',
                    'Turn the person on their side after the seizure stops.',
                    'Time the seizure — if over 5 minutes, call ambulance.',
                ]
            );
        }

        // ── Allergic Reaction ──
        if ($this->findCondition($conditions, ['allergic', 'allergy', 'anaphylaxis', 'rash', 'swelling'])) {
            return $this->result(
                'Emergency',
                'Hospital with Emergency Unit',
                'critical',
                true,
                [
                    'Use EpiPen immediately if available.',
                    'Call ambulance.',
                    'Lie down with legs elevated unless breathing is hard.',
                    'Remove or avoid the allergen if possible.',
                ]
            );
        }

        // ── Trauma ──
        if ($this->findCondition($conditions, ['trauma', 'accident', 'injury', 'fall', 'crush'])) {
            return $this->result(
                'Orthopedics / Trauma Surgery',
                'Trauma Center',
                'critical',
                true,
                [
                    'Do not move the patient — possible spinal injury.',
                    'Call ambulance immediately.',
                    'Control any visible bleeding with pressure.',
                    'Keep the patient warm and still.',
                ]
            );
        }

        // ── Loss of Consciousness ──
        if ($this->findCondition($conditions, ['unconscious', 'unconsciousness', 'unresponsive', 'collapsed'])) {
            return $this->result(
                'Emergency / ICU',
                'Hospital with ICU',
                'critical',
                true,
                [
                    'Call ambulance immediately.',
                    'Check for breathing and pulse.',
                    'If no pulse — start CPR if trained.',
                    'Do not give anything by mouth.',
                    'Put in recovery position if breathing.',
                ]
            );
        }

        // ── Confusion / Blue lips ──
        if ($this->findCondition($conditions, ['confusion', 'confused', 'disoriented', 'blue lips', 'cyanosis'])) {
            return $this->result(
                'Emergency',
                'Hospital with Emergency Unit',
                'critical',
                true,
                [
                    'Call ambulance immediately.',
                    'Keep the patient calm and still.',
                    'Monitor breathing closely.',
                ]
            );
        }

        // ── Severe Headache ──
        if ($this->findCondition($conditions, ['headache', 'migraine', 'thunderclap'])) {
            $sudden    = ($answers['onset'] ?? '') === 'Suddenly (worst of my life)';
            $neckStiff = ($answers['neck_stiffness'] ?? '') === 'yes';

            if ($sudden || $neckStiff) {
                return $this->result(
                    'Neurology',
                    'Neurology Hospital',
                    'critical',
                    true,
                    [
                        'Call ambulance immediately — could be brain bleed or meningitis.',
                        'Do not take painkillers without doctor advice.',
                        'Avoid bright light.',
                        'Keep still and calm.',
                    ]
                );
            }

            return $this->result(
                'Neurology',
                'General Hospital',
                'urgent',
                false,
                [
                    'Rest in a dark quiet room.',
                    'Avoid screens.',
                    'Go to hospital if pain worsens.',
                ]
            );
        }

        // ── High Fever ──
        if ($this->findCondition($conditions, ['fever', 'high temperature', 'hot'])) {
            $veryHigh = in_array($answers['temp'] ?? '', ['Above 41°C', '40-41°C']);
            $confused = ($answers['confusion'] ?? '') === 'yes';
            $rash     = ($answers['rash'] ?? '') === 'yes';

            if ($veryHigh || $confused || $rash || $isChild || $isElderly) {
                return $this->result(
                    'Emergency / Internal Medicine',
                    'Hospital with Emergency Unit',
                    'critical',
                    $confused,
                    [
                        'Go to ER immediately.',
                        'Apply cool damp cloth to forehead.',
                        'Take fever-reducing medication if not allergic.',
                        'Keep hydrated.',
                    ]
                );
            }

            return $this->result(
                'Internal Medicine',
                'General Hospital',
                'urgent',
                false,
                [
                    'Take paracetamol to reduce fever.',
                    'Drink plenty of fluids.',
                    'Visit hospital if fever persists over 24 hours.',
                ]
            );
        }

        // ── Abdominal Pain ──
        if ($this->findCondition($conditions, ['abdominal pain', 'belly pain', 'stomach pain', 'stomach ache'])) {
            $rigid      = ($answers['rigid'] ?? '') === 'yes';
            $intensity  = $answers['intensity'] ?? 5;
            $lowerRight = ($answers['location'] ?? '') === 'Lower right';

            if ($rigid || $intensity >= 8 || $lowerRight) {
                return $this->result(
                    'General Surgery',
                    'Hospital with Surgical Unit',
                    'critical',
                    $rigid,
                    [
                        'Do not eat or drink anything.',
                        'Go to ER immediately.',
                        'Do not take painkillers — may mask symptoms.',
                        'Lie still in a comfortable position.',
                    ]
                );
            }

            return $this->result(
                'Gastroenterology',
                'General Hospital',
                'urgent',
                false,
                [
                    'Avoid eating until evaluated.',
                    'Go to hospital.',
                    'Monitor for worsening pain.',
                ]
            );
        }

        // ── Vomiting Blood ──
        if ($this->findCondition($conditions, ['vomiting blood', 'hematemesis', 'bloody vomit'])) {
            return $this->result(
                'Gastroenterology / Emergency',
                'Hospital with Emergency Unit',
                'critical',
                true,
                [
                    'Call ambulance immediately.',
                    'Do not eat or drink.',
                    'Save a sample if possible for doctors.',
                    'Lie down and stay calm.',
                ]
            );
        }

        // ── Eye Injury ──
        if ($this->findCondition($conditions, ['eye injury', 'eye pain', 'vision loss', 'eye emergency'])) {
            $chemical = ($answers['type'] ?? '') === 'Chemical in eye';
            $vision   = ($answers['type'] ?? '') === 'Sudden vision loss';

            if ($chemical) {
                return $this->result(
                    'Ophthalmology',
                    'Eye Hospital',
                    'critical',
                    false,
                    [
                        'Flush eye with clean water for 15-20 minutes immediately.',
                        'Do not rub the eye.',
                        'Go to eye emergency immediately.',
                        'Remove contact lenses if worn.',
                    ]
                );
            }

            return $this->result(
                'Ophthalmology',
                'Eye Hospital',
                'critical',
                $vision,
                [
                    'Do not rub or press on the eye.',
                    'Cover eye loosely with clean cloth.',
                    'Go to eye hospital immediately.',
                ]
            );
        }

        // ── Back Pain ──
        if ($this->findCondition($conditions, ['back pain', 'spine pain', 'spinal pain'])) {
            $legWeakness = ($answers['legs']    ?? '') === 'yes';
            $bladder     = ($answers['bladder'] ?? '') === 'yes';

            if ($legWeakness || $bladder) {
                return $this->result(
                    'Neurosurgery / Orthopedics',
                    'Hospital with Neurosurgery Unit',
                    'critical',
                    true,
                    [
                        'Call ambulance — possible spinal emergency.',
                        'Do not move — wait for help.',
                    ]
                );
            }

            return $this->result(
                'Orthopedics',
                'General Hospital',
                'urgent',
                false,
                [
                    'Lie flat on firm surface.',
                    'Apply ice pack for 20 minutes.',
                    'Avoid lifting or bending.',
                ]
            );
        }

        // ── Burns ──
        if ($this->findCondition($conditions, ['burn', 'burnt', 'fire', 'heat injury'])) {
            $large      = in_array($answers['size'] ?? '', ['Large (chest/back)', 'Very large (multiple areas)']);
            $face       = ($answers['face']       ?? '') === 'yes';
            $electrical = ($answers['cause']      ?? '') === 'Electrical';

            if ($large || $face || $electrical) {
                return $this->result(
                    'Burns Unit',
                    'Burns Center',
                    'critical',
                    true,
                    [
                        'Call ambulance immediately.',
                        'Cool burn with cool running water for 20 minutes.',
                        'Do not use ice, butter, or toothpaste.',
                        'Cover loosely with clean wrap.',
                    ]
                );
            }

            return $this->result(
                'Burns Unit',
                'General Hospital',
                'urgent',
                false,
                [
                    'Cool with running water for 20 minutes.',
                    'Cover with clean bandage.',
                    'Go to hospital for assessment.',
                ]
            );
        }
        // ── Pregnancy ──
        if ($this->findCondition($conditions, ['pregnancy', 'pregnant', 'labor', 'delivery'])) {
            $bleeding = ($answers['bleeding']  ?? '') === 'yes';
            $movement = ($answers['movement']  ?? '') === 'yes';
            $pain     = ($answers['pain']      ?? '') === 'yes';
            $late     = in_array($answers['weeks'] ?? '', ['36+ weeks', '28-36 weeks']);

            if ($bleeding || $movement || ($pain && $late)) {
                return $this->result(
                    'Obstetrics / Gynecology',
                    'Maternity Hospital',
                    'critical',
                    true,
                    [
                        'Call ambulance immediately.',
                        'Lie on your left side.',
                        'Do not eat or drink.',
                    ]
                );
            }

            return $this->result(
                'Obstetrics / Gynecology',
                'Maternity Hospital',
                'urgent',
                false,
                [
                    'Go to maternity hospital immediately.',
                    'Lie on your left side.',
                ]
            );
        }

        // ── Diabetic Emergency ──
        if ($this->findCondition($conditions, ['diabetic', 'diabetes', 'blood sugar', 'hypoglycemia'])) {
            $lowSugar  = ($answers['sugar']     ?? '') === 'Very low (below 60)';
            $conscious = ($answers['conscious'] ?? 'yes') === 'yes';

            if (!$conscious) {
                return $this->result(
                    'Emergency / Endocrinology',
                    'Hospital with Emergency Unit',
                    'critical',
                    true,
                    [
                        'Call ambulance immediately.',
                        'Do not give anything by mouth.',
                        'Place in recovery position.',
                    ]
                );
            }

            if ($lowSugar) {
                return $this->result(
                    'Endocrinology / Emergency',
                    'General Hospital',
                    'urgent',
                    false,
                    [
                        'Give sugary drink or glucose tablets immediately.',
                        'If no improvement in 15 minutes — call ambulance.',
                        'Go to hospital for evaluation.',
                    ]
                );
            }

            return $this->result(
                'Endocrinology',
                'General Hospital',
                'urgent',
                false,
                [
                    'Go to hospital for blood sugar management.',
                    'Drink water (not sugary drinks).',
                ]
            );
        }

        // ── Poisoning ──
        if ($this->findCondition($conditions, ['poisoning', 'poison', 'overdose', 'toxic', 'intoxication'])) {
            return $this->result(
                'Toxicology / Emergency',
                'Hospital with Emergency Unit',
                'critical',
                true,
                [
                    'Call ambulance immediately.',
                    'Do not induce vomiting unless told by poison control.',
                    'Bring the substance or container to the hospital.',
                    'Monitor breathing closely.',
                ]
            );
        }

        // ── Urinary Retention ──
        if ($this->findCondition($conditions, ['urinary', 'urination', 'urine', 'bladder'])) {
            $canUrinate = ($answers['urination'] ?? 'yes') === 'yes';
            $pain       = $answers['pain'] ?? 5;

            if (!$canUrinate || $pain >= 8) {
                return $this->result(
                    'Urology',
                    'Hospital with Urology Unit',
                    'critical',
                    false,
                    [
                        'Go to ER immediately — bladder may need catheter.',
                        'Do not drink large amounts of water.',
                    ]
                );
            }

            return $this->result(
                'Urology',
                'General Hospital',
                'urgent',
                false,
                [
                    'Go to hospital for urological evaluation.',
                    'Avoid caffeine and alcohol.',
                ]
            );
        }

        // ── Dental Emergency ──
        if ($this->findCondition($conditions, ['dental', 'tooth', 'teeth', 'jaw', 'mouth'])) {
            $swelling   = ($answers['swelling'] ?? '') === 'yes';
            $knockedOut = ($answers['type']     ?? '') === 'Knocked out tooth';

            if ($swelling) {
                return $this->result(
                    'Dental Emergency / ENT',
                    'Hospital with Dental Emergency',
                    'critical',
                    false,
                    [
                        'Go to hospital immediately — spreading infection is dangerous.',
                        'Do not apply heat to swelling.',
                    ]
                );
            }

            if ($knockedOut) {
                return $this->result(
                    'Dental Emergency',
                    'Dental Emergency Clinic',
                    'urgent',
                    false,
                    [
                        'Pick up tooth by crown, not root.',
                        'Place tooth in milk or between cheek and gum.',
                        'Get to dentist within 30 minutes.',
                    ]
                );
            }

            return $this->result(
                'Dental Emergency',
                'Dental Clinic',
                'moderate',
                false,
                [
                    'Take OTC pain relief if needed.',
                    'Visit dentist as soon as possible.',
                ]
            );
        }

        // ── Default Fallback ──
        $intensity = $answers['intensity'] ?? 5;
        $worsening = ($answers['worsening'] ?? '') === 'yes';

        return $this->result(
            'General Emergency',
            'General Hospital',
            ($intensity >= 8 || $worsening) ? 'urgent' : 'moderate',
            $intensity >= 9,
            [
                'Head to the nearest hospital.',
                'Monitor your symptoms closely.',
                'If condition worsens suddenly — call ambulance.',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────
    //  Hospital Finder
    // ─────────────────────────────────────────────────────────

   private function getNearestHospitalBySpecialty($lat, $lng, $specialty)
{
    try {
        if (!is_numeric($lat) || !is_numeric($lng)) return null;

        
        $currentDay = date('l'); 

        $hospital = \App\Models\Hospital::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$lat, $lng, $lat]
        )
        ->where('is_active', 1)
        
        ->where(function($query) use ($currentDay) {
            $query->where('emergency_days', 'LIKE', '%24/7%')
                  ->orWhere('emergency_days', 'LIKE', '%' . $currentDay . '%');
        })
        ->whereHas('specialties', function($q) use ($specialty) {
            $q->whereRaw("LOWER(name) LIKE ?", ['%' . strtolower($specialty) . '%']);
        })
        ->with(['specialties', 'medicalServices'])
        ->orderBy('distance')
        ->first();

        return $hospital ? $this->formatHospitalResponse($hospital) : $this->getNearestHospital($lat, $lng);

    } catch (\Exception $e) {
        return null;
    }
}
   private function getNearestHospital($lat, $lng)
{
    try {
        if (!is_numeric($lat) || !is_numeric($lng)) return null;

        $currentDay = date('l'); 

        $hospital = \App\Models\Hospital::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
            [$lat, $lng, $lat]
        )
        ->where('is_active', 1)
        ->where(function($query) use ($currentDay) {
            $query->where('emergency_days', 'LIKE', '%24/7%')
                  ->orWhere('emergency_days', 'LIKE', '%' . $currentDay . '%');
        })
        ->with(['specialties', 'medicalServices'])
        ->orderBy('distance')
        ->first();

        return $hospital ? $this->formatHospitalResponse($hospital) : null;

    } catch (\Exception $e) {
        return null;
    }
}

    private function formatHospitalResponse($hospital)
    {
        return [
            'id'                  => $hospital->id,
            'name'                => $hospital->name,
            'address'             => $hospital->address,
            'phone'               => $hospital->phone,
            'lat'                 => (float) $hospital->lat,
            'lng'                 => (float) $hospital->lng,
            'rating'              => $hospital->rating,
            'is_24_7_emergency' => (
    str_contains($hospital->emergency_days, '24/7') || 
    str_contains($hospital->emergency_days, date('l'))
),
            'distance_km'         => round($hospital->distance, 1) . ' km',
            'eta_minutes'         => round(($hospital->distance / 30) * 60) . ' min drive',
            'specialties'         => $hospital->specialties->pluck('name')->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Result Builder
    // ─────────────────────────────────────────────────────────

    private function result(
        string $specialty,
        string $hospitalType,
        string $urgency,
        bool   $callAmbulance,
        array  $instructions
    ): array {
        $summaries = [
            'critical' => "Immediate medical attention required. Head to a {$hospitalType} now.",
            'urgent'   => "You need medical attention soon. Visit a {$hospitalType}.",
            'moderate' => "Your condition needs evaluation. Visit a {$hospitalType} at your earliest convenience.",
        ];

        return [
            'recommended_specialty'  => $specialty,
            'hospital_type'          => $hospitalType,
            'urgency_level'          => $urgency,
            'call_ambulance'         => $callAmbulance,
            'immediate_instructions' => $instructions,
            'summary'                => $summaries[$urgency],
        ];
    }
}