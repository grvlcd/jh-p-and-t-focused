<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProtocolController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('protocols', ProtocolController::class);
    Route::apiResource('threads', ThreadController::class);

    Route::get('threads/{thread}/comments', [CommentController::class, 'indexForThread']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    Route::get('protocols/{protocol}/reviews', [ReviewController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

    Route::post('threads/{thread}/vote', [VoteController::class, 'voteOnThread']);
    Route::post('comments/{comment}/vote', [VoteController::class, 'voteOnComment']);
});
