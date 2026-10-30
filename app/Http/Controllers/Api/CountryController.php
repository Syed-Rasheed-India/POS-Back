<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(
            Country::where('is_active', true)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_code' => 'required|string|max:2|unique:countries,country_code',
            'country_name' => 'required|string|max:100',
            'phone_code' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $country = Country::create($validated);

        return response()->json($country, 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Country::findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $country = Country::findOrFail($id);

        $validated = $request->validate([
            'country_code' => 'sometimes|required|string|max:2|unique:countries,country_code,' . $id,
            'country_name' => 'sometimes|required|string|max:100',
            'phone_code' => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $country->update($validated);

        return response()->json($country);
    }

    public function destroy(string $id)
    {
        $country = Country::findOrFail($id);

        $country->delete();

        return response()->json([
            'message' => 'Country deleted successfully.'
        ]);
    }
}