<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationMedia;
use App\Models\LocationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LocationsController extends Controller
{
    public function index(Request $request){
        $authUserId = $request->get('user_id');

        $locations = Location::where(function ($query) use ($authUserId) {
            $query->where('user_id', $authUserId)
                ->orWhereExists(function ($q) use ($authUserId) {
                    $q->select(DB::raw(1))
                        ->from('location_users')
                        ->whereColumn('location_users.location_id', 'locations.id')
                        ->where('location_users.user_id', $authUserId);
                });
        })->get();

        return response()->json($locations);
    }


    public function find(Request $request, $id){
        $authUserId = $request->get('user_id');

        $location = Location::where('id', $id)->with("media")
            ->where(function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId)
                    ->orWhereExists(function ($q) use ($authUserId) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where('location_users.user_id', $authUserId);
                    });
            })
            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($authUserId, "Local não encontrado ou não autorizado!", "Local não encontrado ou não autorizado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $location_users = LocationUser::where("location_id", $location->id)->get();
        return response()->json(["location" => $location, "location_users" => $location_users]);
    }


    public function store(Request $request){
        $userId = $request->get('user_id');

        $data = $request->validate([
            'id_user'     => 'required|integer',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'name' => 'nullable|string|max:255',
            'date' => 'nullable|date',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'media.*' => 'file|mimes:jpeg,png,jpg,mp4,webm|max:10240' // até 10MB por arquivo
        ]);

        $data['user_id'] = $userId;

        $location = Location::create($data);
        LocationUser::create([
            "location_id" => $location->id,
            "user_id" => $request->id_user,
        ]);

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                $path = $file->store('locations/media', 'public');

                LocationMedia::create([
                    'location_id' => $location->id,
                    'type' => $type,
                    'path' => $path,
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

        $userId = $request->get('user_id');
        // $location = Location::findOrFail($id);
        $location = Location::where('id', $id)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereExists(function ($q) use ($userId) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where('location_users.user_id', $userId);
                    });
            })
            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($userId, "Local não encontrado ou não autorizado!", "Local não encontrado ou não autorizado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $location->update($data);

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                $path = $file->store('locations/media', 'public');

                LocationMedia::create([
                    'location_id' => $location->id,
                    'type' => $type,
                    'path' => $path,
                ]);
            }
        }

        $imagesToDelete = json_decode($request->input('images_to_delete'), true);

        \Log::info($imagesToDelete);

        foreach ($imagesToDelete as $images) {
            \Log::info($images);
            // Excluir do storage e do banco, exemplo:
            $fileName = basename($images["path"]);
            Storage::disk('public')->delete("locations/$fileName");

            // Também remove do banco, se necessário
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

        $location = Location::where('id', $id)
            ->where(function ($query) use ($authUserId) {
                $query->where('user_id', $authUserId)
                    ->orWhereExists(function ($q) use ($authUserId) {
                        $q->select(DB::raw(1))
                            ->from('location_users')
                            ->whereColumn('location_users.location_id', 'locations.id')
                            ->where('location_users.user_id', $authUserId);
                    });
            })
            ->first();

        if (!$location) {
            $message = UserController::personalizedMessage($authUserId, "Local não encontrado ou não autorizado!", "Local não encontrado ou não autorizado, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        // Remove o relacionamento antes de deletar (se existir)
        DB::table('location_users')->where('location_id', $location->id)->delete();
        $location->delete();

        $message = UserController::personalizedMessage($authUserId, "Local excluído com sucesso 💔", "Local excluído com sucesso, meu amor 💔");
        return response()->json(['message' => '']);
    }
}
