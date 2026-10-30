<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Phase10ApiClientController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'API client generated successfully',
            'client_token' => 'pos_live_' . bin2hex(random_bytes(16))
        ], 201);
    }
}