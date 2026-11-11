@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header" style="margin-bottom:24px;">
    @php
      $ownerPic = $request->user->Profile_Picture ? asset('storage/'.$request->user->Profile_Picture) : asset('images/default-avatar.svg');
    @endphp
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
      <img src="{{ $ownerPic }}" alt="{{ $request->user->Full_Name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
      <div>
        <div class="card-title">{{ $request->user->Full_Name }}</div>
        <div style="color:var(--muted); font-size:0.85rem;">Request: {{ $request->Title }}</div>
      </div>
      <span class="badge badge-{{ strtolower($request->Status) }}">{{ $request->Status }}</span>
    </div>
  </div>

  <div class="card" style="margin-bottom:24px;">
    <div style="margin-bottom:16px;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div style="flex:1;">
          <div style="font-size:0.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">Skills</div>
          @php $allSkills = $request->skills->merge([$request->skill])->unique('Skill_ID')->values(); @endphp
          <div style="display:flex; flex-wrap:wrap; gap:6px;">
            @foreach ($allSkills as $s)
              <span class="badge badge-open" style="font-size:0.8rem;">{{ $s->Skill_Title }}</span>
            @endforeach
          </div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:0.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">Service Mode</div>
          <span class="badge badge-{{ strtolower(str_replace('-', '', str_replace(' ', '-', $request->Service_Mode ?? 'Remote'))) }}">{{ $request->Service_Mode ?? 'Remote' }}</span>
        </div>
      </div>
      <div style="margin-top:16px;">
        <div style="font-size:0.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">Description</div>
        <p style="color:var(--text); line-height:1.7;">{{ $request->Description }}</p>
      </div>
      <div style="margin-top:16px; display:flex; gap:8px; flex-wrap:wrap;">
        <div style="font-size:0.85rem; color:var(--muted);"><strong>Requested by:</strong> {{ $request->user->Full_Name ?? 'N/A' }}</div>
      </div>
    </div>
  </div>

  @if (auth()->id() == $request->User_ID && $request->assignments->where('Status', 'Accepted')->isNotEmpty())
    @php
      $acceptedAssignment = $request->assignments->firstWhere('Status', 'Accepted');
      $provider = $acceptedAssignment->user;
      $providerPic = $provider->Profile_Picture ? asset('storage/'.$provider->Profile_Picture) : asset('images/default-avatar.svg');
    @endphp
    <div class="card" style="margin-bottom:24px; background:#F0FDF4; border:1px solid #BBF7D0;">
      <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
        <img src="{{ $providerPic }}" alt="{{ $provider->Full_Name }}" style="width:56px; height:56px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); background:var(--bg);" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
        <div style="flex:1; min-width:200px;">
          <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
            <h3 style="margin:0; font-size:1.1rem; color:var(--primary);">{{ $provider->Full_Name }}</h3>
            <span class="badge badge-accepted">Assigned</span>
          </div>
          <p style="margin:4px 0 0; color:var(--muted); font-size:0.9rem;">{{ $provider->Email }}</p>
          <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('messages.index', ['with' => $provider->User_ID]) }}" class="btn btn-primary btn-sm">Message</a>
            <a href="{{ route('profile.show', $provider->User_ID) }}" class="btn btn-secondary btn-sm">View Profile</a>
          </div>
        </div>
        <div style="margin-left:auto;">
          <div style="font-size:0.85rem; color:var(--muted);">Rating: {{ number_format((float)($provider->Avg_Rating ?? 0), 2) }}/5</div>
          <div style="font-size:0.85rem; color:var(--muted);">{{ $provider->Total_Completed ?? 0 }} completed</div>
        </div>
      </div>
    </div>

    <div class="card" style="margin-bottom:24px; background:#F0FDF4; border:1px solid #BBF7D0;">
      <div style="display:flex; align-items:flex-start; gap:12px;">
        <div style="font-size:2rem;">📝</div>
        <div style="flex:1;">
          <strong>Work in Progress</strong>
          <p style="color:var(--muted); margin:6px 0 12px; font-size:0.95rem;">Once the work is done, mark this request as completed. If the request failed or wasn't fulfilled, mark it as failed.</p>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <form method="POST" action="{{ route('requests.complete', $request->Request_ID) }}" style="display:inline;">
              @csrf
              <button type="submit" class="btn btn-success">Mark as Completed</button>
            </form>
            <form method="POST" action="{{ route('requests.fail', $request->Request_ID) }}" style="display:inline;" onsubmit="return confirm('Mark this request as failed? This will notify the assigned user.');">
              @csrf
              <button type="submit" class="btn btn-danger">Mark as Failed</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

  @php
    $pendingAssignment = $request->assignments->firstWhere('Status', 'Pending');
    $isAssignedProvider = $pendingAssignment && auth()->id() == $pendingAssignment->User_ID && auth()->id() != $request->User_ID;
  @endphp

  @if ($isAssignedProvider)
    <div class="card" style="margin-bottom:24px; background:#FEF3C7; border:1px solid #FCD34D;">
      <div style="display:flex; align-items:flex-start; gap:12px;">
        <div style="font-size:2rem;">📋</div>
        <div style="flex:1;">
          <strong>You have a pending request</strong>
          <p style="color:var(--muted); margin:6px 0 12px; font-size:0.95rem;">{{ $request->user->Full_Name }} requested you for this task. Please accept or decline.</p>
          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <form method="POST" action="{{ route('assignments.provider-accept', $pendingAssignment->Assignment_ID) }}" style="display:inline;">
              @csrf
              <button type="submit" class="btn btn-success">Accept</button>
            </form>
            <form method="POST" action="{{ route('assignments.provider-reject', $pendingAssignment->Assignment_ID) }}" style="display:inline;" onsubmit="return confirm('Decline this request?');">
              @csrf
              <button type="submit" class="btn btn-danger">Decline</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endif

  @if (auth()->id() == $request->User_ID && ($request->assignments->isEmpty() || $request->assignments->whereIn('Status', ['Pending', 'Accepted'])->isEmpty()))
    <div class="card" style="margin-bottom:24px; background:#FEF2F2; border:1px solid #FECACA;">
      <form method="POST" action="{{ route('requests.destroy', $request->Request_ID) }}" onsubmit="return confirm('Delete this request? This action cannot be undone.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-block">Delete Request</button>
      </form>
    </div>
  @endif

  @if (session('show_review_prompt') && auth()->id() == $request->User_ID)
    @php
      $reviewRequestId = session('review_request_id');
      $reviewAssignmentId = session('review_assignment_id');
      $hasReviewed = \App\Models\Review::where('Request_ID', $reviewRequestId)->where('Reviewer_ID', auth()->id())->exists();
    @endphp
    @if ($reviewAssignmentId && ! $hasReviewed)
    <div id="review-prompt-modal-overlay" class="review-prompt-modal-overlay" onclick="if(event.target===this)closeReviewPromptModal()">
      <div class="review-prompt-modal">
        <div class="review-prompt-modal-header">
          <h3>Leave a Review</h3>
          <button type="button" class="review-prompt-modal-close" onclick="closeReviewPromptModal()">&times;</button>
        </div>
        <div class="review-prompt-modal-body">
          <p>The request has been <strong>{{ $request->Status === 'Completed' ? 'completed' : 'failed' }}</strong>. Would you like to leave a review for the other party now?</p>
        </div>
        <div class="review-prompt-modal-footer">
          <form method="POST" action="{{ route('assignments.review', $reviewAssignmentId) }}" style="display:flex; flex-direction:column; gap:8px; width:100%;">
            @csrf
            <input type="hidden" name="rating" id="review-rating-value" value="5">
            <div id="review-rating-stars" class="star-rating">
              <button type="button" class="star active" onclick="setReviewRating(1)" title="1 Star">★</button>
              <button type="button" class="star" onclick="setReviewRating(2)" title="2 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(3)" title="3 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(4)" title="4 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(5)" title="5 Stars">★</button>
            </div>
            <div style="font-size:0.75rem; color:var(--muted); margin-top:4px;">Rate from 1 to 5 stars</div>
            <textarea name="comment" placeholder="Write a review (optional)..." style="padding:6px;border:1px solid #ddd;border-radius:6px;font-size:0.9rem;margin-top:6px;width:100%;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:8px;">
              <button type="button" class="btn btn-secondary btn-sm" onclick="closeReviewPromptModal()">Later</button>
              <button type="submit" class="btn btn-primary btn-sm">Review Now</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('review-prompt-modal-overlay').classList.add('active');
      });

      function setReviewRating(value) {
        document.getElementById('review-rating-value').value = value;
        document.querySelectorAll('#review-rating-stars .star').forEach((star, idx) => {
          star.classList.toggle('active', idx < value);
        });
      }
    </script>
    @endif
  @endif

  @if (session('show_review_prompt') && auth()->id() !== $request->User_ID)
    @php
      $reviewRequestId = session('review_request_id');
      $reviewAssignmentId = session('review_assignment_id');
      $hasReviewed = \App\Models\Review::where('Request_ID', $reviewRequestId)->where('Reviewer_ID', auth()->id())->exists();
    @endphp
    @if ($reviewAssignmentId && ! $hasReviewed)
    <div id="review-prompt-modal-overlay" class="review-prompt-modal-overlay" onclick="if(event.target===this)closeReviewPromptModal()">
      <div class="review-prompt-modal">
        <div class="review-prompt-modal-header">
          <h3>Leave a Review</h3>
          <button type="button" class="review-prompt-modal-close" onclick="closeReviewPromptModal()">&times;</button>
        </div>
        <div class="review-prompt-modal-body">
          <p>The request has been <strong>{{ $request->Status === 'Completed' ? 'completed' : 'failed' }}</strong>. Would you like to leave a review for the requester now?</p>
        </div>
        <div class="review-prompt-modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" onclick="closeReviewPromptModal()">Later</button>
          <form method="POST" action="{{ route('assignments.review', $reviewAssignmentId) }}" style="display:inline;">
            @csrf
            <input type="hidden" name="rating" id="review-rating-value" value="5">
            <div id="review-rating-stars" class="star-rating">
              <button type="button" class="star active" onclick="setReviewRating(1)" title="1 Star">★</button>
              <button type="button" class="star" onclick="setReviewRating(2)" title="2 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(3)" title="3 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(4)" title="4 Stars">★</button>
              <button type="button" class="star" onclick="setReviewRating(5)" title="5 Stars">★</button>
            </div>
            <div style="font-size:0.75rem; color:var(--muted); margin-top:4px;">Rate from 1 to 5 stars</div>
            <textarea name="comment" required placeholder="Write a review..." style="padding:6px;border:1px solid #ddd;border-radius:6px;font-size:0.9rem;margin-top:6px;width:100%;"></textarea>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-top:8px;">Review Now</button>
          </form>
        </div>
      </div>
    </div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('review-prompt-modal-overlay').classList.add('active');
      });

      function setReviewRating(value) {
        document.getElementById('review-rating-value').value = value;
        document.querySelectorAll('#review-rating-stars .star').forEach((star, idx) => {
          star.classList.toggle('active', idx < value);
        });
      }
    </script>
    @endif
  @endif

  <div style="margin-top:20px; padding:16px; background:#FFF5F5; border-radius:8px; border:1px solid #FECACA;">
    @php
      $failedAssignment = $request->assignments->firstWhere('Status', 'Failed');
      $provider = $failedAssignment->user ?? null;
      $hasReviewed = $provider ? \App\Models\Review::where('Request_ID', $request->Request_ID)->where('Reviewer_ID', auth()->id())->exists() : false;
    @endphp
    @if ($provider && ! $hasReviewed)
      <h3 style="margin:0 0 12px; font-size:1.1rem; color:var(--primary);">Feedback for {{ $provider->Full_Name }}</h3>
      <p style="color:var(--muted); margin:8px 0 16px; font-size:0.95rem;">This request was marked as failed. You can report this user or leave a review.</p>
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button type="button" class="btn btn-danger btn-sm" onclick="document.getElementById('failed-report-form').style.display='block';">Report User</button>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('failed-review-modal-overlay').classList.add('active');">Leave Review</button>
      </div>

      <form id="failed-report-form" method="POST" action="{{ route('reports.store') }}" style="margin-top:16px; display:none;">
        @csrf
        <input type="hidden" name="reported_user_id" value="{{ $provider->User_ID }}">
        <input type="hidden" name="request_id" value="{{ $request->Request_ID }}">
        <div class="form-group">
          <label>Reason</label>
          <textarea name="reason" rows="4" required class="form-control" style="width:100%;padding:10px;border:2px solid var(--border);border-radius:8px;"></textarea>
        </div>
        <div class="form-group">
          <label>Proof (Optional)</label>
          <input type="text" name="proof" placeholder="Link or description of evidence" class="form-control" style="width:100%;padding:10px;border:2px solid var(--border);border-radius:8px;">
        </div>
        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
      </form>

      <div id="failed-review-modal-overlay" class="review-prompt-modal-overlay" onclick="if(event.target===this)closeFailedReviewModal()">
        <div class="review-prompt-modal">
          <div class="review-prompt-modal-header">
            <h3>Leave a Review for {{ $provider->Full_Name }}</h3>
            <button type="button" class="review-prompt-modal-close" onclick="closeFailedReviewModal()">&times;</button>
          </div>
          <form method="POST" action="{{ route('assignments.review', $failedAssignment->Assignment_ID) }}" style="display:flex; flex-direction:column; gap:8px; width:100%;">
            @csrf
            <div class="review-prompt-modal-body" style="padding:20px;">
              <div class="form-group">
                <label>Rating (1-5)</label>
                <input type="hidden" name="rating" id="failed-review-rating-value" value="5">
                <div id="failed-review-rating-stars" class="star-rating">
                  <button type="button" class="star active" onclick="setFailedReviewRating(1)" title="1 Star">★</button>
                  <button type="button" class="star" onclick="setFailedReviewRating(2)" title="2 Stars">★</button>
                  <button type="button" class="star" onclick="setFailedReviewRating(3)" title="3 Stars">★</button>
                  <button type="button" class="star" onclick="setFailedReviewRating(4)" title="4 Stars">★</button>
                  <button type="button" class="star" onclick="setFailedReviewRating(5)" title="5 Stars">★</button>
                </div>
              </div>
              <div class="form-group">
                <label>Comment</label>
                <textarea name="comment" rows="3" placeholder="Comment (optional)..." style="width:100%;padding:10px;border:2px solid var(--border);border-radius:8px;"></textarea>
              </div>
            </div>
            <div class="review-prompt-modal-footer">
              <button type="button" class="btn btn-secondary btn-sm" onclick="closeFailedReviewModal()">Later</button>
              <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  </div>

  @if (auth()->id() !== $request->User_ID)
    <div class="card" style="margin-top:24px; background:#F0FDF4; border:1px solid #BBF7D0;">
      <h3 style="margin-top:0; color:#065F46;">Actions</h3>
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <form method="post" action="{{ route('assignments.apply') }}" style="display:inline;">
          @csrf
          <input type="hidden" name="request_id" value="{{ $request->Request_ID }}">
          <button type="submit" class="btn btn-success btn-sm">Apply</button>
        </form>
        <a href="{{ route('messages.index', ['with' => $request->User_ID]) }}" class="btn btn-primary btn-sm">Message</a>
        <button class="btn btn-danger btn-sm" onclick="openRequestReportModal({{ $request->User_ID }}, {{ $request->Request_ID }})" title="Report This Request" style="padding:8px 10px; margin-left:auto;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
        </button>
      </div>
    </div>
  @endif

  @if (auth()->id() == $request->User_ID)
    @php
      $matchedProviders = $request->matches()
          ->with('user')
          ->orderByDesc('Match_Score')
          ->get();
    @endphp
    @if ($matchedProviders->isNotEmpty() && $request->Status === 'Open' && $request->assignments->whereIn('Status', ['Pending', 'Accepted'])->isEmpty())
    <div class="card" style="margin-bottom:24px; background:#F0F9FF; border:1px solid #BAE6FD;">
      <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid #BAE6FD;">
        <h3 style="margin:0; color:#0369A1;">Matched Providers</h3>
        <span style="font-size:0.85rem; color:var(--muted);">{{ $matchedProviders->count() }} provider{{ $matchedProviders->count() > 1 ? 's' : '' }} matched</span>
      </div>
      <div class="cards-grid">
        @foreach ($matchedProviders as $match)
          @php
            $provider = $match->user;
            $scorePercent = round($match->Match_Score * 100);
            $providerPic = $provider->Profile_Picture ? asset('storage/'.$provider->Profile_Picture) : asset('images/default-avatar.svg');
            $providerSkills = $provider->skills;
          @endphp
          <div class="card" style="padding:16px; display:flex; gap:12px; align-items:flex-start;">
            <img src="{{ $providerPic }}" alt="{{ $provider->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
            <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
              <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; overflow:hidden;">
                <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $provider->Full_Name }}</div>
                <div style="display:flex; align-items:center; gap:8px; flex-shrink:0;">
                  <span class="badge badge-accepted" style="font-size:0.75rem;">{{ $scorePercent }}% Match</span>
                </div>
              </div>
              <div style="color:var(--muted); font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0;">{{ $provider->Email }}</div>
              <p style="color:var(--muted); font-size:0.85rem; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ \Illuminate\Support\Str::limit($provider->Bio ?? 'No bio available', 100) }}</p>
              <div style="display:flex; gap:4px; flex-wrap:wrap;">
                @foreach ($providerSkills->take(2) as $s)
                  <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
                @endforeach
                @if ($providerSkills->count() > 2)
                  <span class="badge badge-open" style="font-size:0.7rem; opacity:0.7;">+{{ $providerSkills->count() - 2 }} more</span>
                @endif
              </div>
              <div style="margin:8px 0; font-size:0.8rem; color:var(--muted); display:flex; gap:12px; flex-wrap:wrap;">
                <span>Rating: {{ number_format((float)($provider->Avg_Rating ?? 0), 2) }}/5</span>
                <span>Completed: {{ $provider->Total_Completed ?? 0 }}</span>
                @if ($provider->Is_Verified)
                  <span class="badge badge-accepted" style="font-size:0.7rem;">Verified</span>
                @endif
              </div>
              <form method="POST" action="{{ route('requests.direct-assign', $request->Request_ID) }}" style="display:inline;" onsubmit="return confirm('Send request to this provider? They will need to accept or reject.');">
                @csrf
                <input type="hidden" name="provider_id" value="{{ $provider->User_ID }}">
                <button type="submit" class="btn btn-primary btn-sm">Request</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    </div>
    @elseif ($matchedProviders->isEmpty() && $request->Status === 'Open')
    <div class="card" style="margin-bottom:24px; background:#FFF5F5; border:1px solid #FECACA;">
      <div style="display:flex; align-items:flex-start; gap:12px;">
        <div style="font-size:2rem;">🔍</div>
        <div style="flex:1;">
          <strong>No Matched Providers Found</strong>
          <p style="color:var(--muted); margin:6px 0 0; font-size:0.95rem;">No providers matched your request criteria. Other users can still apply normally using the Apply button below.</p>
        </div>
      </div>
    </div>
    @endif
  @endif

  @if (auth()->id() == $request->User_ID)
    <div class="page-header" style="margin-top:32px;">
      <h3 style="margin:0;">Applicants</h3>
    </div>
    @if ($applicants->isEmpty())
      <div class="empty-state">
        <div style="font-size:2rem; margin-bottom:12px;">👥</div>
        <h4 style="margin:0 0 6px; color:var(--text);">No applicants yet</h4>
        <p style="margin:0; color:var(--muted); font-size:0.9rem;">No one has applied to this request yet.</p>
      </div>
    @else
      <div class="table-wrap">
        <table>
          <thead><tr><th></th><th>Name</th><th>Email</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            @foreach ($applicants as $a)
              @php
                $appPic = $a->user->Profile_Picture ? asset('storage/'.$a->user->Profile_Picture) : asset('images/default-avatar.svg');
              @endphp
              <tr>
                <td style="width:44px;"><img src="{{ $appPic }}" alt="{{ $a->user->Full_Name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" onerror="this.src='{{ asset('images/default-avatar.svg') }}'"></td>
                <td>{{ $a->user->Full_Name ?? 'N/A' }}</td>
                <td>{{ $a->user->Email ?? 'N/A' }}</td>
                <td>
                  {{ number_format((float)($a->user->Avg_Rating ?? 0), 2) }} / 5
                  <span style="color:var(--muted);font-size:0.82rem;">({{ $a->user->Total_Completed ?? 0 }} completed)</span>
                  @if ($a->user->isNew)
                    <br><span class="badge badge-new" style="font-size:0.75rem;">New Member</span>
                  @endif
                </td>
                <td><span class="badge badge-{{ strtolower($a->Status) }}">{{ $a->Status }}</span></td>
                <td style="display:flex;gap:6px;">
                  @if ($a->Status === 'Pending')
                    <form method="post" action="{{ route('assignments.accept', $a->Assignment_ID) }}" style="display:inline;">
                      @csrf
                      <button type="submit" class="btn btn-success btn-sm">Accept</button>
                    </form>
                    <form method="post" action="{{ route('assignments.reject', $a->Assignment_ID) }}" style="display:inline;" onsubmit="return confirm('Reject this applicant?');">
                      @csrf
                      <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                    </form>
                  @else
                    <span style="color:var(--muted);">Accepted</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  @endif

  <div style="margin-top:24px;">
    <a href="{{ route('requests.index') }}" class="btn btn-secondary">Back to Requests</a>
  </div>
