@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header" style="margin-bottom:24px;">
    <h2 style="margin:0;">Match Details</h2>
  </div>

  <div class="card">
    <div class="card-title">{{ $request->Title }}</div>
    <div class="card-subtitle">
      Requested by {{ $request->user->Full_Name }} ·
      @php $allSkills = $request->skills->merge([$request->skill])->unique('Skill_ID')->values(); @endphp
      {{ $allSkills->pluck('Skill_Title')->join(', ') }}
    </div>
    <p>{{ $request->Description }}</p>
    <p>Status: <span class="badge badge-{{ strtolower($request->Status) }}">{{ $request->Status }}</span></p>

    @if ($myScore)
      <div style="margin-top:16px; padding:16px; background:#F0FDF4; border-radius:8px; border:1px solid #BBF7D0;">
        <h4 style="margin:0 0 12px; font-size:1rem; color:#065F46;">Your Match Score</h4>
        <div style="display:flex; gap:16px; flex-wrap:wrap;">
          <div style="text-align:center; flex:1;">
            <div style="font-size:1.75rem; font-weight:700; color:var(--primary);">{{ $myScore['score'] }}%</div>
            <div style="font-size:0.85rem; color:var(--muted);">Overall</div>
          </div>
          <div style="text-align:center; flex:1;">
            <div style="font-size:1rem; font-weight:600; color:var(--text);">{{ round($myScore['breakdown']['skill_overlap'] * 100, 1) }}%</div>
            <div style="font-size:0.85rem; color:var(--muted);">Skill Overlap</div>
          </div>
          <div style="text-align:center; flex:1;">
            <div style="font-size:1rem; font-weight:600; color:var(--text);">{{ round($myScore['breakdown']['category_coverage'] * 100, 1) }}%</div>
            <div style="font-size:0.85rem; color:var(--muted);">Category Coverage</div>
          </div>
          <div style="text-align:center; flex:1;">
            <div style="font-size:1rem; font-weight:600; color:var(--text);">{{ round($myScore['breakdown']['rating'] * 100, 1) }}%</div>
            <div style="font-size:0.85rem; color:var(--muted);">Rating</div>
          </div>
          <div style="text-align:center; flex:1;">
            <div style="font-size:1rem; font-weight:600; color:var(--text);">{{ round($myScore['breakdown']['profile_quality'] * 100, 1) }}%</div>
            <div style="font-size:0.85rem; color:var(--muted);">Profile Quality</div>
          </div>
        </div>
      </div>
    @elseif ($isMatched)
      <div style="margin-top:16px; padding:12px; background:#F0FDF4; border-radius:8px; border:1px solid #BBF7D0;">
        <p style="margin:0; color:#065F46;">You are matched to this request. Open it to apply.</p>
      </div>
    @else
      <div style="margin-top:16px; padding:12px; background:#F3F4F6; border-radius:8px; border:1px solid #E5E7EB;">
        <p style="margin:0; color:var(--muted);">You are not matched to this request.</p>
      </div>
    @endif
  </div>

  @if ($ranking && $ranking->isNotEmpty())
    <div class="page-header" style="margin-top:32px;">
      <h3 style="margin:0;">Top Recommended Providers</h3>
    </div>
    <div class="cards-grid">
      @foreach ($ranking as $entry)
        <div class="card">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div style="flex:1;">
              <div class="card-title">{{ $entry['user']->Full_Name }}</div>
              <div class="card-subtitle">
                Rating: {{ $entry['user']->Avg_Rating }} ·
                Completed: {{ $entry['user']->Total_Completed }} ·
                @if ($entry['user']->isNew)
                  <span class="badge badge-new" style="font-size:0.7rem;">New Member</span>
                @elseif ($entry['user']->Is_Verified)
                  <span style="color:#28a745;">&#10003; Verified</span>
                @else
                  <span style="color:#dc3545;">&#10007; Unverified</span>
                @endif
              </div>
              <p style="color:var(--muted); margin:8px 0 0; font-size:0.9rem;">
                Match Score: <strong>{{ $entry['score'] }}%</strong>
              </p>
              <div style="margin-top:8px;">
                <small style="color:var(--muted);">
                  Skill: {{ round($entry['breakdown']['skill_overlap'] * 100, 1) }}% ·
                  Category: {{ round($entry['breakdown']['category_coverage'] * 100, 1) }}% ·
                  Rating: {{ round($entry['breakdown']['rating'] * 100, 1) }}% ·
                  Profile: {{ round($entry['breakdown']['profile_quality'] * 100, 1) }}%
                </small>
              </div>
            </div>
            <a href="{{ route('profile.show', $entry['user']->User_ID) }}" class="btn btn-primary btn-sm">View Profile</a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
