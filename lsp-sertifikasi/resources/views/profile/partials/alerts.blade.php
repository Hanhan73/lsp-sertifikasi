{{-- resources/views/profile/partials/alerts.blade.php --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
    <div>{{ session('success') }}</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-x-circle-fill flex-shrink-0"></i>
    <div>{{ session('error') }}</div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
</div>
@endif