<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexForThread(Request $request, Thread $thread): JsonResponse
    {
        $comments = $thread->comments()
            ->with(['author'])
            ->withSum('votes as votes_sum', 'value')
            ->get();

        // Load user's votes for comments if authenticated
        if ($request->user()) {
            $commentIds = $comments->pluck('id');
            $userVotes = \App\Models\Vote::query()
                ->where('user_id', $request->user()->id)
                ->where('votable_type', \App\Models\Comment::class)
                ->whereIn('votable_id', $commentIds)
                ->get()
                ->keyBy('votable_id');

            foreach ($comments as $comment) {
                $vote = $userVotes->get($comment->id);
                $comment->user_vote = $vote ? $vote->value : null;
            }
        }

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
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        $user = $request->user();
        if (!$user || $comment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([], 204);
    }
}
