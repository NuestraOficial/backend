<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessagesController extends Controller
{
    public function index(Request $request){
        $messages = Message::orderByDesc("date")->get();
        return response()->json($messages);
    }
    public function store(Request $request){
        $userId = $request->get("user_id");
        $data = $request->validate([
            "text" => "nullable", 
            "from_id" => "nullable|numeric", 
            "date" => "nullable|date", 
        ]);

        $data["user_id"] = $userId;
        $message = Message::create($data);

        $message = UserController::personalizedMessage($userId, "Mensagem criada!", "Mensagem criada, meu amorzinho!");
        return response()->json(["message" => $message, "data" => $message]);
    }
}
