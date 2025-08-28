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
        $user_uuid = $request->get('user_uuid');

       $locations = Location::where(function ($query) use ($user_uuid) {
            $query->where('user_uuid', $user_uuid)
                ->orWhereExists(function ($q) use ($user_uuid) {
                    $q->select(DB::raw(1))
                        ->from('location_users')
                        ->whereColumn('location_users.location_id', 'locations.id')
                        ->where('location_users.user_uuid', $user_uuid);
                });
        })->orderBy("date", "DESC")->get();

        return response()->json($locations);
    }



    public function find(Request $request, $id){
        $auth = $this->getAuthUserIdentifier($request);


        $location = Location::where('id', $id)->with("media")
           ->where(function ($query) use ($auth) {
                $query->where($auth['column'], $auth['value'])
                    ->orWhereExists(function ($q) use ($auth) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where("location_users.{$auth['column']}", $auth['value']);
                    });
            })

            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($auth, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $location_users = LocationUser::where("location_id", $location->id)->get();
        return response()->json(["location" => $location, "location_users" => $location_users]);
    }


    public function store(Request $request){
        $userId = $request->get('user_id');
        $user_uuid = $request->get('user_uuid');

        $data = $request->validate([
            'other_user_uuid' => 'nullable|string',
            // 'id_user' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'name' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'media.*' => 'file|mimes:jpeg,png,jpg,mp4,webm|max:10240' // até 10MB por arquivo
        ]);

        // $data['user_id'] = $userId;
        $data['user_uuid'] = $user_uuid;

        $location = Location::create($data);
        LocationUser::create([
            "location_id" => $location->id,
            "user_uuid" => $request->other_user_uuid,
        ]);

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                // $path = $file->store('locations/media', 'public');

                $imgName = uniqid() . "." . $file->extension();
                $path = public_path('files/medias');
                $file->move($path, $imgName);

                // LocationMedia::create([
                //     'location_id' => $location->id,
                //     'type' => $type,
                //     'path' => "files/locations/" . $imgName,
                // ]);

                FolderMedia::create([
                    'location_id' => $location->id,
                    'user_uuid' => $request->other_user_uuid,
                    'name' => $request->name,
                    'description' => $request->description,
                    'date' => $request->date,
                    'type' => $type,
                    'path' => "files/medias/" . $imgName,
                ]);
            }
        }
        
        $message = UserController::personalizedMessage($userId, "Local cadastrado (veja ele no mapa abaixo)!", "Local cadastrado, meu amor 💖 (veja ele no mapa abaixo)");
        return response()->json(["success" => true, "location" => $location, "message" => $message]);
    }

    public function update(Request $request, $id){
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'nullable|date',
            'name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user_uuid = $request->get('user_uuid');
        $userId = $request->get('user_id');
        // $location = Location::findOrFail($id);
        $location = Location::where('id', $id)
            ->where(function ($query) use ($user_uuid) {
                $query->where('user_uuid', $user_uuid)
                    ->orWhereExists(function ($q) use ($user_uuid) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where('location_users.user_uuid', $user_uuid);
                    });
            })
            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($userId, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $location->update($data);

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                // $path = $file->store('locations/media', 'public');
                $imgName = uniqid() . "." . $file->extension();
                $path = public_path('files/medias');
                $file->move($path, $imgName);

                FolderMedia::create([
                    'location_id' => $location->id,
                    'user_uuid' => $request->other_user_uuid,
                    'name' => $request->name,
                    'description' => $request->description,
                    'date' => $request->date,
                    'type' => $type,
                    'path' => "files/medias/" . $imgName,
                ]);

                // LocationMedia::create([
                //     'location_id' => $location->id,
                //     'type' => $type,
                //     'path' => "files/locations/" . $imgName,
                // ]);
            }
        }

        $imagesToDelete = json_decode($request->input('images_to_delete'), true);

        foreach ($imagesToDelete as $images) {
            // Caminho absoluto no sistema de arquivos
            $fullPath = public_path($images["path"]);

            // Apaga o arquivo fisicamente se existir
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            // Remove do banco
            $location->media()->where('path', $images["path"])->delete();
        }

        
        $message = UserController::personalizedMessage($userId, "Local atualizado (veja ele no mapa abaixo)!", "Local atualizado, meu amor 💖 (veja ele no mapa abaixo)");
        return response()->json([
            "success" => true,
            "message" => $message,
            "location" => $location,
        ]);
    }

    public function delete(Request $request, $id){
        $authUserId = $request->get('user_id');
        $user_uuid = $request->get('user_uuid');

        $location = Location::where('id', $id)
            ->where(function ($query) use ($user_uuid) {
                $query->where('user_uuid', $user_uuid)
                    ->orWhereExists(function ($q) use ($user_uuid) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where('location_users.user_uuid', $user_uuid);
                    });
            })
            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($authUserId, "Local não encontrado!", "Local não encontrado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        // Remove o relacionamento antes de deletar (se existir)
        DB::table('location_users')->where('location_id', $location->id)->delete();
        $location->delete();

        $message = UserController::personalizedMessage($authUserId, "Local excluído com sucesso 💔", "Local excluído com sucesso, meu amor 💔");
        return response()->json(['message' => '']);
    }

    private function getAuthUserIdentifier(Request $request){
        if ($request->has('user_uuid')) {
            return ['column' => 'user_uuid', 'value' => $request->get('user_uuid')];
        }

        return ['column' => 'user_id', 'value' => $request->get('user_id')];
    }

}
