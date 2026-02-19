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
            $query->where('title', 'like', '%'.$search.'%');
        }

        $sort = $request->query('sort', 'recent');

        if ($sort === 'most_reviewed') {
            $query->orderByDesc('reviews_count');
        } elseif ($sort === 'highest_rated') {
            $query->orderByDesc('reviews_avg_rating');
        } else {
            $query->latest();
        }

        $protocols = $query->paginate();

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
    public function show(Protocol $protocol): JsonResponse
    {
        $protocol->load(['author', 'threads', 'reviews']);

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
