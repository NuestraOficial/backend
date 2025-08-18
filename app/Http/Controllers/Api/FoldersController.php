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

    public function delete(Request $request) {
        $ids = $request->input('ids', []);
        if (!count($ids)) return response()->json(["message" => "Nenhuma pasta selecionada"], 400);

        Folder::whereIn('id', $ids)->delete();
        return response()->json(["message" => "Pastas removidas com sucesso"]);
    }

}
