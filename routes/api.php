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
});

Route::get('protocols', [ProtocolController::class, 'index']);
Route::get('protocols/{protocol}', [ProtocolController::class, 'show']);

Route::get('threads/{thread}/comments', [CommentController::class, 'indexForThread']);
Route::get('threads', [ThreadController::class, 'index']);
Route::get('threads/{thread}', [ThreadController::class, 'show']);
Route::put('threads/{thread}', [ThreadController::class, 'update']);

Route::get('protocols/{protocol}/reviews', [ReviewController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('protocols', [ProtocolController::class, 'store']);
    Route::put('protocols/{protocol}', [ProtocolController::class, 'update']);
    Route::delete('protocols/{protocol}', [ProtocolController::class, 'destroy']);
    Route::post('threads', [ThreadController::class, 'store']);
    Route::delete('threads/{thread}', [ThreadController::class, 'destroy']);
    Route::post('comments', [CommentController::class, 'store']);
    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);
    Route::post('threads/{thread}/vote', [VoteController::class, 'voteOnThread']);
    Route::post('comments/{comment}/vote', [VoteController::class, 'voteOnComment']);
});
