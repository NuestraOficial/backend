<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\FolderMedia;
use App\Models\FolderUser;
use Illuminate\Http\Request;

class MediasController extends Controller
{
    public function index(Request $request){
        $auth = $this->getAuthUserIdentifier($request);

        $medias = FolderMedia::with("folder")->get();

        return response()->json(["medias" => $medias]);
    }

    public function findByFolderId(Request $request, $folder_id){
        $auth = $this->getAuthUserIdentifier($request);

        $folder = Folder::find($folder_id);
        if(!$folder){
            $message = UserController::personalizedMessage($auth, "Pasta não encontrada!", "Pasta não encontrada, meu amorzinho!");
            return response()->json(['message' =>  $message], 403);
        }

        $medias = FolderMedia::where('folder_id', $folder_id)->with("folder")->get();

        return response()->json(["medias" => $medias, "folder" => $folder]);
    }
    
    public function find(Request $request, $id){
        $media = FolderMedia::with("folder")->find( $id);

        return response()->json(["media" => $media]);
    }

    public function store(Request $request){
        $userId = $request->get('user_id');
        $user_uuid = $request->get("user_uuid");

        $request->validate([
            'other_user_uuid' => 'nullable|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'folder_id' => 'nullable|numeric',
            'folder_name' => 'nullable|string|max:250',
            'date' => 'nullable|date',
            "media*" => "file|mimes:jpeg,png,jpg,mp4,webm|max:10240"
        ]);

        if($request->has("folder_id") && $request->folder_id >0){
            $folder = Folder::find($request->folder_id);
        }

        if((!$request->has("folder_id") || $request->folder_id <= 0) && $request->folder_name){
            $folder = Folder::create([
                "user_uuid" => $user_uuid,
                "name" => $request->folder_name, 
                "total_files" => count($request->media) 
            ]);

            FolderUser::create([
                "folder_id" => $folder->id,
                "user_uuid" => $request->other_user_uuid,
            ]);
        }

        // Salvar mídias
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $type = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
                // $path = $file->store('locations/media', 'public');

                $imgName = uniqid() . "." . $file->extension();
                $path = public_path('files/medias');
                $file->move($path, $imgName);

                FolderMedia::create([
                    'user_uuid' => $request->other_user_uuid,
                    'folder_id' => isset($folder) ? $folder->id : null,
                    'name' => $request->name,
                    'description' => $request->description,
                    'date' => $request->date,
                    'type' => $type,
                    'path' => "files/medias/" . $imgName,
                ]);
            }
        }

        $message = UserController::personalizedMessage($userId, "Registro cadastrado!", "Registro cadastrado, meu amor 💖");
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
        if (!count($ids)) return response()->json(["message" => "Nenhuma pasta selecionada"], 400);

        FolderMedia::whereIn('id', $ids)->delete();
        return response()->json(["message" => "Pastas removidas com sucesso"]);
    }
}
