<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsernameRule;
use Illuminate\Http\Request;

class UsernameRuleController extends Controller
{
    public function index()
    {
        return response()->json(
            UsernameRule::orderBy('id', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'min_length' => 'nullable|integer|min:1',
            'max_length' => 'nullable|integer|min:1',
            'allow_numbers' => 'boolean',
            'allow_special_characters' => 'boolean',
            'require_unique' => 'boolean',
            'pattern' => 'nullable|string|max:255',
        ]);

        $record = UsernameRule::create($validated);

        return response()->json($record, 201);
    }

    public function show($id)
    {
        return response()->json(
            UsernameRule::findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $record = UsernameRule::findOrFail($id);

        $validated = $request->validate([
            'min_length' => 'nullable|integer|min:1',
            'max_length' => 'nullable|integer|min:1',
            'allow_numbers' => 'boolean',
            'allow_special_characters' => 'boolean',
            'require_unique' => 'boolean',
            'pattern' => 'nullable|string|max:255',
        ]);

        $record->update($validated);

        return response()->json($record);
    }

    public function destroy($id)
    {
        UsernameRule::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Username rule deleted successfully'
        ]);
    }
}