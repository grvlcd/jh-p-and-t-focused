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
        $result = $this->storeOrUpdateVote($request, $thread);

        $votesSum = (int) $thread->votes()->sum('value');

        return response()->json([
            'value' => $result ? $result->value : null,
            'votes_sum' => $votesSum,
        ]);
    }

    public function voteOnComment(StoreVoteRequest $request, Comment $comment): JsonResponse
    {
        $result = $this->storeOrUpdateVote($request, $comment);

        $votesSum = (int) $comment->votes()->sum('value');

        return response()->json([
            'value' => $result ? $result->value : null,
            'votes_sum' => $votesSum,
        ]);
    }

    protected function storeOrUpdateVote(StoreVoteRequest $request, Thread|Comment $votable): ?Vote
    {
        $data = $request->validated();
        $userId = $request->user()->id;

        // Check if user already voted
        $existingVote = Vote::query()
            ->where('user_id', $userId)
            ->where('votable_id', $votable->id)
            ->where('votable_type', $votable::class)
            ->first();

        if ($existingVote) {
            // If voting the same value, remove the vote (toggle off)
            if ($existingVote->value === $data['value']) {
                $existingVote->delete();
                return null;
            }
            // Otherwise, update the vote value
            $existingVote->update(['value' => $data['value']]);
            return $existingVote->fresh();
        }

        // Create new vote
        return Vote::query()->create([
            'user_id' => $userId,
            'votable_id' => $votable->id,
            'votable_type' => $votable::class,
            'value' => $data['value'],
        ]);
    }
}
