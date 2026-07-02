@extends('layouts.admin')

{{-- Page Title in Browser Tab --}}
@section('title', 'Quick Access')

{{-- Page Heading --}}
@section('page-title', 'Quick Access')

{{-- Breadcrumb --}}
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{route('bookings.index')}}">Quick Access</a></li>
  <li class="breadcrumb-item active" aria-current="page">View</li>
@endsection



{{-- Main Content --}}
@section('content')
<div class="w-full px-2 sm:px-6">
  <livewire:quick-access.quick-access/>
</div>
@endsection

{{-- Page-specific Scripts --}}
@push('scripts')
<script>
  // Add any page-specific JavaScript here
  console.log('Roles index page loaded');
  
  // Example: Auto-hide alerts after 5 seconds
  setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
      let bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
</script>
@endpush