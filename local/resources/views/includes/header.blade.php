    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="description" content="">
    <meta name="keywords" content="coco bootstrap template, coco admin, bootstrap,admin template, bootstrap admin,">
    <meta name="author" content="Huban Creative">

    <!-- Base Css Files -->
    <link href="{{ asset('assets/libs/jqueryui/ui-lightness/jquery-ui-1.10.4.custom.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/fontello/css/fontello.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/animate-css/animate.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/nifty-modal/css/component.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/magnific-popup/magnific-popup.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/ios7-switch/ios7-switch.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/pace/pace.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/sortable/sortable-theme-bootstrap.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-datepicker/css/datepicker.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/jquery-icheck/skins/all.css') }}" rel="stylesheet" />
    <!-- Code Highlighter for Demo -->
    <link href="{{ asset('assets/libs/prettify/github.css') }}" rel="stylesheet" />

    <!-- Extra CSS Libraries Start -->
    <link href="{{ asset('assets/libs/rickshaw/rickshaw.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/morrischart/morris.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jquery-jvectormap/css/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jquery-clock/clock.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-calendar/css/bic_calendar.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/sortable/sortable-theme-bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jquery-weather/simpleweather.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-xeditable/css/bootstrap-editable.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jquery-datatables/css/dataTables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/jquery-datatables/extensions/TableTools/css/dataTables.tableTools.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/bootstrap-tag/bootstrap-tagsinput.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />
    <!-- Extra CSS Libraries End -->
    <link href="{{ asset('assets/css/style-responsive.css') }}" rel="stylesheet" />

    <style media="screen">
        .side-menu.left { position: fixed; }
        .sidebar-inner { overflow-y: auto !important; }
        .datepicker.dropdown-menu { z-index: 9999999; }
        .select2-container--open .select2-dropdown--below { z-index: 9999999; }
        .logo h1 { text-align: left; padding-left: 10px; }
        .pagination { margin: 0 }
        .pagination>.disabled>span { color: #fff; background-color: #98a3a3; border-color: #ddd; }
        .pagination li a { color: #777; background-color: #fff; border-color: #ddd; }
        .select2-container--default .select2-selection--single, .select2-container--default .select2-search--dropdown .select2-search__field, .select2-dropdown {
            border-color: #ddd;
        }
        .data-table-toolbar { padding: 5px 30px 5px 30px;}
        .table > thead > tr > th { text-align: center; }
        .nav>li>a { padding: 10px; }
        .modal-dialog { margin-top: 100px; }
        .modal-header { padding: 5px; }
        .modal-footer { padding: 5px; }
        input.currency, input.decimal, input.decimal6 { text-align: right; }
        .table hr { margin: 2px; }
        .table > tbody > tr > td { vertical-align: middle; }
        .icheckbox_square-aero { margin-right: 0px; }
        .help-block { margin-top: 0px; margin-bottom: 0px; }
        div[role="tooltip"] { display: none !important; }
        .ui-autocomplete { z-index: 5000; }
        .search-lov label { margin-top: 5px; }
        .search-lov div { padding-right: 0px; }
        .form-horizontal .checkbox-inline { padding-top: 2px; }
        .bootstrap-tagsinput { display: -webkit-box; }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <link rel="shortcut icon" href="{{ asset('assets/img/favicon.ico') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/img/apple-touch-icon-57x57.png') }}" />
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/img/apple-touch-icon-72x72.png') }}" />
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-touch-icon-76x76.png') }}" />
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/img/apple-touch-icon-114x114.png') }}" />
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/img/apple-touch-icon-120x120.png') }}" />
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/img/apple-touch-icon-144x144.png') }}" />
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/img/apple-touch-icon-152x152.png') }}" />
