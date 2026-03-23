<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\specialty;
use Illuminate\Support\Facades\Storage;

class SpecialtyAdminController extends Controller
{

    public function index()
    {
        return response()->json(specialty::with('hospital')->get());
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name'        => 'required|string',
            'icon'        => 'required|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        
        $path = $request->file('icon')->store('uploads/specialties', 'public');

        $specialty = specialty::create([
            'hospital_id' => $request->hospital_id,
            'name'        => $request->name,
            'icon_url'    => asset('storage/' . $path)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Specialty added successfully',
            'data' => $specialty
        ], 201);
    }


    public function destroy($id)
    {
        $specialty = specialty::find($id);
        if (!$specialty) {
            return response()->json(['message' => 'Specialty not found'], 404);
        }

    
        $relativeContext = str_replace(asset('storage/'), '', $specialty->icon_url);
        Storage::disk('public')->delete($relativeContext);
        
        $specialty->delete();
        return response()->json(['message' => 'Specialty deleted successfully']);
    }
}