<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function handle(Request $request)
    {
        $location = $request->input('lat');

        // Perform any necessary processing with the location data
        // For example, you can save it to the database or perform some logic

        // Return a response
        return response()->json(['message' => 'Location received successfully', 'location' => $location]);
    }
}
