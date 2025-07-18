<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationUser;
use Illuminate\Http\Request;

class LocationsController extends Controller
{
    public function store(Request $request){
        $userId = $request->get('user_id');

        $data = $request->validate([
            'user_id'     => 'required|integer',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date'        => 'nullable|date',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
        ]);

        $data['user_id'] = $userId;

        $location = Location::create($data);
        $location = LocationUser::create([
            "location_id" => $location->id,
            "user_id" => $request->user_id,
        ]);

        return response()->json(["success" => true, "location" => $location, "message" => "Local cadastrado, meu amor 💖"]);
       
    }

}
