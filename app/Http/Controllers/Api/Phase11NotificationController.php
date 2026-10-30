<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Phase11NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    public function markRead($id)
    {
        return response()->json([
            'success' => true,
            'message' => "Notification {$id} marked as read"
        ]);
    }
}