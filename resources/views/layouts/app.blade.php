<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Admission') · School Student Admission Records</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('admission.select') }}">
                <span class="brand-mark">SR</span>
                <span class="brand-text">School Student Admission Records</span>
            </a>
            <nav class="site-nav">
    @auth
        <a href="{{ route('admission.select') }}">New Admission</a>
        <a href="{{ route('admin.students.index') }}">Manage Students</a>
        <a href="{{ route('admin.classes.index') }}">Classes &amp; Sections</a>
        <a href="{{ route('branding.logo.edit') }}">Upload Logo</a>

        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="link-button">
                Log out ({{ auth()->user()->name }})
            </button>
        </form>
    @endauth
</nav>
        </div>
    </header>

    <main class="container main-content">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <p class="muted">Student records are stored in Google Sheets. No student data is kept in the database.</p>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
