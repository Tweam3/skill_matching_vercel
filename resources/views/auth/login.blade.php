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
      <h1>Welcome Back</h1>
      <p class="auth-sub">Login to Skill Matching System</p>
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
      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="{{ old('email') }}" autocomplete="email">
        </div>
<div class="form-group">
          <label for="password" style="margin:0;">Password</label>
          <div class="password-field">
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="button" class="password-toggle" aria-label="Toggle password visibility">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
        <a href="{{ route('password.request') }}" class="forgot-link" style="font-size:0.85rem;color:var(--primary);text-decoration:none;display:block;text-align:center;margin-top:12px;">Forgot Password?</a>
        <p style="text-align:center;margin:18px 0 0;color:var(--muted);font-size:0.9rem;">
        No account? <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600;">Register</a>
      </p>
    </div>
  </div>
</div>
@endsection
