<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Specialty; 
use Illuminate\Support\Facades\Storage;

class SpecialtyAdminController extends Controller
{
    
    public function index()
    {
        
        $specialties = Specialty::with('hospitals')->get()->map(function ($item) {
            
            if ($item->icon_url && !filter_var($item->icon_url, FILTER_VALIDATE_URL)) {
                $item->icon_url = asset('storage/' . $item->icon_url);
            }
            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'Specialties retrieved successfully',
            'data' => $specialties
        ], 200);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'hospital_id' => 'required|exists:hospitals,id',
            'name'        => 'required|string',
            'icon'        => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048'
        ]);

        
        $specialty = Specialty::where('name', $request->name)->first();

        if (!$specialty) {
            
            $path = $request->file('icon')->store('uploads/specialties', 'public');
            $specialty = Specialty::create([
                'name'     => $request->name,
                'icon_url' => $path 
            ]);
        }

        
        $specialty->hospitals()->syncWithoutDetaching([$request->hospital_id]);

        return response()->json([
            'status' => true,
            'message' => 'Specialty linked to hospital successfully',
            'data' => $specialty->load('hospitals') 
        ], 201);
    }

    
    public function destroy($id)
    {
        $specialty = Specialty::find($id);
        if (!$specialty) {
            return response()->json(['status' => false, 'message' => 'Specialty not found'], 404);
        }

        
        if ($specialty->icon_url && !filter_var($specialty->icon_url, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($specialty->icon_url);
        }
        
    
        $specialty->delete();
        
        return response()->json(['status' => true, 'message' => 'Specialty deleted successfully'], 200);
    }
}