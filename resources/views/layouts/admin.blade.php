<!doctype html>
<html lang="en" class="scroll-smooth antialiased text-rendering-optimizeLegibility font-sans">
  <!--begin::Head-->
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, shrink-to-fit=no" />
    <title>@yield('title', 'SU-Spaces | Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/strathmore_emblem.png') }}" />
    
    <!--begin::Accessibility Meta Tags-->
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <style>
      :root {
        --bs-body-font-family: 'Inter', sans-serif;
      }

      /* Global system dashboard typography optimization overrides */
      html:not(.auth-page) {
          /* Bumps the base 1rem text vector scale up cleanly across all platforms */
          font-size: 16px !important;
      }

      html:not(.auth-page) body {
          font-size: 1rem;
          line-height: 1.6;
      }

      body {
        font-family: 'Inter', sans-serif;
      }
    </style>
    
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.css') }}" />
    <!--end::Required Plugin(AdminLTE)-->

    @if (file_exists(public_path('build/manifest.json')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
      <script src="https://cdn.tailwindcss.com"></script>
      <script>
        tailwind.config = {
          darkMode: 'class',
        };
      </script>
    @endif
    {{-- Begin: Localised StyleSheet --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    {{-- End: Localised Stylesheet --}}
    
    {{-- Page-specific styles --}}
    @stack('styles')

    <style>
      /* Force global root document scaling definitions */
      html {
        /* Adjusts the base root em calculation (1rem) globally */
        font-size: 14px !important;
      }

      /* Windows OS display scaling overrides */
      @media screen and (-webkit-min-device-pixel-ratio: 1.25), screen and (min-resolution: 120dpi) {
        html {
          font-size: 13px !important; /* Deflates the root tracking layout on scaled Windows machines */
        }
      }

      /* Mobile viewports text inflation prevention */
      @media (max-width: 768px) {
        html {
          -webkit-text-size-adjust: 100%;
          text-size-adjust: 100%;
        }
      }
    </style>

    <style>
      html, body {
          /* Prevents default browser font inflation routines */
          -webkit-text-size-adjust: 100%;
          text-size-adjust: 100%;
      }
      @media screen and (min-width: 1024px) {
          .auth-root-shell {
              /* Forces the base em grid down strictly for widescreen scaling display layouts */
              font-size: 14px !important;
          }
      }
    </style>
  </head>
  <!--end::Head-->
  
  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">
      
      {{-- Include Header --}}
      @include('layouts.partials.header')
      
      {{-- Include Sidebar --}}
      @include('layouts.partials.sidebar')
      
      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-12">
                @php
                  $pageTitle = trim($__env->yieldContent('page-title')) ?: 'Dashboard';
                @endphp
                <div class="w-full mb-6 mt-4 border-l-4 border-[#941c1c] pl-4 transition-all duration-300">
                  <h1 class="text-xl sm:text-2xl font-sans font-extrabold text-[#1d2d54] uppercase tracking-wide leading-none">
                    {{ $pageTitle ?? 'Dashboard' }}
                  </h1>
                </div>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->
        
        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">
            
            {{-- Flash Messages --}}
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif
            
            @if(session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif
            
            @if(session('info'))
              <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif
            {{-- Main Content Area - This is where child views inject content --}}
            @yield('content')
            
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->
      
      {{-- Include Footer --}}
      @include('layouts.partials.footer')
      
    </div>
    <!--end::App Wrapper-->
    
    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="{{ asset('adminlte/js/adminlte.js') }}"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal !== 'undefined') {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->
    
    {{-- Page-specific scripts --}}
    @stack('scripts')
    
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>