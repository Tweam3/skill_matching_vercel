@extends('layouts.app')

@section('content')
<div class="auth-split">
  <div class="auth-image">
    <div class="auth-image-overlay">
      <div class="auth-info-box">
        <h2>Skill Matching System</h2>
        <p>Connect with peers, share expertise, and grow together at ISAT-U. Find mentors, offer help, and build your skills through collaborative learning.</p>
        <div class="auth-stats">
          <div><strong>500+</strong><span>Students</span></div>
          <div><strong>50+</strong><span>Skills</span></div>
          <div><strong>1K+</strong><span>Matches</span></div>
        </div>
      </div>
    </div>
  </div>
  <div class="auth-form">
    <div class="auth-box">
      <h1>Create Account</h1>
      <p class="auth-sub">Join our skill-matching community</p>
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">
          @foreach ($errors->all() as $err)
            <div>{{ $err }}</div>
          @endforeach
        </div>
      @endif
      <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" required value="{{ old('name') }}" maxlength="100" autocomplete="name">
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="{{ old('email') }}" maxlength="255" autocomplete="email">
        </div>
        <div class="form-group">
          <label for="student_id">Student ID</label>
          <input type="text" id="student_id" name="student_id" required value="{{ old('student_id') }}" placeholder="YYYY-XXXX-X" maxlength="20" autocomplete="off">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <div class="password-field">
            <input type="password" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
          </div>
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <div class="password-field">
            <input type="password" id="password_confirmation" name="password_confirmation" required maxlength="72" autocomplete="new-password">
            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
          </div>
        </div>
        @if (auth()->check() && auth()->user()->Role === 'Admin')
        <div class="form-group">
          <label for="role">Role</label>
          <select id="role" name="role">
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Staff">Staff</option>
            <option value="Admin">Admin</option>
          </select>
        </div>
        @endif
        <div class="form-group">
          <label for="council">Council</label>
          <select id="council" name="council">
            <option value="">Select Council</option>
            <option value="HBM">HBM</option>
            <option value="CSC">CSC</option>
            <option value="BIT">BIT</option>
            <option value="EDUC">EDUC</option>
            <option value="Unaffiliated">Unaffiliated (e.g. PE faculty)</option>
          </select>
        </div>
        @if (auth()->check() && auth()->user()->Role === 'Admin')
        <div class="form-group">
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:500;">
            <input type="checkbox" name="as_admin" value="1" style="width:18px; height:18px; accent-color:var(--navy-primary);">
            Register as Admin
          </label>
        </div>
        @endif
        <button type="submit" class="btn btn-primary btn-block">Register</button>
      </form>
      <p class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Login</a>
      </p>
      @if (auth()->check() && auth()->user()->Role === 'Admin')
        <p style="text-align:center;margin-top:16px;"><a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Cancel</a></p>
      @endif
    </div>
  </div>
</div>
@endsection
