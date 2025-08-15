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
}
