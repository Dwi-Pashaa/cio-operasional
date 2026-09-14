<!doctype html>
<html lang="en">

<head>
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>@yield('title') &mdash; {{ config('app.name') }}</title>
	<!-- CSS files -->
	<link href="{{asset('')}}css/tabler.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-flags.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-socials.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-payments.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-vendors.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-marketing.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/demo.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('css/custom-theme.css')}}?v={{ time() }}" rel="stylesheet" />
	<!-- Select2 CSS -->
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
	<style>
		/* Custom Tabler integration for Select2 */
		.select2-container--bootstrap-5 .select2-selection {
			border: 1px solid #dce1e7;
			border-radius: 4px;
			min-height: 38px;
			padding: 0.375rem 0.75rem;
			font-size: 0.875rem;
			background-color: #ffffff;
		}
		.select2-container--bootstrap-5.select2-container--focus .select2-selection,
		.select2-container--bootstrap-5.select2-container--open .select2-selection {
			border-color: #90b5e2;
			box-shadow: 0 0 0 0.25rem rgba(32, 107, 196, 0.25);
		}
		.select2-container--bootstrap-5 .select2-dropdown {
			border-color: #dce1e7;
			box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
			border-radius: 4px;
		}
		.select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
			background-color: #206bc4;
			color: #ffffff;
		}
		.select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
			color: #1e293b;
			padding-left: 0;
			line-height: 1.5;
		}
	</style>
	@stack('css')
</head>

<body>
	<script src="{{asset('')}}js/demo-theme.min.js?1738096685"></script>
	<div class="page">
		<!-- Navbar -->
		<div class="sticky-top">
			@include('components.header')
            @include('components.navbar')
		</div>
		<div class="page-wrapper">
			<!-- Page header -->
			<div class="page-header d-print-none">
				<div class="container-xl">
					<div class="row g-2 align-items-center">
						<div class="col">
							<!-- Page pre-title -->
							<div class="page-pretitle">
								Pages
							</div>
							<h2 class="page-title">
								@yield('title')
							</h2>
						</div>
						@hasSection('page_actions')
							<div class="col-auto ms-auto d-print-none">
								@yield('page_actions')
							</div>
						@endif
					</div>
				</div>
			</div>
			<!-- Page body -->
			<div class="page-body">
				<div class="container-xl">
                    @yield('content')
				</div>
			</div>
			<footer class="footer footer-transparent d-print-none">
				<div class="container-xl">
					<div class="row text-center align-items-center flex-row-reverse">
						<div class="col-12 col-lg-auto mt-3 mt-lg-0">
							<ul class="list-inline list-inline-dots mb-0">
								<li class="list-inline-item">
									Copyright &copy; {{ date('Y') }} {{ config('app.name') }} All rights reserved.
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>
	@stack('modal')
	<!-- Libs JS -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="{{asset('')}}libs/apexcharts/dist/apexcharts.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/jsvectormap.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world-merc.js?1738096685" defer></script>
	<!-- Tabler Core -->
	<script src="{{asset('')}}js/tabler.min.js?1738096685" defer></script>
	<script src="{{asset('')}}js/demo.min.js?1738096685" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<!-- Select2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	<script>
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		}); 
	</script>
	@stack('js')
	@stack('scripts')
</body>

</html>