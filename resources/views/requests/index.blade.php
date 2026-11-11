@extends('layouts.app')

@section('content')
<div class="container">
  <div class="page-header">
    <h2 style="margin:0;">Skill Requests</h2>
    <button class="btn btn-primary" onclick="toggleForm()">+ New Request</button>
  </div>

  <div id="form-card" class="card" style="display:none;margin-bottom:24px; border-top:3px solid var(--primary);">
    <form method="POST" action="{{ route('requests.store') }}" id="create-request-form">
      @csrf
      <div class="form-group">
        <label>Skills</label>
        <div class="skill-picker">
          <input type="text" class="skill-search" placeholder="Search skills…" autocomplete="off">
          <div class="skill-groups">
            @php
              $allSkills = \App\Models\Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();
              $groupedSkills = $allSkills->groupBy(function ($s) {
                  return $s->Subcategory
                    ? ($s->Category . ' / ' . $s->Subcategory)
                    : ($s->Category ?: 'General');
              });
            @endphp
            @foreach ($groupedSkills as $groupLabel => $groupSkills)
              <div class="skill-group" data-group="{{ strtolower($groupLabel) }}">
                <div class="skill-group-title">{{ $groupLabel }}</div>
                @foreach ($groupSkills as $sk)
                  <label class="skill-option">
                    <input type="checkbox" name="skill_ids[]" value="{{ $sk->Skill_ID }}">
                    <span>{{ $sk->Skill_Title }}</span>
                  </label>
                @endforeach
              </div>
            @endforeach
            <div class="skill-empty" style="display:none;">No skills match your search.</div>
          </div>
          <div class="selected-chips" id="selected-chips"></div>
        </div>
        <p class="skill-picker-hint">Tap skills to select multiple. Use the search bar to filter.</p>
      </div>
      <div class="form-group">
        <label>Title</label>
        <input type="text" name="title" required placeholder="e.g. Help with Calculus homework">
      </div>
      <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="4" required placeholder="Describe what you need help with…"></textarea>
      </div>
      <div class="form-group">
        <label>Service Mode</label>
        <div style="display:flex; gap:12px;">
          <label style="cursor:pointer;"><input type="radio" name="service_mode" value="Remote" checked> Remote</label>
          <label style="cursor:pointer;"><input type="radio" name="service_mode" value="Face-to-Face"> Face-to-Face</label>
          <label style="cursor:pointer;"><input type="radio" name="service_mode" value="Hybrid"> Hybrid</label>
        </div>
      </div>
      <div style="display:flex; gap:10px;">
        <button type="submit" id="create-request-btn" class="btn btn-primary btn-block" style="flex:1;">Create Request</button>
        <button type="button" class="btn btn-secondary btn-block" style="flex:1;" onclick="toggleForm()">Cancel</button>
      </div>
    </form>
  </div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('create-request-form');
    var btn = document.getElementById('create-request-btn');
    if (!form || !btn) return;

    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.innerHTML = 'Creating…';
    });

    window.addEventListener('pageshow', function () {
      btn.disabled = false;
      btn.innerHTML = 'Create Request';
    });
  });