</div>

<div id="request-report-modal-overlay" class="report-modal-overlay" onclick="if(event.target===this)closeRequestReportModal()">
  <div class="report-modal">
    <div class="report-modal-header">
      <h3>Report This Request</h3>
      <button type="button" class="report-modal-close" onclick="closeRequestReportModal()">&times;</button>
    </div>
    <form id="request-report-modal-form" method="POST" action="{{ route('reports.store') }}">
      @csrf
      <div class="report-modal-body">
        <input type="hidden" name="reported_user_id" id="request-report-modal-user-id" value="">
        <input type="hidden" name="request_id" id="request-report-modal-request-id" value="">
        <div class="form-group">
          <label>Reason</label>
          <textarea name="reason" rows="4" required class="form-control" style="width:100%;padding:10px;border:2px solid var(--border);border-radius:8px;"></textarea>
        </div>
        <div class="form-group">
          <label>Proof (Optional)</label>
          <input type="text" name="proof" placeholder="Link or description of evidence" class="form-control" style="width:100%;padding:10px;border:2px solid var(--border);border-radius:8px;">
        </div>
      </div>
      <div class="report-modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeRequestReportModal()">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeReviewPromptModal() {
  const overlay = document.getElementById('review-prompt-modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
  }
}

function closeFailedReviewModal() {
  const overlay = document.getElementById('failed-review-modal-overlay');
  if (overlay) {
    overlay.classList.remove('active');
  }
}

function openRequestReportModal(userId, requestId) {
  document.getElementById('request-report-modal-user-id').value = userId;
  document.getElementById('request-report-modal-request-id').value = requestId;
  document.getElementById('request-report-modal-overlay').classList.add('active');
}
function closeRequestReportModal() {
  document.getElementById('request-report-modal-overlay').classList.remove('active');
}

function setFailedReviewRating(value) {
  document.getElementById('failed-review-rating-value').value = value;
  document.querySelectorAll('#failed-review-rating-stars .star').forEach((star, idx) => {
    star.classList.toggle('active', idx < value);
  });
}
</script>
@endsection
