@extends('layouts.app')

@section('content')
<div class="container">
  <h2 style="margin-bottom:24px;">Matches</h2>

  <div class="page-header" style="margin-bottom:20px;">
    <h3 style="margin:0;">For You</h3>
  </div>
  @if ($matched->isEmpty())
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">🤝</div>
      <h3 style="margin:0 0 8px;">No matches yet</h3>
      <p style="margin:0;">Create a request with a skill to get matched with peers who can help.</p>
      <a href="{{ route('requests.index') }}" class="btn btn-primary">Create Request</a>
    </div>
  @endif
  <div class="cards-grid">
    @foreach ($matched as $m)
      @php $allSkills = $m->request->skills->merge([$m->request->skill])->unique('Skill_ID')->values(); @endphp
      @php
        $reqOwnerPic = $m->request->user->Profile_Picture ? asset('storage/'.$m->request->user->Profile_Picture) : asset('images/default-avatar.svg');
      @endphp
      <div class="card" style="padding:16px;">
        <div style="display:flex; gap:12px; align-items:flex-start;">
          <img src="{{ $reqOwnerPic }}" alt="{{ $m->request->user->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
          <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; overflow:hidden;">
              <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $m->request->user->Full_Name }}</div>
              <div style="display:flex; align-items:center; gap:6px; flex-shrink:0;">
                <span class="badge badge-open" style="font-size:0.7rem; white-space:nowrap;">{{ $m->Match_Score }}%</span>
                <a href="{{ route('requests.show', $m->request->Request_ID) }}" class="btn btn-success btn-sm" style="white-space:nowrap; padding:4px 8px; font-size:0.8rem;">View</a>
              </div>
            </div>
            <div style="color:var(--muted); font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0;">{{ $m->request->Title }}</div>
            <p style="color:var(--muted); font-size:0.85rem; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ \Illuminate\Support\Str::limit($m->request->Description, 120) }}</p>
            <div style="display:flex; gap:4px; flex-wrap:wrap;">
              @foreach ($allSkills->take(2) as $s)
                <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
              @endforeach
              @if ($allSkills->count() > 2)
                <span class="badge badge-open" style="font-size:0.7rem; opacity:0.7;">+{{ $allSkills->count() - 2 }} more</span>
              @endif
            </div>
          </div>
        </div>
        <div style="margin-top:14px; border-top:1px solid var(--border); padding-top:10px;">
          <a href="{{ route('messages.index', ['with' => $m->request->user->User_ID]) }}" class="btn btn-secondary btn-sm btn-block">Contact {{ $m->request->user->Full_Name }}</a>
        </div>
      </div>
    @endforeach
  </div>

  <div class="page-header" style="margin-top:32px;">
    <h3 style="margin:0;">Available Requests</h3>
  </div>
  @if ($available->isEmpty())
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">🔍</div>
      <h3 style="margin:0 0 8px;">No open requests</h3>
      <p style="margin:0;">There are no open requests available right now. Check back later!</p>
    </div>
  @endif
  <div class="cards-grid">
    @foreach ($available as $a)
      @php $allSkills = $a->skills->merge([$a->skill])->unique('Skill_ID')->values(); @endphp
      @php
        $availOwnerPic = $a->user->Profile_Picture ? asset('storage/'.$a->user->Profile_Picture) : asset('images/default-avatar.svg');
      @endphp
      <div class="card" style="padding:16px;">
        <div style="display:flex; gap:12px; align-items:flex-start;">
          <img src="{{ $availOwnerPic }}" alt="{{ $a->user->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
          <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0;">
              <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $a->user->Full_Name }}</div>
              <a href="{{ route('requests.show', $a->Request_ID) }}" class="btn btn-success btn-sm" style="white-space:nowrap; padding:5px 10px; font-size:0.85rem;">View Request</a>
            </div>
            <div style="color:var(--muted); font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0;">{{ $a->Title }}</div>
            <p style="color:var(--muted); font-size:0.85rem; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ \Illuminate\Support\Str::limit($a->Description, 120) }}</p>
            <div style="display:flex; gap:4px; flex-wrap:wrap;">
              @foreach ($allSkills->take(2) as $s)
                <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
              @endforeach
              @if ($allSkills->count() > 2)
                <span class="badge badge-open" style="font-size:0.7rem; opacity:0.7;">+{{ $allSkills->count() - 2 }} more</span>
              @endif
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
