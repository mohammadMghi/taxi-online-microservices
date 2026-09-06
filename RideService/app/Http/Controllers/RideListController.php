<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RideListController extends Controller
{
    public function index(Request $request)
    {
        $user_id = $request->headers->get('X-User-ID');
        $rides = \App\Models\Ride::where('user_id', $user_id)->get();

        return response()->json([
            'message' => 'Rides retrieved successfully.',
            'data' => $rides,
        ]);
    }
}
