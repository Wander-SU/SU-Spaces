<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-body" data-bs-theme="dark">
  <!--begin::Container-->
  <div class="container-fluid">
    <!--begin::Start Navbar Links-->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
          <i class="bi bi-list"></i>
        </a>
      </li>
    </ul>
    <!--end::Start Navbar Links-->
    
    <!--begin::End Navbar Links-->
    <ul class="navbar-nav ms-auto">
      <!--begin::Fullscreen Toggle-->
      <li class="nav-item">
        <a class="nav-link" href="#" data-lte-toggle="fullscreen">
          <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
          <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
        </a>
      </li>
      <!--end::Fullscreen Toggle-->
      
      <!--begin::User Menu Dropdown-->
      <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
          <img
            {{-- src="https://avatar.iran.liara.run/username?username={{ auth()->user()->name }}" --}}
            src="https://avatar.iran.liara.run/username?username=SU+Spaces"
            class="user-image rounded-circle shadow"
            alt="User Image"
          />
          <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'Guest' }}</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
          <!--begin::User Image-->
          <li class="user-header text-bg-secondary">
            <img
              {{-- src="https://avatar.iran.liara.run/username?username={{ auth()->user()->name }}" --}}
              src="https://avatar.iran.liara.run/username?username=SU+Spaces"
              class="rounded-circle shadow"
              alt="User Image"
            />
            <p>
              {{-- <small>{{ auth()->user()->role->description }} since {{date('Y',strtotime(auth()->user()->created_at))}}</small> --}}
              <small>Should be changed since Yesterday</small>
            </p>
          </li>
          <!--end::User Image-->
          
          <!--begin::Menu Footer-->
          <li class="user-footer">
            {{-- <a href="{{ route('user.profile') }}" class="btn btn-default btn-flat">Profile</a> --}}
            <a href="{{ route('default') }}" class="btn btn-default btn-flat">Profile</a>
            {{-- <form action="{{ route('logout') }}" method="POST" class="d-inline"> --}}
                <form action="{{ route('default') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-default btn-flat float-end">Sign out</button>
            </form>
          </li>
          <!--end::Menu Footer-->
        </ul>
      </li>
      <!--end::User Menu Dropdown-->
    </ul>
    <!--end::End Navbar Links-->
  </div>
  <!--end::Container-->
</nav>
<!--end::Header-->