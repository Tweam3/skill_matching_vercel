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
      <h1>Forgot Password?</h1>
      <p class="auth-sub">Enter your email and we'll send you a reset link</p>
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
      <form method="POST" action="{{ route('password.request') }}">
        @csrf
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required value="{{ old('email') }}" autocomplete="email">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
      </form>
      <p style="text-align:center;margin:18px 0 0;color:var(--muted);font-size:0.9rem;">
        Remember your password? <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600;">Login</a>
      </p>
    </div>
  </div>
</div>
@endsection