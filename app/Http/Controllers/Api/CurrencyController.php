<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        return response()->json(
            Currency::where('is_active', true)->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'currency_code' => 'required|string|max:3|unique:currencies,currency_code',
            'currency_name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'decimal_places' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $currency = Currency::create($validated);

        return response()->json($currency, 201);
    }

    public function show(string $id)
    {
        return response()->json(
            Currency::findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $currency = Currency::findOrFail($id);

        $validated = $request->validate([
            'currency_code' => 'sometimes|required|string|max:3|unique:currencies,currency_code,' . $id,
            'currency_name' => 'sometimes|required|string|max:100',
            'symbol' => 'nullable|string|max:10',
            'decimal_places' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $currency->update($validated);

        return response()->json($currency);
    }

    public function destroy(string $id)
    {
        $currency = Currency::findOrFail($id);

        $currency->delete();

        return response()->json([
            'message' => 'Currency deleted successfully.'
        ]);
    }
}