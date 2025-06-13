<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        return Employee::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomor' => 'required|unique:employees',
            'nama' => 'required',
            'photo' => 'nullable|image|max:2048'
        ]);

        $data = $request->only([
            'nomor', 'nama', 'jabatan', 'talahir',
            'created_by', 'updated_by', 'deleted_on'
        ]);

        $data['created_on'] = now();
        $data['updated_on'] = now();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 's3');
            $data['photo_upload_path'] = Storage::disk('s3')->url($path);
        }

        $employee = Employee::create($data);

        // Simpan ke Redis
        Redis::set("emp_{$employee->nomor}", $employee->toJson());

        return response()->json($employee);
    }

    public function show($id)
    {
        return Employee::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->only([
            'nama', 'jabatan', 'talahir', 'updated_by', 'deleted_on'
        ]);

        $data['updated_on'] = now();

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 's3');
            $data['photo_upload_path'] = Storage::disk('s3')->url($path);
        }

        $employee->update($data);

        // Perbarui Redis
        Redis::set("emp_{$employee->nomor}", $employee->toJson());

        return response()->json($employee);
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);

        Redis::del("emp_{$employee->nomor}");

        $employee->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
