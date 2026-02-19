<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vote\StoreVoteRequest;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Vote;
use Illuminate\Http\JsonResponse;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function voteOnThread(StoreVoteRequest $request, Thread $thread): JsonResponse
    {
        $vote = $this->storeOrUpdateVote($request, $thread);

        return response()->json($vote);
    }

    public function voteOnComment(StoreVoteRequest $request, Comment $comment): JsonResponse
    {
        $vote = $this->storeOrUpdateVote($request, $comment);

        return response()->json($vote);
    }

    protected function storeOrUpdateVote(StoreVoteRequest $request, Thread|Comment $votable): Vote
    {
        $data = $request->validated();

        return Vote::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'votable_id' => $votable->id,
                'votable_type' => $votable::class,
            ],
            [
                'value' => $data['value'],
            ],
        );
    }
}
