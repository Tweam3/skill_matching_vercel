@extends('layouts.app')

@section('content')
<div class="container">
  <h2 style="margin-bottom:8px;">Search</h2>
  <p style="color:var(--muted);margin-bottom:24px;">Find service requests and skill providers by search or category.</p>

  <form method="GET" action="{{ route('search.index') }}" style="margin-bottom:24px;">
    <div class="card" style="margin-bottom:16px; border-top:3px solid var(--primary);">
      <div style="padding:20px;">

        <div style="display:flex; gap:24px; flex-wrap:wrap; align-items:center; margin-bottom:20px;">
          <div style="flex:1; min-width:200px;">
            <strong>Service Mode</strong>
            <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap;">
              <label style="cursor:pointer; font-size:0.9rem;">
                <input type="radio" name="service_mode" value="" {{ $serviceMode === '' ? 'checked' : '' }}> All
              </label>
              <label style="cursor:pointer; font-size:0.9rem;">
                <input type="radio" name="service_mode" value="Remote" {{ $serviceMode === 'Remote' ? 'checked' : '' }}> Remote
              </label>
              <label style="cursor:pointer; font-size:0.9rem;">
                <input type="radio" name="service_mode" value="Face-to-Face" {{ $serviceMode === 'Face-to-Face' ? 'checked' : '' }}> Face-to-Face
              </label>
              <label style="cursor:pointer; font-size:0.9rem;">
                <input type="radio" name="service_mode" value="Hybrid" {{ $serviceMode === 'Hybrid' ? 'checked' : '' }}> Hybrid
              </label>
            </div>
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <button type="button" id="toggle-categories" class="btn btn-secondary btn-sm">Show Categories</button>
        </div>

        <div id="categories-section" style="display:none; margin-bottom:20px; padding-top:16px; border-top:1px solid var(--border);">
          <strong>Category Tags</strong>
          <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
            <label style="cursor:pointer; font-size:0.9rem;">
              <input type="checkbox" id="select-all-categories"> Select All
            </label>
            <label style="cursor:pointer; font-size:0.9rem;">
              <input type="checkbox" id="clear-all-categories"> Clear All
            </label>
          </div>
          <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
            @foreach ($allCategories as $cat)
              @php $checked = in_array($cat, $categories) ? 'checked' : ''; @endphp
              <label style="cursor:pointer; font-size:0.9rem;">
                <input type="checkbox" name="categories[]" value="{{ $cat }}" {{ $checked }}
                  data-category="{{ $cat }}" class="category-checkbox">
                {{ $cat }}
                @if (isset($allSubcategories[$cat]) && ! empty($allSubcategories[$cat]))
                  <span class="toggle-subcats" data-category="{{ $cat }}" style="cursor:pointer; font-size:0.7rem; margin-left:4px; color:var(--muted);">[+]</span>
                  <span class="subcategory-list" data-category="{{ $cat }}" style="display:none; margin-left:16px; margin-top:4px;">
                    @foreach ($allSubcategories[$cat] as $sub)
                      @php $subChecked = in_array($sub, $subcategories) ? 'checked' : ''; @endphp
                      <div style="margin-top:4px;"><label style="cursor:pointer; font-size:0.85rem;"><input type="checkbox" name="subcategories[]" value="{{ $sub }}" {{ $subChecked }}>{{ $sub }}</label></div>
                    @endforeach
                  </span>
                @endif
              </label>
            @endforeach
          </div>
        </div>

        @if (Auth::check() && Auth::user()->Role === 'Admin')
          <div style="margin-top:20px; padding-top:16px; border-top:1px solid #e0e0e0;">
            <strong>Add Category</strong>
            <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap;">
              <input type="text" name="new_category" placeholder="New category" style="padding:8px;border-radius:6px;border:1px solid var(--border); flex:1; min-width:150px;">
              <input type="text" name="new_subcategory" placeholder="Subcategory (optional)" style="padding:8px;border-radius:6px;border:1px solid var(--border); flex:1; min-width:150px;">
              <button type="button" id="add-category-btn" class="btn btn-primary btn-sm">Add</button>
            </div>
          </div>
        @endif

        <div style="margin-top:20px;">
          <strong>Search</strong>
          <div style="display:flex; gap:10px; margin-top:8px;">
            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="Search by title, description, skill, or name…" style="padding:10px 12px;border-radius:6px;border:1px solid var(--border);width:100%;max-width:400px; font-size:0.95rem; flex:1;">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="{{ route('search.index') }}" class="btn btn-secondary">Reset</a>
          </div>
        </div>

        <div style="margin-top:16px;">
          <strong>Search in</strong>
          <div style="display:flex; gap:16px; margin-top:8px;">
            <label style="cursor:pointer; font-size:0.9rem;">
              <input type="radio" name="search_in" value="requests" {{ $searchIn === 'requests' ? 'checked' : '' }}> Requests
            </label>
            <label style="cursor:pointer; font-size:0.9rem;">
              <input type="radio" name="search_in" value="providers" {{ $searchIn === 'providers' ? 'checked' : '' }}> Providers
            </label>
            <label style="cursor:pointer; font-size:0.9rem;">
              <input type="radio" name="search_in" value="both" {{ $searchIn === 'both' ? 'checked' : '' }}> Both
            </label>
          </div>
        </div>

      </div>
    </div>
  </form>

  @if ($results->isEmpty() && $providers->isEmpty())
    <div class="empty-state">
      <div style="font-size:3rem; margin-bottom:16px;">🔍</div>
      <h3 style="margin:0 0 8px;">No results found</h3>
      <p style="margin:0;">Try adjusting your search terms or filters.</p>
    </div>
  @else
    @if (! $results->isEmpty())
      <div style="margin-bottom:20px;">
        <h3 style="margin-bottom:12px; font-size:1.25rem;">Request</h3>
        <div class="cards-grid">
          @foreach ($results as $r)
            @php $allSkills = $r->skills->merge([$r->skill])->unique('Skill_ID')->values(); @endphp
            @php
              $searchOwnerPic = $r->user->Profile_Picture ? asset('storage/'.$r->user->Profile_Picture) : asset('images/default-avatar.svg');
            @endphp
            <div class="card" style="padding:16px;">
              <div style="display:flex; gap:12px; align-items:flex-start;">
                <img src="{{ $searchOwnerPic }}" alt="{{ $r->user->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
                  <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; overflow:hidden;">
                    <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $r->user->Full_Name ?? 'N/A' }}</div>
                    <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                      <span class="badge badge-{{ strtolower($r->Status) }}" style="font-size:0.7rem; white-space:nowrap;">{{ $r->Status }}</span>
                      <a href="{{ route('requests.show', $r->Request_ID) }}" class="btn btn-success btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">View</a>
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
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="pagination-row">
          {{ $results->links('pagination::bootstrap-4') }}
        </div>
      @endif

    @if (! $providers->isEmpty())
      <div style="margin-top:32px;">
        <h3 style="margin-bottom:12px; font-size:1.25rem;">Skill Providers</h3>
        <div class="cards-grid">
          @foreach ($providers as $p)
            @php $userSkills = $p->skills; @endphp
            <div class="card" style="padding:16px;">
              <div style="display:flex; gap:12px; align-items:flex-start;">
                @php
                  $provOwnerPic = $p->Profile_Picture ? asset('storage/'.$p->Profile_Picture) : asset('images/default-avatar.svg');
                @endphp
                <img src="{{ $provOwnerPic }}" alt="{{ $p->Full_Name }}" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;" onerror="this.src='{{ asset('images/default-avatar.svg') }}'">
                <div style="flex:1; min-width:0; display:flex; flex-direction:column; gap:8px;">
                  <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; overflow:hidden;">
                    <div class="card-title" style="font-size:1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">{{ $p->Full_Name }}</div>
                    <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                      <a href="{{ route('profile.show', $p->User_ID) }}" class="btn btn-secondary btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">Profile</a>
                      @if (Auth::check() && Auth::id() !== $p->User_ID)
                        <a href="{{ route('messages.index', ['with' => $p->User_ID]) }}" class="btn btn-primary btn-xs" style="white-space:nowrap; padding:3px 6px; font-size:0.75rem;">Message</a>
                      @endif
                    </div>
                  </div>
                  <div style="display:flex; gap:4px; flex-wrap:wrap;">
                    @foreach ($userSkills->take(2) as $s)
                      <span class="badge badge-open" style="font-size:0.7rem;">{{ $s->Skill_Title }}</span>
                    @endforeach
                    @if ($userSkills->count() > 2)
                      <span class="badge badge-open" style="font-size:0.7rem; opacity:0.7;">+{{ $userSkills->count() - 2 }} more</span>
                    @endif
                  </div>
                  <div style="margin:8px 0; font-size:0.85rem; color:var(--muted);">
                    Rating: {{ number_format((float)$p->Avg_Rating, 2) }}/5 ·
                    Completed: {{ $p->Total_Completed }} ·
                    {{ $p->Is_Verified ? 'Verified' : 'Unverified' }}
                  </div>
                  @if ($p->isNew)
                    <span class="badge badge-new" style="font-size:0.75rem;">New Member</span>
                  @endif
                  @if ($p->Is_Verified)
                    <div style="display:flex;gap:4px;margin-top:4px;">
                      <span class="badge badge-accepted">Verified</span>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="pagination-row">
          {{ $providers->links('pagination::bootstrap-4') }}
        </div>
      @endif
    @endif

