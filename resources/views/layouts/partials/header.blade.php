<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-white dark:bg-[#161615] border-b border-gray-200/80 position-sticky top-0 end-0">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Start Navbar Links-->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link text-white hover:bg-white/10 transition-colors rounded-md" data-lte-toggle="sidebar" href="#" role="button" style="color: #ffffff !important;">
          <i class="bi bi-list" style="color: #ffffff !important;"></i>
        </a>
      </li>
    </ul>
    <!--end::Start Navbar Links-->
    
    <!--begin::End Navbar Links-->
    <ul class="navbar-nav ms-auto">
      <!--begin::Fullscreen Toggle-->
      <li class="nav-item">
        <a class="nav-link text-white hover:bg-white/10 transition-colors rounded-md" href="#" data-lte-toggle="fullscreen" style="color: #ffffff !important;">
          <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen" style="color: #ffffff !important;"></i>
          <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none; color: #ffffff !important;"></i>
        </a>
      </li>
      <!--end::Fullscreen Toggle-->
      
      <!--begin::User Menu Dropdown-->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link p-0 flex items-center justify-center align-middle self-center" data-bs-toggle="dropdown" aria-label="User menu">
          @php
            $name = auth()->user()->name ?? 'Guest';
            $parts = preg_split('/\s+/', trim($name));
            $initials = '';
            foreach (array_slice($parts ?: ['G'], 0, 2) as $part) {
              $initials .= strtoupper(substr($part, 0, 1));
            }
          @endphp
          <span class="w-9 h-9 flex items-center justify-center align-middle self-center bg-white text-[#941c1c] rounded-full font-bold font-sans text-sm shadow-xs">{{ $initials }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden z-50 p-1.5">
          <li>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="flex w-full items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 rounded-lg font-sans transition-colors text-left">Sign out</button>
            </form>
          </li>
        </ul>
      </li>
      <!--end::User Menu Dropdown-->
    </ul>
    <!--end::End Navbar Links-->
  </div>
  <!--end::Container-->
</nav>
<!--end::Header-->