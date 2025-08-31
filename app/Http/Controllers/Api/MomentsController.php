<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Media;
use App\Models\Moment;
use Illuminate\Http\Request;

class MomentsController extends Controller
{
    public function index(Request $request){
        $moments = Moment::with(["location", "medias"])->orderBy("date", "DESC")->get();
        return response()->json($moments);
    }

    public function find(Request $request, $id){
        $moment = Moment::with(["location", "medias"])->find($id);
        return response()->json(["moment" => $moment]);
    }

    public function store(Request $request){
        try {
            $userId = $request->get('user_id');
            $request->validate([
                'name' => 'required',
                'description' => 'nullable|max:255',
                'date' => 'nullable|date',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'location_name' => 'nullable',
                "media*" => "file|mimes:jpeg,png,jpg,mp4,webm|max:10240"
            ]);

            if($request->has("longitude") && $request->has("latitude")){
                $location = Location::create([
                    'name' => $request->location_name,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'user_id' => $userId,
                ]);
                $request->merge(['location_id' => $location->id]);
            }

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                    $imgName = uniqid() . "." . $file->extension();
                    $path = public_path('files/medias');
                    $file->move($path, $imgName);

                    Media::create([
                        'location_id' => $request->location_id,
                        'user_id' => $userId,
                        'name' => $request->name,
                        'description' => $request->description,
                        'date' => $request->date,
                        'type' => $type,
                        'path' => "files/medias/" . $imgName,
                    ]);
                }
            }

            $moment = Moment::create([
                'name' => $request->name,
                'location_id' => $request->location_id,
                'description' => $request->description,
                'date' => $request->date,
                'user_id' => $userId,
            ]);

            return response()->json(["moment" => $moment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        try {
            $userId = $request->get('user_id');
            $request->validate([
                'name' => 'required',
                'description' => 'nullable|max:255',
                'date' => 'nullable|date',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'location_name' => 'nullable',
                "media*" => "file|mimes:jpeg,png,jpg,mp4,webm|max:10240"
            ]);

            $moment = Moment::find($id);
            if (!$moment) {
                $message = UserController::personalizedMessage($userId, "Momento não encontrado!", "Momento não encontrado, meu amorzinho!");
                return response()->json(['message' =>  $message], 404);
            }

            if(!$request->has("location_id") && $request->has("longitude") && $request->has("latitude")){
                $location = Location::create([
                    'name' => $request->location_name,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'user_id' => $userId,
                ]);
                $request->merge(['location_id' => $location->id]);
            }

            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                    $imgName = uniqid() . "." . $file->extension();
                    $path = public_path('files/medias');
                    $file->move($path, $imgName);

                    Media::create([
                        'moment_id' => $id,
                        'location_id' => $request->location_id,
                        'user_id' => $userId,
                        'name' => $request->name,
                        'description' => $request->description,
                        'date' => $request->date,
                        'type' => $type,
                        'path' => "files/medias/" . $imgName,
                    ]);
                }
            }   

            $imagesToDelete = json_decode($request->input('images_to_delete'), true);
            foreach ($imagesToDelete as $images) {
                $fullPath = public_path($images["path"]); // Caminho absoluto no sistema de arquivos
                if (file_exists($fullPath)) unlink($fullPath);
                $moment->medias()->where('path', $images["path"])->delete(); // Remove do banco
            }

            $moment->update([
                'name' => $request->name,
                'location_id' => $request->location_id,
                'description' => $request->description,
                'date' => $request->date,
            ]);

            return response()->json(["moment" => $moment], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, $id){
        $moment = Moment::find($id);
        $userId = $request->get('user_id');
            
        if (!$moment) {
            $message = UserController::personalizedMessage($userId, "Momento não encontrado!", "Momento não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 404);
        }

        $moment->delete();

        $message = UserController::personalizedMessage($userId, "Momento apagado!", "Momento apagado, meu amorzinho!");
        return response()->json(['message' => $message], 200);
    }
}
