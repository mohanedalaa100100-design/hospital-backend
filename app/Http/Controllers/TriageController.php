<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TriageController extends Controller
{
    /**
     * POST /api/triage
     * 
     * Request body:
     * {
     *   "conditions": ["Severe chest pain"],
     *   "age": "45",
     *   "gender": "Male",
     *   "chronic_diseases": ["Heart Disease"],
     *   "special_conditions": ["Pregnant"],
     *   "answers": {
     *     "intensity": 8,
     *     "radiating": "yes",
     *     "sweating": "yes",
     *     "duration": "1-4 hours"
     *   }
     * }
     */
    public function assess(Request $request): JsonResponse
    {
        $request->validate([
            'conditions'         => 'required|array',
            'age'                => 'nullable|string',
            'gender'             => 'nullable|string',
            'chronic_diseases'   => 'nullable|array',
            'special_conditions' => 'nullable|array',
            'answers'            => 'nullable|array',
        ]);

        $conditions        = $request->input('conditions', []);
        $age               = (int) $request->input('age', 30);
        $chronicDiseases   = $request->input('chronic_diseases', []);
        $specialConditions = $request->input('special_conditions', []);
        $answers           = $request->input('answers', []);

        $isElderly    = $age >= 65;
        $isChild      = $age <= 12;
        $isPregnant   = in_array('Pregnant', $specialConditions);
        $hasHeart     = in_array('Heart Disease', $chronicDiseases);

        $result = $this->runTriage(
            $conditions, $answers,
            $isElderly, $isChild, $isPregnant, $hasHeart
        );

        return response()->json($result);
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
        if (in_array('Severe chest pain', $conditions)) {
            $intensity  = $answers['intensity'] ?? 5;
            $radiating  = ($answers['radiating'] ?? '') === 'yes';
            $sweating   = ($answers['sweating']  ?? '') === 'yes';
            $duration   = $answers['duration'] ?? '';

            $isHeartAttack = $radiating || $sweating || $intensity >= 8 || $hasHeart || $isElderly;

            if ($isHeartAttack) {
                return $this->result(
                    specialty:     'Cardiology',
                    hospitalType:  'Cardiac Center',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Sit down and stay calm. Do not move or exert yourself.',
                        'Chew one aspirin tablet if you are not allergic.',
                        'Loosen tight clothing around your chest.',
                        'Keep your door unlocked so help can reach you.',
                        'Call ambulance immediately — do not drive yourself.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Cardiology',
                hospitalType:  'General Hospital with Cardiac Unit',
                urgency:       'urgent',
                callAmbulance: in_array($duration, ['4-12 hours', '12+ hours']),
                instructions: [
                    'Rest and avoid physical activity.',
                    'Monitor your symptoms closely.',
                    'Head to the nearest hospital with a cardiac unit.',
                ]
            );
        }

        // ── Stroke ──
        if (in_array('Stroke symptoms', $conditions)) {
            return $this->result(
                specialty:     'Neurology',
                hospitalType:  'Stroke Center / Neurology Hospital',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Call ambulance IMMEDIATELY — every minute matters for stroke.',
                    'Do not give the patient anything to eat or drink.',
                    'Note the exact time symptoms started — tell the doctor.',
                    'Keep the patient still and calm.',
                    'Do not give aspirin for stroke symptoms.',
                ]
            );
        }

        // ── Breathing ──
        if (in_array('Difficulty breathing', $conditions)) {
            $canSpeak = ($answers['speaking'] ?? 'yes') === 'yes';

            if (!$canSpeak || $isChild || $isElderly || $isPregnant) {
                return $this->result(
                    specialty:     'Pulmonology / Emergency',
                    hospitalType:  'Hospital with ICU',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance immediately.',
                        'Sit upright — do not lie down.',
                        'Use your inhaler if you have asthma.',
                        'Loosen tight clothing.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Pulmonology',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Sit upright and try to breathe slowly.',
                    'Avoid dusty or smoky environments.',
                    'Head to the nearest hospital.',
                ]
            );
        }

        // ── Severe Bleeding ──
        if (in_array('Severe bleeding', $conditions)) {
            $controlled = ($answers['controlled'] ?? 'yes') === 'yes';
            $location   = $answers['location'] ?? '';
            $dangerous  = str_contains($location, 'Internal') || str_contains($location, 'Chest');

            if (!$controlled || $dangerous) {
                return $this->result(
                    specialty:     'Emergency Surgery',
                    hospitalType:  'Trauma Center',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Apply firm pressure to the wound with a clean cloth.',
                        'Do not remove the cloth — add more on top if soaked.',
                        'Keep the injured area elevated above heart level if possible.',
                        'Call ambulance immediately.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Emergency',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Keep applying pressure to the wound.',
                    'Go to the nearest emergency room.',
                ]
            );
        }

        // ── Seizures ──
        if (in_array('Seizures', $conditions)) {
            $ongoing    = in_array($answers['duration'] ?? '', ['Still ongoing', '5+ minutes']);
            $firstTime  = ($answers['first_time'] ?? 'no') === 'yes';

            return $this->result(
                specialty:     'Neurology',
                hospitalType:  'Neurology Hospital',
                urgency:       $ongoing ? 'critical' : 'urgent',
                callAmbulance: $ongoing || $firstTime,
                instructions: [
                    'Do not hold the person down or put anything in their mouth.',
                    'Clear the area of hard objects.',
                    'Turn the person on their side after the seizure stops.',
                    'Time the seizure — if over 5 minutes, call ambulance.',
                ]
            );
        }

        // ── Allergic Reaction ──
        if (in_array('Severe allergic reaction', $conditions)) {
            return $this->result(
                specialty:     'Emergency',
                hospitalType:  'Hospital with Emergency Unit',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Use EpiPen immediately if available.',
                    'Call ambulance.',
                    'Lie down with legs elevated unless breathing is hard.',
                    'Remove or avoid the allergen if possible.',
                ]
            );
        }

        // ── Trauma ──
        if (in_array('Major accident or trauma', $conditions)) {
            return $this->result(
                specialty:     'Orthopedics / Trauma Surgery',
                hospitalType:  'Trauma Center',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Do not move the patient — possible spinal injury.',
                    'Call ambulance immediately.',
                    'Control any visible bleeding with pressure.',
                    'Keep the patient warm and still.',
                ]
            );
        }

        // ── Loss of Consciousness ──
        if (in_array('Loss of consciousness', $conditions)) {
            return $this->result(
                specialty:     'Emergency / ICU',
                hospitalType:  'Hospital with ICU',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Call ambulance immediately.',
                    'Check for breathing and pulse.',
                    'If no pulse — start CPR if trained.',
                    'Do not give anything by mouth.',
                    'Put in recovery position if breathing.',
                ]
            );
        }

        // ── Confusion / Blue lips ──
        if (in_array('Sudden confusion', $conditions) || in_array('Blue lips or face', $conditions)) {
            return $this->result(
                specialty:     'Emergency',
                hospitalType:  'Hospital with Emergency Unit',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Call ambulance immediately.',
                    'Keep the patient calm and still.',
                    'Monitor breathing closely.',
                ]
            );
        }

        // ── Severe Headache ──
        if (in_array('Severe headache / Thunderclap', $conditions)) {
            $sudden    = ($answers['onset'] ?? '') === 'Suddenly (worst of my life)';
            $neckStiff = ($answers['neck_stiffness'] ?? '') === 'yes';

            if ($sudden || $neckStiff) {
                return $this->result(
                    specialty:     'Neurology',
                    hospitalType:  'Neurology Hospital',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance immediately — could be brain bleed or meningitis.',
                        'Do not take painkillers without doctor advice.',
                        'Avoid bright light.',
                        'Keep still and calm.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Neurology',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Rest in a dark quiet room.',
                    'Avoid screens.',
                    'Go to hospital if pain worsens.',
                ]
            );
        }

        // ── High Fever ──
        if (in_array('High fever (39°C+)', $conditions)) {
            $veryHigh = in_array($answers['temp'] ?? '', ['Above 41°C', '40-41°C']);
            $confused = ($answers['confusion'] ?? '') === 'yes';
            $rash     = ($answers['rash'] ?? '') === 'yes';

            if ($veryHigh || $confused || $rash || $isChild || $isElderly) {
                return $this->result(
                    specialty:     'Emergency / Internal Medicine',
                    hospitalType:  'Hospital with Emergency Unit',
                    urgency:       'critical',
                    callAmbulance: $confused,
                    instructions: [
                        'Go to ER immediately.',
                        'Apply cool damp cloth to forehead.',
                        'Take fever-reducing medication if not allergic.',
                        'Keep hydrated.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Internal Medicine',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Take paracetamol to reduce fever.',
                    'Drink plenty of fluids.',
                    'Visit hospital if fever persists over 24 hours.',
                ]
            );
        }

        // ── Abdominal Pain ──
        if (in_array('Severe abdominal pain', $conditions)) {
            $rigid      = ($answers['rigid'] ?? '') === 'yes';
            $intensity  = $answers['intensity'] ?? 5;
            $lowerRight = ($answers['location'] ?? '') === 'Lower right';

            if ($rigid || $intensity >= 8 || $lowerRight) {
                return $this->result(
                    specialty:     'General Surgery',
                    hospitalType:  'Hospital with Surgical Unit',
                    urgency:       'critical',
                    callAmbulance: $rigid,
                    instructions: [
                        'Do not eat or drink anything.',
                        'Go to ER immediately.',
                        'Do not take painkillers — may mask symptoms.',
                        'Lie still in a comfortable position.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Gastroenterology',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Avoid eating until evaluated.',
                    'Go to hospital.',
                    'Monitor for worsening pain.',
                ]
            );
        }

        // ── Vomiting Blood ──
        if (in_array('Vomiting blood', $conditions)) {
            return $this->result(
                specialty:     'Gastroenterology / Emergency',
                hospitalType:  'Hospital with Emergency Unit',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Call ambulance immediately.',
                    'Do not eat or drink.',
                    'Save a sample if possible for doctors.',
                    'Lie down and stay calm.',
                ]
            );
        }

        // ── Eye Injury ──
        if (in_array('Eye injury / sudden vision loss', $conditions)) {
            $chemical = ($answers['type'] ?? '') === 'Chemical in eye';
            $vision   = ($answers['type'] ?? '') === 'Sudden vision loss';

            if ($chemical) {
                return $this->result(
                    specialty:     'Ophthalmology',
                    hospitalType:  'Eye Hospital',
                    urgency:       'critical',
                    callAmbulance: false,
                    instructions: [
                        'Flush eye with clean water for 15-20 minutes immediately.',
                        'Do not rub the eye.',
                        'Go to eye emergency immediately.',
                        'Remove contact lenses if worn.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Ophthalmology',
                hospitalType:  'Eye Hospital',
                urgency:       'critical',
                callAmbulance: $vision,
                instructions: [
                    'Do not rub or press on the eye.',
                    'Cover eye loosely with clean cloth.',
                    'Go to eye hospital immediately.',
                ]
            );
        }

        // ── Back Pain ──
        if (in_array('Severe back pain', $conditions)) {
            $legWeakness = ($answers['legs']    ?? '') === 'yes';
            $bladder     = ($answers['bladder'] ?? '') === 'yes';

            if ($legWeakness || $bladder) {
                return $this->result(
                    specialty:     'Neurosurgery / Orthopedics',
                    hospitalType:  'Hospital with Neurosurgery Unit',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance — possible spinal emergency.',
                        'Do not move — wait for help.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Orthopedics',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Lie flat on firm surface.',
                    'Apply ice pack for 20 minutes.',
                    'Avoid lifting or bending.',
                ]
            );
        }

        // ── Burns ──
        if (in_array('Burns', $conditions)) {
            $large      = in_array($answers['size'] ?? '', ['Large (chest/back)', 'Very large (multiple areas)']);
            $face       = ($answers['face']       ?? '') === 'yes';
            $electrical = ($answers['cause']      ?? '') === 'Electrical';

            if ($large || $face || $electrical) {
                return $this->result(
                    specialty:     'Burns Unit',
                    hospitalType:  'Burns Center',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance immediately.',
                        'Cool burn with cool running water for 20 minutes.',
                        'Do not use ice, butter, or toothpaste.',
                        'Cover loosely with clean wrap.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Burns Unit',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Cool with running water for 20 minutes.',
                    'Cover with clean bandage.',
                    'Go to hospital for assessment.',
                ]
            );
        }

        // ── Pregnancy ──
        if (in_array('Pregnancy complications', $conditions)) {
            $bleeding = ($answers['bleeding']  ?? '') === 'yes';
            $movement = ($answers['movement']  ?? '') === 'yes';
            $pain     = ($answers['pain']      ?? '') === 'yes';
            $late     = in_array($answers['weeks'] ?? '', ['36+ weeks', '28-36 weeks']);

            if ($bleeding || $movement || ($pain && $late)) {
                return $this->result(
                    specialty:     'Obstetrics / Gynecology',
                    hospitalType:  'Maternity Hospital',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance immediately.',
                        'Lie on your left side.',
                        'Do not eat or drink.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Obstetrics / Gynecology',
                hospitalType:  'Maternity Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Go to maternity hospital immediately.',
                    'Lie on your left side.',
                ]
            );
        }

        // ── Diabetic Emergency ──
        if (in_array('Diabetic emergency', $conditions)) {
            $lowSugar  = ($answers['sugar']     ?? '') === 'Very low (below 60)';
            $conscious = ($answers['conscious'] ?? 'yes') === 'yes';

            if (!$conscious) {
                return $this->result(
                    specialty:     'Emergency / Endocrinology',
                    hospitalType:  'Hospital with Emergency Unit',
                    urgency:       'critical',
                    callAmbulance: true,
                    instructions: [
                        'Call ambulance immediately.',
                        'Do not give anything by mouth.',
                        'Place in recovery position.',
                    ]
                );
            }

            if ($lowSugar) {
                return $this->result(
                    specialty:     'Endocrinology / Emergency',
                    hospitalType:  'General Hospital',
                    urgency:       'urgent',
                    callAmbulance: false,
                    instructions: [
                        'Give sugary drink or glucose tablets immediately.',
                        'If no improvement in 15 minutes — call ambulance.',
                        'Go to hospital for evaluation.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Endocrinology',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Go to hospital for blood sugar management.',
                    'Drink water (not sugary drinks).',
                ]
            );
        }

        // ── Poisoning ──
        if (in_array('Poisoning / overdose', $conditions)) {
            return $this->result(
                specialty:     'Toxicology / Emergency',
                hospitalType:  'Hospital with Emergency Unit',
                urgency:       'critical',
                callAmbulance: true,
                instructions: [
                    'Call ambulance immediately.',
                    'Do not induce vomiting unless told by poison control.',
                    'Bring the substance or container to the hospital.',
                    'Monitor breathing closely.',
                ]
            );
        }

        // ── Urinary Retention ──
        if (in_array('Urinary retention / severe pain', $conditions)) {
            $canUrinate = ($answers['urination'] ?? 'yes') === 'yes';
            $pain       = $answers['pain'] ?? 5;

            if (!$canUrinate || $pain >= 8) {
                return $this->result(
                    specialty:     'Urology',
                    hospitalType:  'Hospital with Urology Unit',
                    urgency:       'critical',
                    callAmbulance: false,
                    instructions: [
                        'Go to ER immediately — bladder may need catheter.',
                        'Do not drink large amounts of water.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Urology',
                hospitalType:  'General Hospital',
                urgency:       'urgent',
                callAmbulance: false,
                instructions: [
                    'Go to hospital for urological evaluation.',
                    'Avoid caffeine and alcohol.',
                ]
            );
        }

        // ── Dental Emergency ──
        if (in_array('Dental / jaw emergency', $conditions)) {
            $swelling   = ($answers['swelling'] ?? '') === 'yes';
            $knockedOut = ($answers['type']     ?? '') === 'Knocked out tooth';

            if ($swelling) {
                return $this->result(
                    specialty:     'Dental Emergency / ENT',
                    hospitalType:  'Hospital with Dental Emergency',
                    urgency:       'critical',
                    callAmbulance: false,
                    instructions: [
                        'Go to hospital immediately — spreading infection is dangerous.',
                        'Do not apply heat to swelling.',
                    ]
                );
            }

            if ($knockedOut) {
                return $this->result(
                    specialty:     'Dental Emergency',
                    hospitalType:  'Dental Emergency Clinic',
                    urgency:       'urgent',
                    callAmbulance: false,
                    instructions: [
                        'Pick up tooth by crown, not root.',
                        'Place tooth in milk or between cheek and gum.',
                        'Get to dentist within 30 minutes.',
                    ]
                );
            }

            return $this->result(
                specialty:     'Dental Emergency',
                hospitalType:  'Dental Clinic',
                urgency:       'moderate',
                callAmbulance: false,
                instructions: [
                    'Take OTC pain relief if needed.',
                    'Visit dentist as soon as possible.',
                ]
            );
        }

        // ── Default Fallback ──
        $intensity = $answers['intensity'] ?? 5;
        $worsening = ($answers['worsening'] ?? '') === 'yes';

        return $this->result(
            specialty:     'General Emergency',
            hospitalType:  'General Hospital',
            urgency:       ($intensity >= 8 || $worsening) ? 'urgent' : 'moderate',
            callAmbulance: $intensity >= 9,
            instructions: [
                'Head to the nearest hospital.',
                'Monitor your symptoms closely.',
                'If condition worsens suddenly — call ambulance.',
            ]
        );
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
            'recommended_specialty' => $specialty,
            'hospital_type'         => $hospitalType,
            'urgency_level'         => $urgency,
            'call_ambulance'        => $callAmbulance,
            'immediate_instructions'=> $instructions,
            'summary'               => $summaries[$urgency],
        ];
    }
}