</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('select-all-categories');
    var clearAll = document.getElementById('clear-all-categories');
    var checkboxes = document.querySelectorAll('.category-checkbox');
    var toggleBtn = document.getElementById('toggle-categories');
    var categoriesSection = document.getElementById('categories-section');

    if (toggleBtn && categoriesSection) {
      toggleBtn.addEventListener('click', function () {
        if (categoriesSection.style.display === 'none') {
          categoriesSection.style.display = 'block';
          toggleBtn.textContent = 'Hide Categories';
        } else {
          categoriesSection.style.display = 'none';
          toggleBtn.textContent = 'Show Categories';
        }
      });
    }

    selectAll.addEventListener('change', function (e) {
      checkboxes.forEach(function (cb) {
        cb.checked = e.target.checked;
      });
    });

    clearAll.addEventListener('change', function (e) {
      if (e.target.checked) {
        checkboxes.forEach(function (cb) {
          cb.checked = false;
        });
        clearAll.checked = false;
      }
    });

    checkboxes.forEach(function (cb) {
      cb.addEventListener('change', function () {
        selectAll.checked = false;
        selectAll.indeterminate = Array.from(checkboxes).some(function (c) { return c.checked; }) && !Array.from(checkboxes).every(function (c) { return c.checked; });
        clearAll.checked = false;
      });
    });

    var toggles = document.querySelectorAll('.toggle-subcats');
    toggles.forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.stopPropagation();
        var cat = el.getAttribute('data-category');
        var list = document.querySelector('.subcategory-list[data-category="' + cat + '"]');
        if (list.style.display === 'none') {
          list.style.display = 'block';
          el.textContent = '[-]';
        } else {
          list.style.display = 'none';
          el.textContent = '[+]';
        }
      });
    });

    var addBtn = document.getElementById('add-category-btn');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        var catInput = document.querySelector('input[name="new_category"]');
        var subInput = document.querySelector('input[name="new_subcategory"]');
        var cat = catInput.value.trim();
        var sub = subInput.value.trim();
        if (!cat) {
          alert('Please enter a category name.');
          return;
        }
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("search.categories.add") }}';
        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        var catField = document.createElement('input');
        catField.type = 'hidden';
        catField.name = 'category';
        catField.value = cat;
        var subField = document.createElement('input');
        subField.type = 'hidden';
        subField.name = 'subcategory';
        subField.value = sub;

        if (cat) form.appendChild(catField);
        if (sub) form.appendChild(subField);
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
      });
    }
  });
</script>
@endpush
@endsection
