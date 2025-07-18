<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    // usado localmente
    public static function verifyTokenInternal($token)
    {
        if(App::environment('local')){
            return [
                'valid' => false
            ];
        }
        $record = DB::table('user_tokens')->where('token', $token)->first();
        $user = User::find($record->user_id);

        if (!$record) {
            return [
                'valid' => false
            ];
        }

        return [
            'valid' => true,
            'id' => $record->user_id,
            'name' => $user->name ?? null,
            'email' => $email->email ?? null,
            'token_id' => $record->id,
        ];
    }
}
