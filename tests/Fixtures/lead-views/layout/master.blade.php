<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="file:///{{ str_replace('\\', '/', public_path('assets/css/bootstrap.min.css')) }}">
<link rel="stylesheet" href="file:///{{ str_replace('\\', '/', public_path('assets/css/icons.css')) }}">
<link rel="stylesheet" href="file:///{{ str_replace('\\', '/', public_path('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')) }}">
<style>body{background:#f5f7fb;color:#34445e;font-family:Arial,sans-serif;padding:24px}.page-content{max-width:1500px;margin:auto}.table{color:inherit}</style>
@stack('styles')</head><body>@yield('content')
<script src="file:///{{ str_replace('\\', '/', public_path('assets/js/jquery.min.js')) }}"></script>
<script src="file:///{{ str_replace('\\', '/', public_path('assets/js/bootstrap.bundle.min.js')) }}"></script>
<script src="file:///{{ str_replace('\\', '/', public_path('assets/plugins/datatable/js/jquery.dataTables.min.js')) }}"></script>
<script src="file:///{{ str_replace('\\', '/', public_path('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')) }}"></script>
@stack('scripts')</body></html>
