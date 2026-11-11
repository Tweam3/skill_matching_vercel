@extends('layouts.app')

@php $hideFooter = true; @endphp

@section('content')
<div class="container">
  @php
    $isOwner = auth()->check() && auth()->id() === $user->User_ID;
    $isAdmin = auth()->check() && auth()->user()->Role === 'Admin';
    $canEdit = $isOwner || $isAdmin;

    $profileVisibility = $user->settings['profile_visibility'] ?? 'public';
    $canView = $profileVisibility === 'public' || $canEdit;
  @endphp

  <div class="page-header">
    <h2 style="margin:0;">{{ $isOwner ? 'My Profile' : 'Profile' }}</h2>
  </div>

  @if (!$canView)
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">&#128274;</div>
      <h3 style="margin:0 0 8px;">Private Profile</h3>
      <p style="margin:0;">This user has set their profile to private.</p>
    </div>
  @else

  <div class="card" style="margin-bottom:24px;">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:24px; flex-wrap:wrap;">
      <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap; flex:1; min-width:280px;">
        <div style="flex-shrink:0;">
          @php
            $pic = $user->Profile_Picture ? asset('storage/'.$user->Profile_Picture) : asset('images/default-avatar.svg');
          @endphp
          <img src="{{ $pic }}" alt="Profile picture" onerror="this.src='{{ asset('images/default-avatar.svg') }}'" style="width:112px; height:112px; border-radius:50%; object-fit:cover; border:3px solid var(--primary); background:var(--bg); box-shadow:0 4px 12px rgba(0,0,0,0.1);">
        </div>
        <div style="flex:1; min-width:240px;">
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
            <h3 style="margin:0; font-size:1.5rem; color:var(--primary);">{{ $user->Full_Name }}</h3>
            @if ($user->isNew)
              <span class="badge badge-new">New Member</span>
            @endif
          </div>
          <p style="margin:0; color:var(--muted); font-size:1rem;">{{ $user->Email }}</p>
          <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
            <span class="badge badge-{{ strtolower(str_replace(' ', '_', $user->Role)) }}">{{ $user->Role }}</span>
            <span class="badge badge-pending">{{ $user->Council ?? 'Unaffiliated' }}</span>
            <span class="badge badge-open">Rating {{ number_format((float)$user->Avg_Rating, 2) }}</span>
            <span class="badge badge-assigned">{{ $user->Total_Completed }} completed</span>
          </div>
        </div>
      </div>
      <div style="flex-shrink:0; display:flex; align-items:center; gap:8px; margin-top:16px;">
        @if ($canEdit)
          <a href="{{ route('profile.edit', $user->User_ID) }}" class="btn btn-primary" style="padding:10px 20px; font-size:0.95rem; font-weight:600; white-space:nowrap;">Edit Profile</a>
        @elseif (auth()->check() && auth()->id() !== $user->User_ID)
          <a href="{{ route('messages.index', ['with' => $user->User_ID]) }}" class="btn btn-success" style="padding:10px 20px; font-size:0.95rem; font-weight:600; white-space:nowrap;">Message</a>
        @endif
      </div>
    </div>
  </div>

  @if ($user->Bio)
    <div class="card" style="margin-bottom:24px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:var(--radius);">
      <h3 style="margin:0 0 12px; padding:0 4px; font-size:1.05rem; color:var(--primary); display:flex; align-items:center; justify-content:space-between; gap:8px;">
        📝 About
        @if ($canEdit)
          <a href="{{ route('profile.edit', $user->User_ID) }}#about" class="btn btn-secondary btn-sm">Edit</a>
        @endif
      </h3>
      <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:10px; padding:16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <p style="color:var(--text); font-weight:500; margin:0; white-space:pre-line; line-height:1.7;">
          {{ $user->Bio }}
        </p>
      </div>
    </div>
  @endif

  @if (!$isOwner)
    <!-- Reviews Received - only for viewers -->
    <div class="card" style="margin-bottom:24px;">
      <h3 style="margin-top:0; font-size:1.1rem; color:var(--primary); display:flex; align-items:center; justify-content:space-between; gap:8px;">
        Reviews Received
        <span style="font-size:0.85rem; color:var(--muted); font-weight:400;">
          {{ $reviewsReceived->count() }} review{{ $reviewsReceived->count() > 1 ? 's' : '' }}
          @if ($reviewsReceived->count() > 0)
            &mdash; Avg: {{ number_format($reviewsReceived->avg('Rating'), 2) }}/5
          @endif
        </span>
      </h3>
      @if ($reviewsReceived->isEmpty())
        <div class="empty-state" style="padding:24px 20px; text-align:center;">
          <div style="font-size:2rem; margin-bottom:12px;">⭐</div>
          <h4 style="margin:0 0 6px; color:var(--text);">No reviews yet</h4>
          <p style="margin:0; color:var(--muted); font-size:0.9rem;">This user hasn't received any reviews yet.</p>
        </div>
      @else
        <div class="cards-grid">
          @foreach ($reviewsReceived as $r)
            <div class="card" style="padding:16px; display:flex; flex-direction:column; gap:8px;">
              <div style="display:flex; align-items:flex-start; gap:12px;">
                @php
                  $reviewerPic = $r->reviewer->Profile_Picture ? asset('storage/'.$r->reviewer->Profile_Picture) : asset('images/default-avatar.svg');
                @endphp
                <img src="{{ $reviewerPic }}" alt="{{ $r->reviewer->Full_Name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                <div style="flex:1; min-width:0;">
                  <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:4px;">
                    <div class="card-title" style="font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $r->reviewer->Full_Name }}</div>
                    <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                      <span style="font-size:1rem; letter-spacing:2px;">{{ str_repeat('★', (int)$r->Rating) . str_repeat('☆', 5 - (int)$r->Rating) }}</span>
                      <span style="font-size:0.8rem; color:var(--muted);">{{ $r->Rating }}/5</span>
                    </div>
                  </div>
                  @if ($r->request)
                    <div style="font-size:0.75rem; color:var(--muted); margin-bottom:4px;">
                      For: {{ $r->request->Title }}
                    </div>
                  @endif
                  <p style="margin:4px 0 0; color:var(--text); line-height:1.5; font-size:0.9rem;">{{ $r->Comment }}</p>
                  <div style="font-size:0.7rem; color:var(--muted); margin-top:8px;">
                    {{ $r->Created_At ? $r->Created_At->format('M d, Y H:i') : 'N/A' }}
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  @endif

  <div class="card" style="margin-bottom:24px;">
    <h3 style="margin-top:0; font-size:1.1rem; color:var(--primary); display:flex; align-items:center; justify-content:space-between; gap:8px;">
      {{ $isOwner ? 'My' : 'Their' }} Skills
      @if ($canEdit)
        <a href="{{ route('skills.index') }}" class="btn btn-primary btn-sm">Manage Skills</a>
      @endif
    </h3>
