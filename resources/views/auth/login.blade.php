@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
    <div class="card card-narrow">
        <div class="page-head">
            <h1>Admin Login</h1>
            <p class="muted">Sign in to manage student records.</p>
        </div>

        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf

            <div class="form-field {{ $errors->has('email') ? 'has-error' : '' }}">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-field {{ $errors->has('password') ? 'has-error' : '' }}">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>

            <label class="checkbox">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
        </form>
    </div>
@endsection
