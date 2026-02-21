<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>@yield('title', 'MikroTik Manager')</title>
  <meta content="" name="description">

  <!-- Favicons -->
  <link href="{{ asset('assets/img/favicon.png') }}" rel="icon">
  <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- Template CSS -->
  <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

  <style>
    .status-light { width:10px; height:10px; border-radius:50%; display:inline-block; }
    .status-light.online  { background:#28a745; box-shadow:0 0 6px #28a745; }
    .status-light.offline { background:#dc3545; box-shadow:0 0 6px #dc3545; }
  </style>

  @stack('styles')
</head>

<body>

  @include('layouts.header')
  @include('layouts.sidebar')

  <main id="main" class="main">

    {{-- Trial expiry banner --}}
    @if(isset($currentTenant) && $currentTenant->isOnTrial())
    @php $daysLeft = $currentTenant->trialDaysLeft(); @endphp
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-3"
         style="border-radius:0;margin:-20px -20px 20px;padding:10px 24px;" role="alert">
        <i class="bi bi-clock-history"></i>
        <span>
            <strong>Free Trial:</strong>
            @if($daysLeft > 0)
                {{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }} remaining.
            @else
                Your trial expires today!
            @endif
            <a href="#" class="alert-link ms-2">Upgrade now →</a>
        </span>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
  </main>

  @include('layouts.footer')

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Vendor JS -->
  <script src="{{ asset('assets/vendor/apexcharts/apexcharts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
  <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
  <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
  <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- Global SweetAlert flash messages --}}
  @if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({ icon: 'success', html: @json(session('success')), timer: 3500, timerProgressBar: true, showConfirmButton: false, position: 'top-end', toast: true });
    });
  </script>
  @endif

  @if(session('error'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({ icon: 'error', title: 'Error', html: @json(session('error')), confirmButtonColor: '#d33' });
    });
  </script>
  @endif

  @if(session('warning'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({ icon: 'warning', title: 'Warning', html: @json(session('warning')), timer: 4000, timerProgressBar: true, showConfirmButton: false, position: 'top-end', toast: true });
    });
  </script>
  @endif

  {{-- Page-specific scripts go here --}}
  @stack('scripts')

</body>
</html>
