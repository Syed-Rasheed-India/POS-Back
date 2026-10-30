<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CompanyUserController extends Controller
{
    public function index()
    {
        return response()->json(
            CompanyUser::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'username' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:8',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:20',
        ]);

        $validated['password_hash'] = Hash::make(
            $validated['password']
        );

        unset($validated['password']);

        $record = CompanyUser::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            CompanyUser::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = CompanyUser::findOrFail($id);

        $validated = $request->validate([
            'username' => 'sometimes|string|max:50',
            'email' => 'sometimes|email|max:150',
            'password' => 'nullable|string|min:8',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'status' => 'nullable|string|max:20',
        ]);

        if (isset($validated['password'])) {
            $validated['password_hash'] = Hash::make(
                $validated['password']
            );

            $validated['password_changed_at'] = now();

            unset($validated['password']);
        }

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        CompanyUser::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Company user deleted successfully'
        ]);
    }
}