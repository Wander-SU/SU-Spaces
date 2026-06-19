@extends('layouts.admin')

{{-- Page Title in Browser Tab --}}
@section('title', 'Building Navigation')

{{-- Page Heading --}}
@section('page-title', 'Building Navigation')

{{-- Breadcrumb --}}
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ route('buildingNavigation.index') }}">Building Navigation</a></li>
  <li class="breadcrumb-item active" aria-current="page">View</li>
@endsection



{{-- Main Content --}}
@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">      
      <div class="card-body">
          <livewire:building-navigation.building-navigation/>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
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