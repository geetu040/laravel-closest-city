<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getLocations()
    {
        $locations = ["abc", "def", "ghi"];

        return response()->json(['locations' => $locations]);
    }
}
