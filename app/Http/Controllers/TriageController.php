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
                '1- Sit down and try to remain calm. Do not engage in physical activity.',
                '2- If you have aspirin and aren\'t allergic, chew one full-strength tablet.',
                '3- Keep your door unlocked if you are alone so help can reach you.',
                '4- Call ambulance immediately — do not drive yourself.',
            ]
        );
    }

    
    return $this->result(
        'Cardiology',
        'General Hospital with Cardiac Unit',
        'urgent',
        in_array($duration, ['4-12 hours', '12+ hours']),
        [
            '1- Rest and avoid physical activity.',
            '2- Monitor your symptoms closely.',
            '3- Head to the nearest hospital with a cardiac unit.',
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
            '1- Call ambulance IMMEDIATELY — every minute matters for stroke.',
            '2- Do not give the patient anything to eat or drink.',
            '3- Note the exact time symptoms started and tell the doctor.',
            '4- Keep the patient still and calm.',
            '5- Do not give aspirin for stroke symptoms.',
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
                '1- Call ambulance immediately.',
                '2- Sit upright — do not lie down.',
                '3- Use your inhaler if you have asthma.',
                '4- Loosen tight clothing.',
            ]
        );
    }

    return $this->result(
        'Pulmonology',
        'General Hospital',
        'urgent',
        false,
        [
            '1- Sit upright and try to breathe slowly.',
            '2- Avoid dusty or smoky environments.',
            '3- Head to the nearest hospital.',
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
                '1- Apply firm pressure to the wound with a clean cloth.',
                '2- Do not remove the cloth — add more on top if soaked.',
                '3- Keep the injured area elevated above heart level if possible.',
                '4- Call ambulance immediately.',
            ]
        );
    }

    return $this->result(
        'Emergency',
        'General Hospital',
        'urgent', 
        false,
        [
            '1- Keep applying pressure to the wound.',
            '2- Go to the nearest emergency room.',
        ]
    );
}
        // ── Seizures ──