@if ($userSkills->isEmpty())
      <div class="empty-state" style="padding:32px 20px;">
        <div style="font-size:2rem; margin-bottom:12px;">🛠️</div>
        <h4 style="margin:0 0 6px; color:var(--text);">No skills listed yet</h4>
        @if ($isOwner)
          <p style="margin:0; color:var(--muted); font-size:0.9rem;">Add skills to your profile so others can find you.</p>
          <a href="{{ route('skills.index') }}" class="btn btn-primary btn-sm" style="margin-top:12px;">Add Skills</a>
        @else
          <p style="margin:0; color:var(--muted); font-size:0.9rem;">This user hasn&rsquo;t added any skills to their profile.</p>
        @endif
      </div>
    @else
      <div class="profile-skills-container">
        @php
          $grouped = $userSkills->groupBy(function ($s) {
            return $s->pivot->Proficiency ?? 0;
          });
          $labels = [1 => 'Beginner', 2 => 'Advanced Beginner', 3 => 'Competent', 4 => 'Proficient', 5 => 'Expert'];
          $orderedIds = [5, 4, 3, 2, 1];
        @endphp
        @foreach ($orderedIds as $level)
          @if ($grouped->has($level))
            <div style="margin-bottom:16px;">
              <h4 style="margin:0 0 10px; font-size:0.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">{{ $labels[$level] }}</h4>
              <div class="skills-chips">
                @foreach ($grouped[$level] as $s)
                  <span>
                    {{ $s->Skill_Title }}
                    @if ($canEdit)
                      <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                        <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                        <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700; font-size:1rem; margin-left:4px;">×</button>
                      </form>
                    @endif
                  </span>
                @endforeach
              </div>
            </div>
          @endif
        @endforeach
        @if ($grouped->has(0))
          <div style="margin-bottom:16px;">
            <h4 style="margin:0 0 10px; font-size:0.85rem; color:var(--muted); text-transform:uppercase; letter-spacing:0.05em;">Not rated</h4>
            <div class="skills-chips">
              @foreach ($grouped[0] as $s)
                <span>
                  {{ $s->Skill_Title }}
                  @if ($canEdit)
                    <form method="post" action="{{ route('profile.skill.remove') }}" style="display:inline;" onsubmit="return confirm('Remove this skill?');">
                      @csrf
                      <input type="hidden" name="user_id" value="{{ $user->User_ID }}">
                      <input type="hidden" name="skill_id" value="{{ $s->Skill_ID }}">
                      <button type="submit" style="border:none;background:none;color:var(--danger);cursor:pointer;font-weight:700; font-size:1rem; margin-left:4px;">×</button>
                    </form>
                  @endif
                </span>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    @endif
  </div>

  @if ($reviewsGiven->isNotEmpty())
    <div class="card" style="margin-top:24px;">
      <h3 style="margin-top:0; font-size:1.1rem; color:var(--primary); display:flex; align-items:center; justify-content:space-between; gap:8px;">
        Reviews Given
        <span style="font-size:0.85rem; color:var(--muted); font-weight:400;">
          {{ $reviewsGiven->count() }} review{{ $reviewsGiven->count() > 1 ? 's' : '' }}
        </span>
      </h3>
      <div class="cards-grid">
        @foreach ($reviewsGiven as $r)
          <div class="card" style="padding:16px; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
              @php
                $reviewedPic = $r->reviewedUser->Profile_Picture ? asset('storage/'.$r->reviewedUser->Profile_Picture) : asset('images/default-avatar.svg');
              @endphp
              <img src="{{ $reviewedPic }}" alt="{{ $r->reviewedUser->Full_Name }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
              <div style="flex:1; min-width:0;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:4px;">
                  <div class="card-title" style="font-size:0.95rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $r->reviewedUser->Full_Name }}</div>
                  <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                    <span style="font-size:1rem; letter-spacing:2px;">{{ str_repeat('★', (int)$r->Rating) . str_repeat('☆', 5 - (int)$r->Rating) }}</span>
                    <span style="font-size:0.8rem; color:var(--muted);">{{ $r->Rating }}/5</span>
                  </div>
                </div>
                @if ($r->request)
                  <div style="font-size:0.75rem; color:var(--muted); margin-bottom:4px;">
                    For: {{ $r->request->Title }}
                  </div>
                @endif
                <p style="margin:4px 0 0; color:var(--text); line-height:1.5; font-size:0.9rem;">{{ $r->Comment }}</p>
                <div style="font-size:0.7rem; color:var(--muted); margin-top:8px;">
                  {{ $r->Created_At ? $r->Created_At->format('M d, Y H:i') : 'N/A' }}
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  @endif

<div id="report-modal-overlay" class="report-modal-overlay" onclick="if(event.target===this)closeReportModal()">
  <div class="report-modal">
    <div class="report-modal-header">
      <h3>Report User</h3>
      <button type="button" class="report-modal-close" onclick="closeReportModal()">&times;</button>
    </div>
    <form id="report-modal-form" method="POST" action="{{ route('reports.store') }}">
      @csrf
      <div class="report-modal-body">
        <input type="hidden" name="reported_user_id" id="report-modal-user-id" value="">
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
        <button type="button" class="btn btn-secondary btn-sm" onclick="closeReportModal()">Cancel</button>
        <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
      </div>
    </form>
  </div>
</div>

<script>
function openReportModal(userId) {
  document.getElementById('report-modal-user-id').value = userId;
  document.getElementById('report-modal-overlay').classList.add('active');
}
function closeReportModal() {
  document.getElementById('report-modal-overlay').classList.remove('active');
}
</script>
@endsection