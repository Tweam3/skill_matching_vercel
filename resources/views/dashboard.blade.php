@extends('layouts.app')

@section('content')
  <div class="container">
    <p style="color:var(--muted);margin-bottom:24px; font-size:1.25rem;">Welcome back, <strong>{{ auth()->user()->Full_Name }}</strong>.</p>

    <div class="page-header" style="margin-bottom:28px;">
      <h2 style="margin:0;">Student Dashboard</h2>
      <a href="{{ route('requests.index') }}" class="btn btn-primary btn-sm">+ New Request</a>
    </div>

    <div class="page-header" style="margin-bottom:28px;">
      <h3 style="margin:0;">Quick Actions</h3>
    </div>
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px; margin-bottom:24px;">
      <a href="{{ route('requests.index') }}" class="btn btn-primary btn-block">+ New Request</a>
      <a href="{{ route('skills.index') }}" class="btn btn-success btn-block">+ Add Skill</a>
      <a href="{{ route('matches.index') }}" class="btn btn-secondary btn-block">Browse Matches</a>
      <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-block">Messages</a>
      <a href="{{ route('reviews.index') }}" class="btn btn-secondary btn-block">Reviews</a>
      <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary btn-block">My Profile</a>
    </div>

    <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-value">{{ $openRequests }}</div>
      <div class="stat-label">Open Requests</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $pendingApplications }}</div>
      <div class="stat-label">Pending Applications</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $matchesCount }}</div>
      <div class="stat-label">Matches</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ number_format((float)$user->Avg_Rating, 2) }}</div>
      <div class="stat-label">My Rating</div>
    </div>
    <div class="stat-card">
      <div class="stat-value">{{ $unreadCount }}</div>
      <div class="stat-label">Unread Notifications</div>
    </div>
  </div>

  <div class="page-header" style="margin-top:32px;">
    <h3 style="margin:0;">Recent Requests</h3>
  </div>
  @if ($recent->isEmpty())
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">📝</div>
      <h3 style="margin-bottom:8px;">No requests yet</h3>
      <p>Create your first skill request to get matched with peers.</p>
      <a href="{{ route('requests.index') }}" class="btn btn-primary">Create Request</a>
    </div>
  @else
    <div class="cards-grid">
      @foreach ($recent as $r)
        @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
        <div class="card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div style="flex:1;">
              <div class="card-title">{{ $r->Title }}</div>
              <div class="card-subtitle">{{ $allSkills->pluck('Skill_Title')->join(', ') }}</div>
              <div style="margin-top:8px;"><span class="badge badge-{{ strtolower($r->Status) }}">{{ $r->Status }}</span></div>
            </div>
            <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-success btn-sm">View</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
