<?php

use App\Http\Controllers\Api\FoldersController;
use App\Http\Controllers\Api\LocationsController;
use App\Http\Controllers\Api\MediasController;
use App\Http\Controllers\Api\MessagesController;
use App\Http\Controllers\Api\MomentsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return response()->json("ola mundo");
});

Route::middleware(['check_user_token'])->group(function () {
    Route::group(["prefix" => "moments"], function(){
        Route::get("", [MomentsController::class, "index"]);
        Route::post("", [MomentsController::class, "store"]);
        Route::get('/{id}', [MomentsController::class, 'find']);
        Route::post('/{id}', [MomentsController::class, 'update']);
        Route::delete('/{id}', [MomentsController::class, 'delete']);
    });

    Route::group(["prefix" => "locations"], function(){
        Route::get("", [LocationsController::class, "index"]);
        Route::post("", [LocationsController::class, "store"]);
        Route::get('/{id}', [LocationsController::class, 'find']);
        Route::post('/{id}', [LocationsController::class, 'update']);
        Route::delete('/{id}', [LocationsController::class, 'delete']);
    });

    Route::group(["prefix" => "medias"], function(){
        Route::get("", [MediasController::class, "index"]);
        Route::post("", [MediasController::class, "store"]);
        Route::get('/{id}', [MediasController::class, 'find']);
        Route::get('/by-folder/{folder_id}', [MediasController::class, 'findByFolderId']);
        Route::post('/{id}', [MediasController::class, 'update']);
        Route::delete('', [MediasController::class, 'delete']);
    });

    Route::group(["prefix" => "folders"], function(){
        Route::get("", [FoldersController::class, "index"]);
        Route::get("/{id}", [FoldersController::class, "find"]);
        Route::post("/{id}", [FoldersController::class, "update"]);
        Route::delete("", [FoldersController::class, "delete"]);
    });

    Route::group(["prefix" => "messages"], function(){
        Route::get("", [MessagesController::class, "index"]);
        Route::get("/{id}", [MessagesController::class, "find"]);
        Route::post("", [MessagesController::class, "store"]);
        Route::post("/{id}", [MessagesController::class, "update"]);
        Route::delete("", [MessagesController::class, "delete"]);
    });
});