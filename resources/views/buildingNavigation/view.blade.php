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

@push('styles')
<style>
  .content-wrapper {
    background-color: #F5F6F8;
  }

  .content-wrapper .card {
    border: 1px solid #D9D9D9;
    border-radius: 14px;
    background: #FFFFFF;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  }

  .content-wrapper .card-body {
    border-radius: 14px;
  }

  @media (max-width: 767.98px) {
    .content-wrapper .card-body {
      padding: 0.9rem;
    }
  }
</style>
@endpush



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