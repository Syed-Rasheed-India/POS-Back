<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Phase9EcrController extends Controller
{
    public function getProviders(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => ['Verifone', 'Ingenico', 'PAX']
        ]);
    }

    public function getTerminals(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function mapTerminal(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Terminal mapped successfully',
            'data' => $request->all()
        ]);
    }
}