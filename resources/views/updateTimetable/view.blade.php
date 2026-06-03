@extends('layouts.admin')

{{-- Page Title in Browser Tab --}}
@section('title', 'Roles Management')

{{-- Page Heading --}}
@section('page-title', 'Roles')

{{-- Breadcrumb --}}
@section('breadcrumb')
  <li class="breadcrumb-item"><a href="{{ URL::to('/') }}">Home</a></li>
  <li class="breadcrumb-item active" aria-current="page">Roles</li>
@endsection



{{-- Main Content --}}
@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">All Roles</h3>
        <div class="card-tools">
          <form action="{{ route('baseBookings.updateFull') }}" method="POST">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Update Full Timetable
            </button>
          </form>
        </div>
      </div>
      <!-- /.card-header -->
      
      <div class="card-body">
          
          
          {{-- Pagination --}}
          <div class="mt-3">
            
          </div>
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