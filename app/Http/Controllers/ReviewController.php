<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $reviews = Review::where('Reviewed_User_ID', $uid)
            ->with(['reviewer', 'request'])
            ->latest('Created_At')
            ->get();

        return view('reviews.index', compact('reviews'));
    }

    public function create($assignmentId)
    {
        $assignment = Assignment::with(['request', 'request.user', 'user'])->findOrFail($assignmentId);
        $uid = Auth::id();

        if ($uid !== $assignment->User_ID && $uid !== $assignment->request->User_ID) {
            abort(403);
        }

        $hasReviewed = Review::where('Request_ID', $assignment->Request_ID)
            ->where('Reviewer_ID', $uid)
            ->exists();

        if ($hasReviewed) {
            return back()->with('error', 'You have already reviewed this request.');
        }

        $ratedId = $uid == $assignment->User_ID ? $assignment->request->User_ID : $assignment->User_ID;
        $ratedUser = User::find($ratedId);

        return view('reviews.create', compact('assignment', 'ratedUser'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => ['required', 'integer', 'exists:skill_requests,Request_ID'],
            'rated_user_id' => ['required', 'integer', 'exists:users,User_ID'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ]);
        Review::create([
            'Reviewed_User_ID' => $validated['rated_user_id'],
            'Reviewer_ID' => Auth::id(),
            'Request_ID' => $validated['request_id'],
            'Rating' => $validated['rating'],
            'Comment' => $validated['comment'],
        ]);
        $avg = Review::where('Reviewed_User_ID', $validated['rated_user_id'])->avg('Rating');
        $user = User::find($validated['rated_user_id']);
        $user->Avg_Rating = round($avg, 2);
        $user->Total_Completed = ($user->Total_Completed ?? 0) + 1;
        $user->save();

        return redirect()->route('reviews.index')->with('success', 'Review submitted.');
    }
}