</script>
@endpush

  @if ($requests->isEmpty())
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">📭</div>
      <h3 style="margin:0 0 8px;">No requests yet</h3>
      <p style="margin:0;">You haven't posted any skill requests. Create your first one to get started.</p>
      <button class="btn btn-primary" onclick="toggleForm()">+ New Request</button>
    </div>
  @else
    <div class="cards-grid">
      @foreach ($requests as $r)
        @php
          $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values();
        @endphp
        <div class="card" style="padding:16px;">
          <div style="display:flex; gap:12px; align-items:flex-start;">
            @php
              $ownerPic = $r->user->Profile_Picture ? asset('storage/'.$r->user->Profile_Picture) : asset('images/default-avatar.svg');
            @endphp
            <img src="{{ $ownerPic }}" alt="{{ $r->user->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
            <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
              <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; overflow:hidden;">
                <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $r->user->Full_Name }}</div>
                <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                  <span class="badge badge-{{ strtolower($r->Status) }}" style="font-size:0.7rem; white-space:nowrap;">{{ $r->Status }}</span>
                  <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-success btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">View</a>
                  <a href="{{ route('requests.edit', $r->Request_ID) }}" class="btn btn-primary btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">Edit</a>
                  @if ($r->assignments->isEmpty() || $r->assignments->whereIn('Status', ['Pending', 'Accepted'])->isEmpty())
                  <form method="POST" action="{{ route('requests.destroy', $r->Request_ID) }}" style="display:inline;" onsubmit="return confirm('Delete this request?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">Del</button>
                  </form>
                  @endif
                </div>
              </div>
              <div style="color:var(--muted); font-size:0.85rem; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0;">{{ $r->Title }}</div>
<p style="color:var(--muted); font-size:0.85rem; line-height:1.5; margin:0; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ \Illuminate\Support\Str::limit($r->Description, 120) }}</p>
               <div style="display:flex; gap:4px; flex-wrap:wrap;">
                 @foreach ($allSkills->take(2) as $s)
                   <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
                 @endforeach
                 @if ($allSkills->count() > 2)
                   <span class="badge badge-open" style="font-size:0.7rem; opacity:0.7;">+{{ $allSkills->count() - 2 }} more</span>
                 @endif
               </div>
               <div style="font-size:0.75rem; color:var(--muted); margin-top:8px;">
                 Created: {{ $r->Created_At->format('M d, Y H:i') }}
               </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
<script>
function toggleForm() {
  const c = document.getElementById('form-card');
  c.style.display = c.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function () {
  const picker = document.querySelector('.skill-picker');
  if (!picker) return;

  const searchInput = picker.querySelector('.skill-search');
  const skillOptions = picker.querySelectorAll('.skill-option');
  const skillGroups = picker.querySelectorAll('.skill-group');
  const emptyState = picker.querySelector('.skill-empty');
  const chipsContainer = picker.querySelector('.selected-chips');

  function updateChips() {
    const checked = picker.querySelectorAll('input[name="skill_ids[]"]:checked');
    chipsContainer.innerHTML = '';
    checked.forEach(function (cb) {
      const label = cb.closest('.skill-option');
      const name = label ? label.querySelector('span').textContent : cb.value;
      const chip = document.createElement('span');
      chip.className = 'skill-chip';
      chip.innerHTML = name + ' <button type="button" data-id="' + cb.value + '">×</button>';
      chipsContainer.appendChild(chip);
    });
  }

  function filterSkills() {
    const term = searchInput.value.trim().toLowerCase();
    let anyVisible = false;

    skillGroups.forEach(function (group) {
      let groupHasVisible = false;
      const options = group.querySelectorAll('.skill-option');
      options.forEach(function (opt) {
        const text = opt.textContent.toLowerCase();
        const match = text.indexOf(term) !== -1;
        opt.style.display = match ? 'flex' : 'none';
        if (match) groupHasVisible = true;
      });
      group.style.display = groupHasVisible ? 'block' : 'none';
      if (groupHasVisible) anyVisible = true;
    });

    emptyState.style.display = anyVisible ? 'none' : 'block';
  }

  picker.addEventListener('change', function (e) {
    if (e.target.name === 'skill_ids[]') {
      updateChips();
    }
  });

  chipsContainer.addEventListener('click', function (e) {
    if (e.target.tagName === 'BUTTON') {
      const id = e.target.getAttribute('data-id');
      const cb = picker.querySelector('input[name="skill_ids[]"][value="' + id + '"]');
      if (cb) {
        cb.checked = false;
        updateChips();
      }
    }
  });

  searchInput.addEventListener('input', filterSkills);

  updateChips();
});
</script>
@endsection
