<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicAdminController extends Controller
{
    
    public function index()
    {
        $clinics = Clinic::with(['hospital', 'specialty'])->paginate(10);
        return response()->json(['status' => true, 'data' => $clinics]);
    }

    
    public function store(Request $request)
    {
        $clinic = Clinic::create($request->all());
        return response()->json(['status' => true, 'message' => 'تم إضافة العيادة بنجاح', 'data' => $clinic]);
    }

    
    public function show($id)
    {
        $clinic = Clinic::with(['hospital', 'specialty'])->find($id);
        if (!$clinic) return response()->json(['status' => false, 'message' => 'العيادة غير موجودة'], 404);
        return response()->json(['status' => true, 'data' => $clinic]);
    }

    
    public function update(Request $request, $id)
    {
        $clinic = Clinic::find($id);
        if (!$clinic) return response()->json(['status' => false, 'message' => 'العيادة غير موجودة'], 404);
        $clinic->update($request->all());
        return response()->json(['status' => true, 'message' => 'تم تحديث البيانات بنجاح']);
    }


    public function destroy($id)
    {
        $clinic = Clinic::find($id);
        if (!$clinic) return response()->json(['status' => false, 'message' => 'العيادة غير موجودة'], 404);
        $clinic->delete();
        return response()->json(['status' => true, 'message' => 'تم حذف العيادة بنجاح']);
    }

    public function stats($id)
    {
        return response()->json(['status' => true, 'message' => 'Stats feature coming soon']);
    }
}