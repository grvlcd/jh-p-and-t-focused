<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Thread\StoreThreadRequest;
use App\Http\Requests\Thread\UpdateThreadRequest;
use App\Models\Thread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Thread::query()
            ->with(['protocol', 'author'])
            ->withCount(['comments'])
            ->withSum('votes as votes_sum', 'value');

        $search = $request->query('search');

        if ($search !== null && $search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        $sort = $request->query('sort', 'recent');

        if ($sort === 'most_upvoted') {
            $query->orderByDesc('votes_sum');
        } else {
            $query->latest();
        }

        $threads = $query->paginate();

        return response()->json($threads);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThreadRequest $request): JsonResponse
    {
        $data = $request->validated();

        $thread = Thread::query()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'protocol_id' => $data['protocol_id'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json($thread->load(['protocol', 'author']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Thread $thread): JsonResponse
    {
        $thread->load(['protocol', 'author', 'comments']);

        return response()->json($thread);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThreadRequest $request, Thread $thread): JsonResponse
    {
        $thread->update($request->validated());

        return response()->json($thread);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Thread $thread): JsonResponse
    {
        $user = $request->user();
        if (!$user || $thread->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $thread->delete();

        return response()->json([], 204);
    }
}
