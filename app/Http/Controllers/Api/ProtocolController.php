<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Protocol\StoreProtocolRequest;
use App\Http\Requests\Protocol\UpdateProtocolRequest;
use App\Models\Protocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProtocolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Protocol::query()
            ->withCount(['threads', 'reviews'])
            ->withAvg('reviews as reviews_avg_rating', 'rating');

        $search = $request->query('search');

        if ($search !== null && $search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhereHas('threads', function ($threadQuery) use ($term) {
                        $threadQuery->where('title', 'like', $term)
                            ->orWhere('body', 'like', $term);
                    });
            });
        }

        $sort = $request->query('sort', 'recent');

        if ($sort === 'most_reviewed') {
            $query->orderByDesc('reviews_count');
        } elseif ($sort === 'highest_rated') {
            $query->orderByDesc('reviews_avg_rating');
        } else {
            $query->latest();
        }

        $perPage = min((int) $request->query('per_page', 15), 50);
        $perPage = $perPage < 1 ? 15 : $perPage;
        $protocols = $query->paginate($perPage);

        return response()->json($protocols);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProtocolRequest $request): JsonResponse
    {
        $data = $request->validated();

        $protocol = Protocol::query()->create([
            'title' => $data['title'],
            'content' => $data['content'],
            'tags' => $data['tags'] ?? [],
            'author_id' => $request->user()->id,
            'rating' => $data['rating'] ?? null,
        ]);

        return response()->json($protocol, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Protocol $protocol): JsonResponse
    {
        $protocol->load(['author', 'threads', 'reviews']);

        // Load user's votes for threads if authenticated
        if ($request->user()) {
            $threadIds = $protocol->threads->pluck('id');
            $userVotes = \App\Models\Vote::query()
                ->where('user_id', $request->user()->id)
                ->where('votable_type', \App\Models\Thread::class)
                ->whereIn('votable_id', $threadIds)
                ->get()
                ->keyBy('votable_id');

            foreach ($protocol->threads as $thread) {
                $vote = $userVotes->get($thread->id);
                $thread->user_vote = $vote ? $vote->value : null;
            }
        }

        return response()->json($protocol);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProtocolRequest $request, Protocol $protocol): JsonResponse
    {
        $protocol->update($request->validated());

        return response()->json($protocol);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Protocol $protocol): JsonResponse
    {
        $protocol->delete();

        return response()->json([], 204);
    }
}
