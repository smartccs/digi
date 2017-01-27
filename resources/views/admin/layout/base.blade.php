<!DOCTYPE html>
<html lang="en">
	<head>
		<!-- Meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Title -->
		<title>Xuber - @yield('title') - Admin Dashboard</title>

		<!-- Vendor CSS -->
		<link rel="stylesheet" href="{{asset('admin/vendor/bootstrap4/css/bootstrap.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/themify-icons/themify-icons.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/font-awesome/css/font-awesome.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/animate.css/animate.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/jscrollpane/jquery.jscrollpane.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/waves/waves.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/switchery/dist/switchery.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/DataTables/css/dataTables.bootstrap4.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/DataTables/Responsive/css/responsive.bootstrap4.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/DataTables/Buttons/css/buttons.dataTables.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/DataTables/Buttons/css/buttons.bootstrap4.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/vendor/switchery/dist/switchery.min.css')}}">
		<link rel="stylesheet" href="{{asset('admin/assets/css/core.css')}}">

		<!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		@yield('styles')
	</head>
	<body class="fixed-sidebar fixed-header content-appear skin-default">

		<div class="wrapper">

			<!-- Preloader -->
			<div class="preloader"></div>

			<!-- Sidebar -->
			<div class="site-overlay"></div>


				@include('admin.include.nav')			

				@include('admin.include.header')

				<div class="site-content">

					@include('layouts.notify')

					@yield('content')

					@include('admin.include.footer')

				</div>

		</div>

			<!-- Vendor JS -->
			<script type="text/javascript" src="{{asset('admin/vendor/jquery/jquery-1.12.3.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/tether/js/tether.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/bootstrap4/js/bootstrap.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/detectmobilebrowser/detectmobilebrowser.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/jscrollpane/jquery.mousewheel.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/jscrollpane/mwheelIntent.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/jscrollpane/jquery.jscrollpane.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/jquery-fullscreen-plugin/jquery.fullscreen')}}-min.js"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/waves/waves.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/switchery/dist/switchery.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/js/jquery.dataTables.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/js/dataTables.bootstrap4.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Responsive/js/dataTables.responsi')}}ve.min.js"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Responsive/js/responsive.bootstra')}}p4.min.js"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Buttons/js/dataTables.buttons')}}.min.js"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Buttons/js/buttons.bootstrap4')}}.min.js"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/JSZip/jszip.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/pdfmake/build/pdfmake.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/pdfmake/build/vfs_fonts.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Buttons/js/buttons.html5.min.js')}}"></script>

			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Buttons/js/buttons.print.min.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/vendor/DataTables/Buttons/js/buttons.colVis.min.js')}}"></script>

			<script type="text/javascript" src="{{asset('admin/vendor/switchery/dist/switchery.min.js')}}"></script>
			<!-- Neptune JS -->
			<script type="text/javascript" src="{{asset('admin/assets/js/app.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/assets/js/demo.js')}}"></script>
			<script type="text/javascript" src="{{asset('admin/assets/js/tables-datatable.js')}}"></script>

			@yield('scripts')
		</body>

</html>