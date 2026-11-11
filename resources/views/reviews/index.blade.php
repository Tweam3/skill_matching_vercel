@extends('layouts.app')

@section('content')
<div class="container">
  <h2 style="margin:0;">Reviews Received</h2>
  <span style="color:var(--muted); font-size:0.85rem;">Reviews left by others about you</span>

  @if ($reviews->isEmpty())
    <div class="alert alert-info">No reviews received yet.</div>
  @else
    <div class="cards-grid">
      @foreach ($reviews as $r)
        <div class="card" style="margin-bottom:16px;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div style="flex:1;">
              <div class="card-title">{{ $r->reviewer->Full_Name ?? 'Unknown User' }}</div>
              <div class="card-subtitle">
                {{ str_repeat('★', (int) $r->Rating) . str_repeat('☆', 5 - (int) $r->Rating) }}
                <span style="color:var(--muted); font-size:0.85rem; margin-left:6px;">{{ $r->Rating }}/5</span>
              </div>
              @if ($r->request)
                <div style="margin-top:6px; color:var(--muted); font-size:0.85rem;">Request: {{ $r->request->Title }}</div>
              @endif
              <p style="margin-top:8px; color:var(--text); line-height:1.6;">{{ $r->Comment }}</p>
              <div style="margin-top:8px; color:var(--muted); font-size:0.8rem;">
                {{ $r->Created_At ? $r->Created_At->format('M d, Y h:i A') : 'N/A' }}
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
