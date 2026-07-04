<!--begin::Footer-->
<footer class="app-footer w-full bg-white border-t border-gray-200/60 py-4 px-6 flex flex-col sm:flex-row justify-center items-center text-center gap-2">
  <p class="text-xs font-sans font-medium text-gray-400 tracking-wide mb-0">
    Copyright &copy; {{ date('Y') }}
    <a href="{{ URL::to('/') }}" class="text-gray-400 no-underline">{{ env('APP_NAME') }}</a>.
    All rights reserved.
  </p>
</footer>
<!--end::Footer--> 