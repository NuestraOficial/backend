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
    
    public function find(Request $request, $id){
        $message = Message::find($id);
        return response()->json(["message" => $message]);
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

        $messageResponse = UserController::personalizedMessage($userId, "Mensagem criada!", "Mensagem criada, meu amorzinho!");
        return response()->json(["message" => $messageResponse, "data" => $message]);
    }
    
    public function update(Request $request, $id){
        $userId = $request->get("user_id");
        $data = $request->validate([
            "text" => "nullable", 
            "from_id" => "nullable|numeric", 
            "date" => "nullable|date", 
        ]);

        $message = Message::find($id);
        if($message){
            $messageResponse = UserController::personalizedMessage($userId, "Mensagem não encontrada!", "Mensagem não encontrada, meu amorzinho!");
            return response()->json(["message" => $messageResponse], 404);
        }

        $data["user_id"] = $userId;
        $message->update($data);

        $message = UserController::personalizedMessage($userId, "Mensagem atualizada!", "Mensagem atualizada, meu amorzinho!");
        return response()->json(["message" => $message, "data" => $message]);
    }

    public function delete(Request $request) {
        $ids = $request->input('ids', []);
        if (!count($ids)) return response()->json(["message" => "Nenhuma mensagem selecionada"], 400);

        Message::whereIn('id', $ids)->delete();
        return response()->json(["message" => "Mensagens removidas com sucesso"]);
    }
}