if ($this->findCondition($conditions, ['seizure', 'convulsion', 'fit', 'epilepsy'])) {

    $duration = $answers['duration'] ?? '';
    $ongoing = str_contains(strtolower($duration), 'ongoing') || str_contains($duration, '5+');
    $firstTime = ($answers['first_time'] ?? 'no') === 'yes';

    return $this->result(
        'Neurology',
        'Neurology Hospital',
        $ongoing ? 'critical' : 'urgent',
        $ongoing || $firstTime,          
        [
            '1- Do not hold the person down or try to restrain their movements.',
            '2- Never put anything in the person\'s mouth.',
            '3- Clear the surrounding area of any hard or sharp objects to prevent injury.',
            '4- Gently turn the person onto their side after the seizure stops to keep the airway clear.',
            '5- Time the seizure — if it lasts more than 5 minutes, call ambulance immediately.',
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
            '1- Use EpiPen immediately if available.',
            '2- Call ambulance.',
            '3- Lie down with legs elevated unless breathing is hard.',
            '4- Remove or avoid the allergen if possible.',
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
            '1- Do not move the patient — possible spinal injury.',
            '2- Call ambulance immediately.',
            '3- Control any visible bleeding with firm pressure.',
            '4- Keep the patient warm and still.',
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
            '1- Call ambulance immediately.',
            '2- Check for breathing and pulse.',
            '3- If there is no pulse — start CPR if you are trained.',
            '4- Do not give anything by mouth (no water or food).',
            '5- Put the patient in the recovery position if they are breathing.',
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
            '1- Call ambulance immediately.',
            '2- Keep the patient calm and still.',
            '3- Monitor breathing closely for any changes.',
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
                '1- Call ambulance immediately — could be brain bleed or meningitis.',
                '2- Do not take painkillers without doctor advice.',
                '3- Avoid bright light.',
                '4- Keep still and calm.',
            ]
        );
    }

    return $this->result(
        'Neurology',
        'General Hospital',
        'urgent',
        false,
        [
            '1- Rest in a dark quiet room.',
            '2- Avoid screens.',
            '3- Go to hospital if pain worsens.',
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
                '1- Go to ER immediately.',
                '2- Apply cool damp cloth to forehead and underarms.',
                '3- Take fever-reducing medication if not allergic.',
                '4- Keep hydrated by drinking small sips of water.',
            ]
        );
    }

    return $this->result(
        'Internal Medicine',
        'General Hospital',
        'urgent', 
        false,
        [
            '1- Take paracetamol to reduce fever.',
            '2- Drink plenty of fluids.',
            '3- Visit hospital if fever persists over 24 hours.',
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
                '1- Do not eat or drink anything (NPO) — in case emergency surgery is needed.',
                '2- Go to ER immediately.',
                '3- Do not take painkillers — they may mask severe symptoms and delay diagnosis.',
                '4- Lie still in a comfortable position (like bending your knees).',
            ]
        );
    }

    return $this->result(
        'Gastroenterology',
        'General Hospital',
        'urgent', 
        false,
        [
            '1- Avoid eating until you are medically evaluated.',
            '2- Go to the nearest hospital.',
            '3- Monitor your symptoms closely for any worsening pain or fever.',
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
            '1- Call ambulance immediately.',
            '2- Do not eat or drink anything (NPO).',
            '3- Save a sample of the vomit if possible for the doctors to inspect.',
            '4- Lie down on your side and try to stay calm.',
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
                '1- Flush eye with plenty of clean running water for 15-20 minutes immediately.',
                '2- Do not rub or press the eye under any circumstances.',
                '3- Remove contact lenses immediately if worn.',
                '4- Go to the nearest eye emergency hospital right after flushing.',
            ]
        );
    }

    return $this->result(
        'Ophthalmology',
        'Eye Hospital',
        'critical', 
        $vision,  
        [
            '1- Do not rub, press, or touch the eye.',
            '2- Cover the injured eye loosely with a clean cloth or eye shield.',
            '3- Go to the nearest specialized eye hospital immediately.',
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
                '1- Call ambulance immediately — possible spinal emergency (Cauda Equina Syndrome).',
                '2- Do not move at all and stay flat — wait for professional medical help.',
            ]
        );
    }

    return $this->result(
        'Orthopedics',
        'General Hospital',
        'urgent',
        false,
        [
            '1- Lie flat on a firm surface in a comfortable position.',
            '2- Apply an ice pack wrapped in a cloth to the painful area for 20 minutes.',
            '3- Avoid any lifting, bending, or sudden twisting movements.',
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
                '1- Call ambulance immediately.',
                '2- Cool the burn with cool, clean running water for 20 minutes (do not use freezing water).',
                '3- Do not apply ice, butter, toothpaste, or oils to the burn.',
                '4- Cover the burn loosely with clean plastic wrap or a sterile cloth.',
            ]
        );
    }

    return $this->result(
        'Burns Unit',
        'General Hospital',
        'urgent',
        false,
        [
            '1- Cool the burn with running water for 20 minutes.',
            '2- Cover with a clean, non-stick bandage or wrap.',
            '3- Go to the nearest hospital for proper medical assessment.',
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
                '1- Call ambulance immediately.',
                '2- Lie on your left side to improve blood flow to the baby.',
                '3- Do not eat or drink anything (NPO).',
            ]
        );
    }

    return $this->result(
        'Obstetrics / Gynecology',
        'Maternity Hospital',
        'urgent',
        false,
        [
            '1- Go to the nearest maternity hospital immediately.',
            '2- Lie on your left side while moving or resting.',
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
                '1- Call ambulance immediately.',
                '2- Do not give anything by mouth (no water, food, or sugar) — to prevent choking.',
                '3- Place the patient in the recovery position to keep the airway open.',
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
                '1- Give a sugary drink, fruit juice, or glucose tablets immediately (Rule of 15).',
                '2- If there is no improvement in 15 minutes — call an ambulance.',
                '3- Go to the nearest hospital for proper medical evaluation.',
            ]
        );
    }

    return $this->result(
        'Endocrinology',
        'General Hospital',
        'urgent', 
        false,
        [
            '1- Go to the hospital for blood sugar management.',
            '2- Drink plenty of water (avoid sugary drinks or juice in this case).',
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
            '1- Call ambulance immediately.',
            '2- Do not induce vomiting unless explicitly told by poison control or a doctor.',
            '3- Bring the substance, chemical, or medicine container to the hospital if possible.',
            '4- Monitor breathing and level of consciousness closely.',
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
                '1- Go to hospital immediately — spreading infection can affect your airway and is dangerous.',
                '2- Do not apply heat to the swelling; use a cold compress instead.',
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
                '1- Pick up the tooth by the crown (chewing surface), never touch the root.',
                '2- Place the tooth in a cup of milk or keep it between your cheek and gum to preserve it.',
                '3- Get to a dentist immediately, ideally within 30 minutes to increase chances of saving it.',
            ]
        );
    }

    return $this->result(
        'Dental Emergency',
        'Dental Clinic',
        'moderate',
        false,
        [
            '1- Take over-the-counter pain relief (like paracetamol or ibuprofen) if needed.',
            '2- Visit a dentist as soon as possible to check the tooth.',
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
        '1- Head to the nearest hospital for proper medical examination.',
        '2- Monitor your symptoms closely for any new or changing signs.',
        '3- If your condition worsens suddenly — call an ambulance immediately.',
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
                [(float)$lat, (float)$lng, (float)$lat]
            )
            ->where('is_active', 1)
            ->where(function($query) use ($currentDay) {
                $query->where('emergency_days', 'LIKE', '%24/7%')
                      ->orWhere('emergency_days', 'LIKE', '%' . $currentDay . '%');
            })
            ->whereHas('specialties', function($q) use ($specialty) {
                
                $words = explode(' ', str_replace(['/', '-', ','], ' ', $specialty));
                $q->where(function($subParam) use ($words) {
                    foreach ($words as $word) {
                        if (strlen($word) > 3) { 
                            $subParam->orWhereRaw("LOWER(name) LIKE ?", ['%' . strtolower($word) . '%']);
                        }
                    }
                });
            })
            ->with(['specialties', 'medicalServices'])
            ->orderBy('distance')
            ->first();

        
            return $hospital ? $this->formatHospitalResponse($hospital) : $this->getNearestHospital($lat, $lng);

        } catch (\Exception $e) {
            
            return $this->getNearestHospital($lat, $lng);
        }
    }

    private function getNearestHospital($lat, $lng)
    {
        try {
            if (!is_numeric($lat) || !is_numeric($lng)) return null;

            $currentDay = date('l'); 

            $hospital = \App\Models\Hospital::selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance",
                [(float)$lat, (float)$lng, (float)$lat]
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
        $distance = (float) $hospital->distance;
        
        
        $eta = round(($distance / 30) * 60);
        $eta = $eta < 1 ? 1 : $eta; 

        return [
            'id'                => $hospital->id,
            'name'              => $hospital->name,
            'address'           => $hospital->address,
            'phone'             => $hospital->phone,
            'lat'               => (float) $hospital->lat,
            'lng'               => (float) $hospital->lng,
            'rating'            => (float) $hospital->rating,
            'is_24_7_emergency' => str_contains($hospital->emergency_days, '24/7'),
            'distance_km'       => round($distance, 1) . ' km',
            'eta_minutes'       => $eta . ' min drive',
            'specialties'       => $hospital->specialties->pluck('name')->toArray(),
            'medical_services'  => $hospital->medicalServices->pluck('name')->toArray(),
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

        
        $lat = request()->input('lat');
        $lng = request()->input('lng');

        
        $nearestHospital = $this->getNearestHospitalBySpecialty($lat, $lng, $specialty);

        return [
            'urgency_level'          => $urgency,
            'call_ambulance'         => $callAmbulance,
            'recommended_specialty'  => $specialty,
            'hospital_type'          => $hospitalType,
            'summary'                => $summaries[$urgency] ?? $summaries['moderate'],
            'immediate_instructions' => $instructions,
            'nearest_hospital'       => $nearestHospital,
        ];
    }
}