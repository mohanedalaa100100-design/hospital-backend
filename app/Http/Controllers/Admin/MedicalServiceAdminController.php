<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\medicalservice;

class MedicalServiceAdminController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name'        => 'required|string',
            'icon'        => 'required|image|mimes:png,jpg,jpeg,svg|max:2048',
            'description' => 'nullable|string'
        ]);

        $path = $request->file('icon')->store('uploads/services', 'public');

        $service = medicalservice::create([
            'hospital_id' => $request->hospital_id,
            'name'        => $request->name,
            'description' => $request->description,
            'icon_url'    => asset('storage/' . $path)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Medical Service added successfully',
            'data' => $service
        ], 201);
    }
}