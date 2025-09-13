<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FolderUser;
use App\Models\Location;
use App\Models\Media;
use Illuminate\Http\Request;

class MediasController extends Controller
{
    public function index(Request $request){
        $medias = Media::with("folder")->orderBy("date", "DESC")->get();
        return response()->json(["medias" => $medias]);
    }

    public function findByFolderId(Request $request, $folder_id){
        $auth = $this->getAuthUserIdentifier($request);

        $folder = Folder::find($folder_id);
        if(!$folder){
            $message = UserController::personalizedMessage($auth, "Pasta não encontrada!", "Pasta não encontrada, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $medias = Media::where('folder_id', $folder_id)->with("folder")->orderBy("date", "DESC")->get();

        return response()->json(["medias" => $medias, "folder" => $folder]);
    }
    
    public function find(Request $request, $id){
        $media = Media::with(["folder", "location", "moment"])->find( $id);
        return response()->json(["media" => $media]);
    }

    public function store(Request $request){
        $userId = $request->get('user_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'location_id' => 'nullable|numeric',
            'folder_id' => 'nullable|numeric',
            'folder_name' => 'nullable|string|max:250',
            'date' => 'nullable|date',
            "media*" => "file|mimes:jpeg,png,jpg,mp4,webm|max:10240",
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable',
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

        if($request->has("folder_id") && $request->folder_id >0){
            $folder = Folder::find($request->folder_id);
            $folder->total_files = $folder->total_files + count($request->media);
            $folder->save();
        }

        if((!$request->has("folder_id") || $request->folder_id <= 0) && $request->folder_name){
            $folder = Folder::create([
                "user_id" => $userId,
                "description" => $request->description, 
                "name" => $request->folder_name, 
                "total_files" => count($request->media) 
            ]);
        }

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';

                $imgName = uniqid() . "." . $file->extension();
                $path = public_path('files/medias');
                $file->move($path, $imgName);

                Media::create([
                    'user_id' => $userId,
                    'folder_id' => isset($folder) ? $folder->id : null,
                    'name' => $request->name,
                    'description' => $request->description,
                    'date' => $request->date,
                    'type' => $type,
                    'path' => "files/medias/" . $imgName,
                    "location_id" => $request->location_id ?: null,
                ]);
            }
        }

        $message = UserController::personalizedMessage($userId, "Registro cadastrado!", "Registro cadastrado, meu amor 💖");
        return response()->json(["success" => true, "folder" => isset($folder) ? $folder : null, "message" => $message]);
    }

    public function update(Request $request, $id){
        $userId = $request->get('user_id');

        $request->validate([
            'name' => 'required|string|max:255',
            'moment_id' => 'nullable|numeric',
            'location_id' => 'nullable|numeric',
            'folder_id' => 'nullable|numeric',
            'folder_name' => 'nullable|string|max:250',
            'date' => 'nullable|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable',
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

        $media = Media::find($id);
        if(!$media) return response()->json(["message" => "Registro não encontrado"], 404);
        
        $media->update([
            "name" => $request->name,
            "folder_id" => $request->folder_id,
            "description" => $request->description,
            "date" => $request->date,
            "location_id" => $request->location_id ?: null,
        ]);

        if($request->has("folder_id") && $request->folder_id >0){
            $folder = Folder::find($request->folder_id);
            if($media->folder_id != $request->folder_id){
                $folder->increment("total_files");
            }
        }

        // criando uma pasta
        if((!$request->has("folder_id") || $request->folder_id <= 0) && $request->folder_name){
            $folder = Folder::create([
                "user_id" => $userId,
                "description" => $request->description, 
                "name" => $request->folder_name, 
                "total_files" => 1 
            ]);

            $media->folder_id = $folder->id;
            $media->save();
        }


        $message = UserController::personalizedMessage($userId, "Registro atualizado!", "Registro atualizado, meu amor 💖");
        return response()->json(["success" => true, "folder" => isset($folder) ? $folder : null, "message" => $message]);
    }
    
    private function getAuthUserIdentifier(Request $request){
        if ($request->has('user_uuid')) {
            return ['column' => 'user_uuid', 'value' => $request->get('user_uuid')];
        }

        return ['column' => 'user_id', 'value' => $request->get('user_id')];
    }

    public function delete(Request $request) {
        $ids = $request->input('ids', []);
        if (!count($ids)) return response()->json(["message" => "Nenhuma media selecionada"], 400);

        Media::whereIn('id', $ids)->delete();
        return response()->json(["message" => "Medias removidas com sucesso"]);
    }
}
