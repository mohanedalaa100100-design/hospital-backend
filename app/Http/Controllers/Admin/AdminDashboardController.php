<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hospital;
use App\Models\Appointment;
use App\Models\Doctor;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data'   => [
                'total_users'             => User::count(),
                'total_hospitals'         => Hospital::count(),
                'total_doctors'           => Doctor::count(),
                'total_appointments'      => Appointment::count(),
                'pending_appointments'    => Appointment::where('status', 'pending')->count(),
                'completed_appointments'  => Appointment::where('status', 'completed')->count(),
                'total_revenue'           => Appointment::where('status', 'completed')
                    ->leftJoin('doctors', 'appointments.doctor_id', '=', 'doctors.id')
                    ->sum('doctors.consultation_fee') ?? 0,
            ]
        ], 200);
    }
}