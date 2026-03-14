<!-- BEGIN: Theme CSS-->
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/tabler-icons.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />
<!-- Core CSS -->
<link rel="stylesheet" href="{{ asset('assets/vendor/css' . ($configData['rtlSupport'] ? '/rtl' : '') . '/core.css') }}" class="{{ $configData['hasCustomizer'] ? 'template-customizer-core-css' : '' }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/css' . ($configData['rtlSupport'] ? '/rtl' : '') . '/' . $configData['theme'] . '.css') }}" class="{{ $configData['hasCustomizer'] ? 'template-customizer-theme-css' : '' }}" />
<link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/typeahead-js/typeahead.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/toastr/toastr.css') }}" />

<!-- Font Settings CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/font-settings.css') }}" />
<!-- Font Size Switcher CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/font-size-switcher.css') }}" />

<!-- Responsive Design CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/responsive-tables.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/mobile-responsive.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/responsive-navigation.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/css/touch-friendly.css') }}" />

<!-- Table Action Buttons CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/table-action-buttons.css') }}" />

<!-- DataTables Fixes CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/datatable-fixes.css') }}" />

<!-- Dark Mode CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/dark-mode.css') }}" />

<!-- Vendor Styles -->
@yield('vendor-style')


<!-- Page Styles -->
@yield('page-style')

