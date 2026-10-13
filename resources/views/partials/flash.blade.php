@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif

@if (session('warning'))
    <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-error" role="alert">{{ session('error') }}</div>
@endif

@if ($errors->any() && ! session('error'))
    <div class="alert alert-error" role="alert">
        Please correct the highlighted fields and try again.
    </div>
@endif
