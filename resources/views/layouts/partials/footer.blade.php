<!--begin::Footer-->
<footer class="app-footer bg-success-subtle">
  <!--begin::To the end-->
  <div class="float-end d-none d-sm-inline">
    Version 0.0.1
  </div>
  <!--end::To the end-->
  
  <!--begin::Copyright-->
  <strong>
    Copyright &copy; {{ date('Y') }}&nbsp;
    <a href="{{ URL::to('/') }}" class="text-decoration-none">{{ env('APP_NAME') }}</a>.
  </strong>
  All rights reserved.
  <!--end::Copyright-->
</footer>
<!--end::Footer--> 