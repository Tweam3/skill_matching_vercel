@extends('layouts.app')

@section('content')
@guest
  <div class="hero-full-width" style="background: linear-gradient(135deg, #002147 0%, #0a2d6e 50%, #1e3a8a 100%); color:#fff; padding:80px 20px; text-align:center; position:relative; overflow:hidden;">
    <div style="position:absolute; top:0; left:0; right:0; bottom:0; background-image: url('{{ asset('images/isat-u-building.jfif') }}'); background-size: cover; background-position: center; opacity:0.15; pointer-events:none;"></div>
    <div style="position:relative; z-index:1; max-width:800px; margin:0 auto;">
      <div style="display:inline-flex; align-items:center; gap:8px; background:#FFD700; padding:8px 16px; border-radius:999px; margin-bottom:24px;">
        <span class="university-badge" style="background:#FFD700; color:#002147; font-weight:700; letter-spacing:0.04em;">ISAT-U</span>
      </div>
      <h1 style="font-size:3rem; margin:0 0 20px; line-height:1.2; font-weight:800; color:#FFD700;">Skill Matching System</h1>
      <p style="font-size:1.25rem; color:#cbd5e1; max-width:600px; margin:0 auto 32px; line-height:1.7;">
        Connect with peers based on skills. Request help, offer mentorship, and build together—on campus and beyond.
      </p>
      <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
        <a href="{{ route('register') }}" class="btn btn-gold btn-lg">Get Started</a>
        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">Login</a>
      </div>
    </div>
  </div>

  <div style="max-width:1100px; margin:0 auto; padding:70px 20px;">
    <div style="text-align:center; margin-bottom:48px;">
      <h2 style="font-size:1.75rem; margin-bottom:8px; color:var(--primary);">How it works</h2>
      <p style="color:var(--muted); margin:0;">Three simple steps to get help or share your skills.</p>
    </div>
    <div class="cards-grid how-it-works-grid">
      <div class="card" style="text-align:center; padding:32px 24px; border-top:3px solid var(--gold);">
        <div style="width:56px; height:56px; background:var(--primary-light); color:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.75rem; margin:0 auto 16px;">📝</div>
        <div class="card-title">Post a request</div>
        <div class="card-subtitle">Describe the skill you need and when you need it.</div>
      </div>
      <div class="card" style="text-align:center; padding:32px 24px; border-top:3px solid var(--success);">
        <div style="width:56px; height:56px; background:#F0FDF4; color:var(--success); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.75rem; margin:0 auto 16px;">🤝</div>
        <div class="card-title">Get matched</div>
        <div class="card-subtitle">We suggest verified peers with the right skills and strong ratings.</div>
      </div>
      <div class="card" style="text-align:center; padding:32px 24px; border-top:3px solid var(--warning);">
        <div style="width:56px; height:56px; background:#FEF3C7; color:var(--warning-text); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.75rem; margin:0 auto 16px;">⭐</div>
        <div class="card-title">Learn & review</div>
        <div class="card-subtitle">Complete the session, then leave a review to build trust.</div>
      </div>
    </div>
  </div>

  <div style="background:var(--surface); border-top:1px solid #e5e7eb; padding:60px 20px;">
    <div style="max-width:900px; margin:0 auto; text-align:center;">
      <h2 style="color:var(--primary); margin-bottom:10px; font-size:1.75rem;">Ready to start?</h2>
      <p style="color:var(--muted); margin-bottom:24px; font-size:1.1rem;">Join the ISAT-U skill-sharing community today.</p>
      <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Create an account</a>
    </div>
  </div>
@else
  <div style="background: linear-gradient(135deg, #002147 0%, #0a2d6e 50%, #1e3a8a 100%); color:#fff; padding:40px 20px; border-radius:0 0 24px 24px; margin-bottom:32px; position:relative; overflow:hidden;">
    <div style="position:absolute; top:0; left:0; right:0; bottom:0; background-image: url('{{ asset('images/isat-u-building.jfif') }}'); background-size: cover; background-position: center; opacity:0.15; pointer-events:none;"></div>
    <div style="position:relative; z-index:1; text-align:center;">
      <h1 style="margin:0 0 8px; font-size:2rem; color:#FFD700;">Welcome back, {{ auth()->user()->Full_Name }}</h1>
      <p style="color:#cbd5e1; margin:0; font-size:1.05rem;">Here's what's happening with your skill requests and matches.</p>
    </div>
  </div>
  </div>

  <div class="container" style="padding:32px 20px;">
    <div class="page-header" style="margin-bottom:28px;">
      <h3 style="margin:0;">Quick Actions</h3>
    </div>
    <div class="card" style="padding:24px; margin-bottom:24px;">
      <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:12px;">
        <a href="{{ route('requests.index') }}" class="btn btn-primary btn-block">+ New Request</a>
        <a href="{{ route('skills.index') }}" class="btn btn-success btn-block">+ Add Skill</a>
        <a href="{{ route('matches.index') }}" class="btn btn-secondary btn-block">Browse Matches</a>
        <a href="{{ route('messages.index') }}" class="btn btn-secondary btn-block">Messages</a>
        <a href="{{ route('reviews.index') }}" class="btn btn-secondary btn-block">Reviews</a>
        <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary btn-block">My Profile</a>
      </div>
    </div>

    <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-value">{{ $stats['requests'] }}</div>
          <div class="stat-label">My Requests</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ $stats['matches'] }}</div>
          <div class="stat-label">Active Matches</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ $stats['unread'] }}</div>
          <div class="stat-label">Unread Messages</div>
        </div>
        <div class="stat-card">
          <div class="stat-value">{{ number_format((float)($stats['rating'] ?? 0), 2) }}</div>
          <div class="stat-label">My Rating</div>
        </div>
      </div>

      <div class="page-header" style="margin-top:28px;">
        <h3 style="margin:0;">Recent Requests</h3>
        <a href="{{ route('requests.index') }}" class="btn btn-primary btn-sm">+ New Request</a>
      </div>
      @if ($stats['latest']->isEmpty())
        <div class="empty-state">
          <div style="font-size:3rem; margin-bottom:16px;">📭</div>
          <h3>No requests yet</h3>
          <p>Create your first skill request to get started.</p>
          <a href="{{ route('requests.index') }}" class="btn btn-primary">Create Request</a>
        </div>
      @else
        <div class="cards-grid">
          @foreach ($stats['latest'] as $r)
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
@endif
@endsection
