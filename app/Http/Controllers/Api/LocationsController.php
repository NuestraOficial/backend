<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FolderMedia;
use App\Models\Location;
use App\Models\LocationMedia;
use App\Models\LocationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LocationsController extends Controller
{
    public function index(Request $request){
        $locations = Location::all();
        return response()->json($locations);
    }

    public function find(Request $request, $id){
        $userId = $request->get('user_id');
        $location = Location::find($id);

        if (!$location) {
            $message = UserController::personalizedMessage($userId, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 404);
        }

        return response()->json(["location" => $location]);
    }


    public function store(Request $request){
        $userId = $request->get('user_id');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $data['user_id'] = $userId;
        $location = Location::create($data);
        
        // if ($request->hasFile('media')) {
        //     foreach ($request->file('media') as $file) {
        //         $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

        //         $imgName = uniqid() . "." . $file->extension();
        //         $path = public_path('files/medias');
        //         $file->move($path, $imgName);

        //         FolderMedia::create([
        //             'location_id' => $location->id,
        //             'user_uuid' => $request->other_user_uuid,
        //             'name' => $request->name,
        //             'description' => $request->description,
        //             'date' => $request->date,
        //             'type' => $type,
        //             'path' => "files/medias/" . $imgName,
        //         ]);
        //     }
        // }
        
        $message = UserController::personalizedMessage($userId, "Local cadastrado!", "Local cadastrado, meu amor 💖");
        return response()->json(["location" => $location, "message" => $message]);
    }

    public function update(Request $request, $id){
        $userId = $request->get('user_id');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $location = Location::find($id);

        if (!$location) {
            $message = UserController::personalizedMessage($userId, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $location->update($data);

        // Salvar mídias
        // if ($request->hasFile('media')) {
        //     foreach ($request->file('media') as $file) {
        //         $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

        //         $imgName = uniqid() . "." . $file->extension();
        //         $path = public_path('files/medias');
        //         $file->move($path, $imgName);

        //         FolderMedia::create([
        //             'location_id' => $location->id,
        //             'user_uuid' => $request->other_user_uuid,
        //             'name' => $request->name,
        //             'description' => $request->description,
        //             'date' => $request->date,
        //             'type' => $type,
        //             'path' => "files/medias/" . $imgName,
        //         ]);
        //     }
        // }

        // $imagesToDelete = json_decode($request->input('images_to_delete'), true);

        // foreach ($imagesToDelete as $images) {
        //     // Caminho absoluto no sistema de arquivos
        //     $fullPath = public_path($images["path"]);

        //     // Apaga o arquivo fisicamente se existir
        //     if (file_exists($fullPath)) {
        //         unlink($fullPath);
        //     }

        //     // Remove do banco
        //     $location->media()->where('path', $images["path"])->delete();
        // }

        
        $message = UserController::personalizedMessage($userId, "Local atualizado!", "Local atualizado, meu amor 💖");
        return response()->json(["message" => $message, "location" => $location]);
    }

    public function delete(Request $request, $id){
        $userId = $request->get('user_id');

        $location = Location::find($id);

        if (!$location) {
            $message = UserController::personalizedMessage($userId, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 404);
        }

        $location->delete();

        $message = UserController::personalizedMessage($userId, "Local excluído com sucesso 💔", "Local excluído com sucesso, meu amor 💔");
        return response()->json(['message' => $message]);
    }
}
