@extends('layouts.admin')

{{-- Page Title in Browser Tab --}}
@section('title', 'Base Bookings Management')

{{-- Page Heading --}}
@section('page-title', 'Base Bookings')

{{-- Breadcrumb --}}
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('baseBookings.index') }}">Base Bookings</a></li>
  <li class="breadcrumb-item active" aria-current="page">View</li>
@endsection



{{-- Main Content --}}
@section('content')
<div class="w-full px-4 sm:px-8 max-w-none bg-transparent min-h-screen">
  <livewire:update-timetable.update-timetable/>
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