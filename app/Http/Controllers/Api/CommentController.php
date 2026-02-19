<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexForThread(Thread $thread): JsonResponse
    {
        $comments = $thread->comments()
            ->with(['author', 'children'])
            ->get();

        return response()->json($comments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $data = $request->validated();

        $comment = Comment::query()->create([
            'body' => $data['body'],
            'thread_id' => $data['thread_id'],
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return response()->json($comment->load(['author', 'children']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $comment->update($request->validated());

        return response()->json($comment->load(['author', 'children']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([], 204);
    }
}
