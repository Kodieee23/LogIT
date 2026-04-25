@extends('layouts.app')

@section('content')
<div class="login-layout">
    
    <!-- Left Side: Dark Constellation Animation -->
    <div class="login-left">
        <!-- particles.js container -->
        <div id="particles-js"></div>
        
        <div class="login-left-content">
            <h1>LogIT</h1>
            <p>The smart way to track your IT workflow.</p>
        </div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-right">
        <div class="login-card">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error" style="border-radius: var(--radius-sm); margin-bottom: 1rem; padding: 1rem; background-color: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--status-red); color: var(--status-red);">
                    <i class='bx bx-error-circle'></i> {{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success" style="border-radius: var(--radius-sm); margin-bottom: 1rem; padding: 1rem; background-color: var(--status-green-bg); border-left: 4px solid var(--status-green); color: var(--status-green);">
                    <i class='bx bx-check-circle'></i> {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div style="position: relative;">
                        <i class='bx bx-user' style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required autofocus style="padding-left: 3rem;" value="{{ old('username') }}">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 2.5rem;">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <i class='bx bx-lock-alt' style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required style="padding-left: 3rem;">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Log In
                </button>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="#" onclick="document.getElementById('forgot-password-form').style.display='block'; this.style.display='none'; return false;" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: underline;">Forgot Password?</a>
                </div>
            </form>

            <form id="forgot-password-form" action="{{ route('password.request.admin') }}" method="POST" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                @csrf
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">Enter your username to request a password reset from the administrator.</p>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
                <button type="submit" class="btn btn-outline" style="width: 100%; border-color: var(--primary); color: var(--primary);">Request Reset</button>
            </form>
        </div>
    </div>
</div>
@endsection
