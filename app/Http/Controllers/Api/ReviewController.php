<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Review\UpdateReviewRequest;
use App\Models\Protocol;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Protocol $protocol): JsonResponse
    {
        $reviews = $protocol->reviews()
            ->with('author')
            ->get();

        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $data = $request->validated();

        $review = Review::query()->updateOrCreate(
            [
                'protocol_id' => $data['protocol_id'],
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => $data['rating'],
                'feedback' => $data['feedback'] ?? null,
            ],
        );

        return response()->json($review->load(['protocol', 'author']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function update(UpdateReviewRequest $request, Review $review): JsonResponse
    {
        $review->update($request->validated());

        return response()->json($review->load(['protocol', 'author']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json([], 204);
    }
}
