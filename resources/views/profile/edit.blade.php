@extends('layouts.app')

@section('content')
<div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.5rem;">User Profile</h1>
        <p style="color: var(--text-muted);">Manage your account settings and change your password.</p>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2);">
            <i class='bx bx-check-circle'></i> Profile updated successfully.
        </div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="alert alert-success" style="background-color: var(--status-green-bg); color: var(--status-green); border: 1px solid rgba(16,185,129,0.2);">
            <i class='bx bx-check-circle'></i> Password updated successfully.
        </div>
    @endif

    <div class="glass-card">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Profile Information</h2>
        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('patch')

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input id="full_name" name="full_name" type="text" class="form-control" value="{{ old('full_name', $user->full_name) }}" required>
                @error('full_name')
                    <span style="color: var(--status-red); font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input id="username" name="username" type="text" class="form-control" value="{{ old('username', $user->username) }}" required>
                @error('username')
                    <span style="color: var(--status-red); font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <div class="glass-card">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem;">Change Password</h2>
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            @method('put')

            <div class="form-group">
                <label class="form-label" for="current_password">Current Password</label>
                <input id="current_password" name="current_password" type="password" class="form-control" required>
                @error('current_password', 'updatePassword')
                    <span style="color: var(--status-red); font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">New Password</label>
                <input id="password" name="password" type="password" class="form-control" required>
                @error('password', 'updatePassword')
                    <span style="color: var(--status-red); font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required>
                @error('password_confirmation', 'updatePassword')
                    <span style="color: var(--status-red); font-size: 0.85rem;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>
</div>
@endsection
