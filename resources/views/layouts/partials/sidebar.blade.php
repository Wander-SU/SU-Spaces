<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="{{ URL::to('/') }}" class="brand-link">
      <!--begin::Brand Image-->
      <img
        src="{{ asset('adminlte/assets/img/photo1.png') }}"
        alt="{{ env('APP_NAME')}}"
        class="brand-image opacity-75 shadow"
      />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light">{{ env('APP_NAME')}}</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->
  
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
      >      
        {{-- Booking Rooms Section --}}
        <li class="nav-header">Booking Rooms</li>

        {{-- Quick Access --}}
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-fast-forward-btn-fill"></i>
            <p>
              Quick Access
            </p>
          </a>
        </li>

        {{-- Book Rooms --}}
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-bookmark-check-fill"></i>
            <p>
              Book Rooms
            </p>
          </a>
        </li>

        {{-- Previous Bookings --}}
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-clock-history"></i>
            <p>
              Previous Bookings
            </p>
          </a>
        </li>

        {{-- Timetable Management Section --}}
        <li class="nav-header">Timetable Management</li>
      
        {{-- Users --}}
        <li class="nav-item">
          <a href="{{ route('baseBookings.index') }}" class="nav-link">
            <i class="nav-icon bi bi-calendar-date-fill"></i>
            <p>Timetable</p>
          </a>
        </li>
        
        {{-- User Management Section --}}
        <li class="nav-header">User Management</li>
      
        {{-- Users --}}
        <li class="nav-item">
          <a href="{{ route('userManagement.index') }}" class="nav-link">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Users</p>
          </a>
        </li>

        {{-- Report Generation Section --}}
        <li class="nav-header">Reports Section</li>
      
        {{-- Users --}}
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-bar-chart-line"></i>
            <p>Reports</p>
          </a>
        </li>
        
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->