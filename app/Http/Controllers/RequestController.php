<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillRequest;
use App\Models\UserMatch;
use App\Services\Matching\Recommender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $requests = SkillRequest::where('User_ID', $uid)->with('skill')->latest('Request_ID')->get();
        $appliedRequests = SkillRequest::whereHas('assignments', function ($q) use ($uid) {
            $q->where('User_ID', $uid)
                ->where('Status', '!=', 'Rejected');
        })
            ->with(['skill', 'assignments' => function ($q) use ($uid) {
                $q->where('User_ID', $uid)
                    ->where('Status', '!=', 'Rejected');
            }])
            ->get()
            ->sortBy(function ($r) {
                $status = $r->Status;
                $order = ['Pending' => 1, 'Completed' => 2, 'Cancelled' => 3];

                return $order[$status] ?? 4;
            })
            ->sortByDesc(function ($r) {
                return $r->Request_ID;
            })
            ->values();

        return view('requests.index', compact('requests', 'appliedRequests'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['integer', 'exists:skills,Skill_ID'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'service_mode' => ['required', 'string', 'in:Remote,Face-to-Face,Hybrid'],
        ]);
        $uid = Auth::id();
        $skillIds = $validated['skill_ids'];
        $primarySkillId = $skillIds[0];
        $requestModel = DB::transaction(function () use ($uid, $validated, $skillIds, $primarySkillId) {
            $requestModel = SkillRequest::create([
                'User_ID' => $uid,
                'Skill_ID' => $primarySkillId,
                'Title' => $validated['title'],
                'Description' => $validated['description'],
                'Status' => 'Open',
                'Service_Mode' => $validated['service_mode'],
                'Created_At' => now(),
            ]);
            $requestId = $requestModel->Request_ID;
            $requestModel->skills()->sync($skillIds);

            $recommendations = app(Recommender::class)->rankForRequest($requestModel, 20);

            foreach ($recommendations as $rec) {
                $provider = $rec['user'];
                UserMatch::updateOrCreate([
                    'Matched_User_ID' => $provider->User_ID,
                    'Request_ID' => $requestId,
                ], [
                    'Match_Score' => $rec['score'],
                ]);
                Notification::create([
                    'User_ID' => $provider->User_ID,
                    'Notif_Type' => 'Match',
                    'Message' => 'New request: '.$requestModel->Title,
                    'url' => '/requests/'.$requestId,
                ]);
            }

            return $requestModel;
        });

        return redirect()->route('requests.show', $requestModel->Request_ID)
            ->with('success', 'Request created. '.$requestModel->matches()->count().' provider(s) matched.')
            ->with('show_matched_providers', true);
    }

    public function show($id)
    {
        $request = SkillRequest::with(['skill', 'skills', 'user', 'assignments', 'matches.user'])->findOrFail($id);
        $applicants = [];
        if (Auth::id() == $request->User_ID) {
            // Get matched provider IDs to exclude them from applicants (they were requested, not applied)
            $matchedProviderIds = $request->matches->pluck('Matched_User_ID')->toArray();

            $applicants = Assignment::where('Request_ID', $id)
                ->whereIn('Status', ['Pending', 'Accepted'])
                ->whereNotIn('User_ID', $matchedProviderIds)
                ->with('user')
                ->get();
        }

        return view('requests.show', compact('request', 'applicants'));
    }

    public function edit($id)
    {
        $request = SkillRequest::with('skill')->findOrFail($id);
        $skills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();

        return view('requests.edit', compact('request', 'skills'));
    }

    public function update(Request $request, $id)
    {
        $skillRequest = SkillRequest::findOrFail($id);
        $validated = $request->validate([
            'skill_ids' => ['required', 'array', 'min:1'],
            'skill_ids.*' => ['integer', 'exists:skills,Skill_ID'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'service_mode' => ['required', 'string', 'in:Remote,Face-to-Face,Hybrid'],
        ]);
        $skillIds = $validated['skill_ids'];
        $skillRequest->update([
            'Skill_ID' => $skillIds[0],
            'Title' => $validated['title'],
            'Description' => $validated['description'],
            'Service_Mode' => $validated['service_mode'],
        ]);
        $skillRequest->skills()->sync($skillIds);

        return redirect()->route('requests.index')->with('success', 'Request updated.');
    }

    public function destroy($id)
    {
        $skillRequest = SkillRequest::with('assignments')->findOrFail($id);

        if ($skillRequest->User_ID != Auth::id()) {
            abort(403);
        }

        $hasActiveAssignment = $skillRequest->assignments->whereIn('Status', ['Pending', 'Accepted'])->isNotEmpty();
        if ($hasActiveAssignment) {
            return back()->with('error', 'Cannot delete request with pending or accepted applicants.');
        }

        DB::transaction(function () use ($id, $skillRequest) {
            UserMatch::where('Request_ID', $id)->delete();
            Assignment::where('Request_ID', $id)->delete();
            Review::where('Request_ID', $id)->delete();
            Report::where('Request_ID', $id)->delete();
            $skillRequest->delete();
        });

        return redirect()->route('requests.index')->with('success', 'Request deleted.');
    }

    public function complete($id)
    {
        $request = SkillRequest::with('assignments')->findOrFail($id);
        $uid = Auth::id();
        if ($uid != $request->User_ID) {
            abort(403);
        }
        $acceptedAssignment = $request->assignments->firstWhere('Status', 'Accepted');
        if (! $acceptedAssignment) {
            return back()->with('error', 'No accepted applicant found for this request.');
        }
        DB::transaction(function () use ($request, $acceptedAssignment) {
            $acceptedAssignment->update(['Status' => 'Completed', 'Completed_At' => now()]);
            $request->update(['Status' => 'Completed']);
            Notification::create([
                'User_ID' => $acceptedAssignment->User_ID,
                'Notif_Type' => 'Review',
                'Message' => 'You can now review '.$request->user->Full_Name.' for request: '.$request->Title,
                'url' => '/reviews/create/'.$acceptedAssignment->Assignment_ID,
            ]);
        });

        return redirect()->route('requests.show', $id)
            ->with('success', 'Request marked as completed.')
            ->with('show_review_prompt', true)
            ->with('review_request_id', $id)
            ->with('review_assignment_id', $acceptedAssignment->Assignment_ID);
    }

    public function fail($id)
    {
        $request = SkillRequest::with('assignments')->findOrFail($id);
        $uid = Auth::id();
        if ($uid != $request->User_ID) {
            abort(403);
        }

        $acceptedAssignment = $request->assignments->firstWhere('Status', 'Accepted');

        DB::transaction(function () use ($request, $acceptedAssignment) {
            if ($acceptedAssignment) {
                $acceptedAssignment->update(['Status' => 'Failed', 'Completed_At' => now()]);
                $providerId = $acceptedAssignment->User_ID;
                Notification::create([
                    'User_ID' => $providerId,
                    'Notif_Type' => 'Review',
                    'Message' => 'You can now review '.$request->user->Full_Name.' for request: '.$request->Title,
                    'url' => '/reviews/create/'.$acceptedAssignment->Assignment_ID,
                ]);
                Notification::create([
                    'User_ID' => $providerId,
                    'Notif_Type' => 'Failed Request',
                    'Message' => 'The request "'.$request->Title.'" was marked as failed by the requester. Please provide feedback.',
                    'url' => '/requests/'.$request->Request_ID.'/provider-feedback',
                ]);
            }
            $request->update(['Status' => 'Failed']);
        });

        return redirect()->route('requests.show', $id)
            ->with('success', 'Request marked as failed.')
            ->with('show_review_prompt', true)
            ->with('review_request_id', $id)
            ->with('review_assignment_id', $acceptedAssignment ? $acceptedAssignment->Assignment_ID : null);
    }

    public function providerFeedback($id)
    {
        $request = SkillRequest::with(['assignments' => function ($q) {
            $q->where('Status', 'Failed')->with('user');
        }])->findOrFail($id);
        $acceptedAssignment = $request->assignments->firstWhere('Status', 'Failed');
        if (! $acceptedAssignment) {
            return redirect()->route('requests.index')->with('error', 'No failed assignment found for this request.');
        }
        $provider = $acceptedAssignment->user;
        $requester = $request->user;

        return view('requests.provider-feedback', compact('request', 'provider', 'requester', 'acceptedAssignment'));
    }

    public function submitProviderFeedback(Request $request, $id)
    {
        $skillRequest = SkillRequest::with('assignments')->findOrFail($id);
        $acceptedAssignment = $skillRequest->assignments->firstWhere('Status', 'Failed');
        if (! $acceptedAssignment) {
            return back()->with('error', 'No failed assignment found for this request.');
        }

        $validated = $request->validate([
            'feedback_type' => ['required', 'in:rate,report'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:500'],
            'reason' => ['required_if:feedback_type,report', 'string'],
            'proof' => ['nullable', 'string'],
        ]);

        if ($validated['feedback_type'] === 'rate') {
            if (! $validated['rating'] || ! $validated['comment']) {
                return back()->with('error', 'Rating and comment are required.');
            }
            Review::create([
                'Reviewed_User_ID' => $skillRequest->User_ID,
                'Reviewer_ID' => Auth::id(),
                'Request_ID' => $id,
                'Rating' => $validated['rating'],
                'Comment' => $validated['comment'],
            ]);
            $avg = Review::where('Reviewed_User_ID', $skillRequest->User_ID)->avg('Rating');
            $user = User::find($skillRequest->User_ID);
            $user->Avg_Rating = round($avg, 2);
            $user->Total_Completed = ($user->Total_Completed ?? 0) + 1;
            $user->save();

            return redirect()->route('requests.show', $id)->with('success', 'Feedback submitted for the requester.');
        }

        Report::create([
            'Reporter_ID' => Auth::id(),
            'Reported_User_ID' => $skillRequest->User_ID,
            'Request_ID' => $id,
            'Reason' => $validated['reason'],
            'Proof' => $validated['proof'] ?? null,
            'Status' => 'Pending',
        ]);

return redirect()->route('requests.show', $id)->with('success', 'Report submitted for the requester.');
    }

    public function directAssign(Request $request, $id)
    {
        $skillRequest = SkillRequest::with(['assignments' => function ($q) {
            $q->whereIn('Status', ['Pending', 'Accepted']);
        }])->findOrFail($id);

        if ($skillRequest->User_ID !== Auth::id()) {
            abort(403);
        }

        if ($skillRequest->Status !== 'Open') {
            return back()->with('error', 'Can only request providers for open requests.');
        }

        $hasActiveAssignment = $skillRequest->assignments->whereIn('Status', ['Pending', 'Accepted'])->isNotEmpty();
        if ($hasActiveAssignment) {
            return back()->with('error', 'Request already has an active assignment or pending request.');
        }

        $validated = $request->validate([
            'provider_id' => ['required', 'integer', 'exists:users,User_ID'],
        ]);

        $providerId = $validated['provider_id'];

        $match = UserMatch::where('Request_ID', $id)
            ->where('Matched_User_ID', $providerId)
            ->first();

        if (! $match) {
            return back()->with('error', 'This provider was not matched to your request.');
        }

        DB::transaction(function () use ($skillRequest, $providerId, $match) {
            $assignment = Assignment::create([
                'Request_ID' => $skillRequest->Request_ID,
                'User_ID' => $providerId,
                'Status' => 'Pending',
                'Created_At' => now(),
            ]);

            Notification::create([
                'User_ID' => $providerId,
                'Notif_Type' => 'Assignment',
                'Message' => $skillRequest->user->Full_Name . ' requested you for: ' . $skillRequest->Title,
                'url' => '/requests/' . $skillRequest->Request_ID,
            ]);

            Notification::create([
                'User_ID' => $skillRequest->User_ID,
                'Notif_Type' => 'Assignment',
                'Message' => 'You requested ' . $match->user->Full_Name . ' for your request. They will need to accept.',
                'url' => '/requests/' . $skillRequest->Request_ID,
            ]);
        });

        return redirect()->route('requests.show', $id)->with('success', 'Request sent to provider. They will need to accept or reject.');
    }
}
