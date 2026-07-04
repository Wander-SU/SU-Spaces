<!--begin::Sidebar-->
<aside class="app-sidebar bg-[#02338D] text-white w-64 min-h-screen flex flex-col justify-between" data-bs-theme="dark" style="background-color: #02338D !important;">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand w-full bg-white py-3 px-4 flex items-center justify-center border-b border-gray-100">
    <!--begin::Brand Link-->
    <a href="{{ URL::to('/') }}" class="brand-link px-0 py-0 border-0 w-full flex items-center justify-center">
      <!--begin::Brand Image-->
      <img
        src="{{ asset('images/strathmore_logo.png') }}"
        alt="{{ env('APP_NAME')}}"
        class="brand-image block mx-auto w-auto max-w-full object-contain"
        style="max-height: 2.75rem; transform: scale(2.5); transform-origin: center;"
      />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light visually-hidden">{{ env('APP_NAME')}}</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->
  
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper flex-1 pt-2">
    <nav class="space-y-0.5">
      <!--begin::Sidebar Menu-->
      @php
        $activeNavClass = 'flex items-center gap-3 px-6 py-3.5 bg-white/10 text-white font-semibold border-l-4 border-[#c99d3b] transition-all rounded-none font-sans text-sm';
        $defaultNavClass = 'flex items-center gap-3 px-6 py-3.5 text-gray-300 hover:bg-white/5 hover:text-white transition-all rounded-none font-sans text-sm font-medium';
      @endphp
      <ul
        class="nav sidebar-menu flex-column px-0"
        data-lte-toggle="treeview"
        role="navigation"
        aria-label="Main navigation"
        data-accordion="false"
      >      
        {{-- Booking Rooms Section --}}
        <li class="nav-header px-6 pt-2 pb-1 text-[11px] uppercase tracking-wide text-white/60">Booking Rooms</li>

        {{-- Quick Access --}}
        <li class="w-full">
          <a href="{{ route('bookings.index') }}" class="nav-link {{ request()->routeIs('bookings.index') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-fast-forward-btn-fill"></i>
            <p>
              Quick Access
            </p>
          </a>
        </li>

        {{-- Book Rooms --}}
        <li class="w-full">
          <a href="{{ route('buildingNavigation.index') }}" class="nav-link {{ request()->routeIs('buildingNavigation.index') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-bookmark-check-fill"></i>
            <p>
              Book Rooms
            </p>
          </a>
        </li>

        {{-- Previous bookings report/list page --}}
        <li class="w-full">
          <a href="{{ route('bookings.previous') }}" class="nav-link {{ request()->routeIs('bookings.previous') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-clock-history"></i>
            <p>
              All Bookings
            </p>
          </a>
        </li>

        @if(auth()->user()->role->role_name!="Student" && auth()->user()->role->role_name!="Lecturer" )
        {{-- Timetable Management Section --}}
        <li class="nav-header px-6 pt-4 pb-1 text-[11px] uppercase tracking-wide text-white/60">Timetable Management</li>
      
        {{-- Users --}}
        <li class="w-full">
          <a href="{{ route('baseBookings.index') }}" class="nav-link {{ request()->routeIs('baseBookings.index') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-calendar-date-fill"></i>
            <p>Timetable</p>
          </a>
        </li>
        @endif

        @if(auth()->user()->role->role_name=="System Admin" )
        {{-- User Management Section --}}
        <li class="nav-header px-6 pt-4 pb-1 text-[11px] uppercase tracking-wide text-white/60">User Management</li>
      
        {{-- Users --}}
        <li class="w-full">
          <a href="{{ route('userManagement.index') }}" class="nav-link {{ request()->routeIs('userManagement.index') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-person-lines-fill"></i>
            <p>Users</p>
          </a>
        </li>

        {{-- Report Generation Section --}}
        <li class="nav-header px-6 pt-4 pb-1 text-[11px] uppercase tracking-wide text-white/60">Reports Section</li>
      
        {{-- Users --}}
        <li class="w-full">
          <a href="{{ route('reports.dashboard') }}" class="nav-link {{ request()->routeIs('reports.dashboard') ? $activeNavClass : $defaultNavClass }}">
            <i class="nav-icon bi bi-bar-chart-line"></i>
            <p>Reports</p>
          </a>
        </li>
        @endif
        
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->