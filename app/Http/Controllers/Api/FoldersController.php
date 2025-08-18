<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;

class FoldersController extends Controller
{
    public function index(Request $request){
        $folders = Folder::all();
        return response()->json(["folders" => $folders]);
    }

    public function find(Request $request, $id){
        $folder = Folder::find($id);
        if(!$folder) return response()->json(["message" => "Pasta não encontrada"], 404);
        
        return response()->json(["folder" => $folder]);
    }
    
    public function update(Request $request, $id){
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $folder = Folder::find($id);
        if(!$folder) return response()->json(["message" => "Pasta não encontrada"], 404);

        $folder->update($request->only(["name", "description"]));
        
        return response()->json(["folder" => $folder, "message" => "Pasta atualizada"]);
    }

    public function delete(Request $request) {
        $ids = $request->input('ids', []);
        if (!count($ids)) return response()->json(["message" => "Nenhuma pasta selecionada"], 400);

        Folder::whereIn('id', $ids)->delete();
        return response()->json(["message" => "Pastas removidas com sucesso"]);
    }
}
