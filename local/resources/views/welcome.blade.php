<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Under Maintenance | Coco - Responsive Bootstrap Admin Template</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="description" content="">
    <meta name="keywords" content="coco bootstrap template, coco admin, bootstrap,admin template, bootstrap admin,">
    <meta name="author" content="Huban Creative">

    <!-- Base Css Files -->
    <link href="{{('assets/libs/jqueryui/ui-lightness/jquery-ui-1.10.4.custom.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/fontello/css/fontello.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/animate-css/animate.min.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/nifty-modal/css/component.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/magnific-popup/magnific-popup.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/ios7-switch/ios7-switch.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/pace/pace.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/sortable/sortable-theme-bootstrap.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/bootstrap-datepicker/css/datepicker.css')}}" rel="stylesheet" />
    <link href="{{('assets/libs/jquery-icheck/skins/all.css')}}" rel="stylesheet" />
    <!-- Code Highlighter for Demo -->
    <link href="{{('assets/libs/prettify/github.css')}}" rel="stylesheet" />

    <!-- Extra CSS Libraries Start -->
    <link href="{{('assets/css/style.css')}}" rel="stylesheet" type="text/css" />
    <!-- Extra CSS Libraries End -->
    <link href="{{('assets/css/style-responsive.css')}}" rel="stylesheet" />

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

    <link rel="shortcut icon" href="{{('assets/img/favicon.ico')}}">
    <link rel="apple-touch-icon" href="{{('assets/img/apple-touch-icon.png')}}" />
    <link rel="apple-touch-icon" sizes="57x57" href="{{('assets/img/apple-touch-icon-57x57.png')}}" />
    <link rel="apple-touch-icon" sizes="72x72" href="{{('assets/img/apple-touch-icon-72x72.png')}}" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{('assets/img/apple-touch-icon-76x76.png')}}" />
    <link rel="apple-touch-icon" sizes="114x114" href="{{('assets/img/apple-touch-icon-114x114.png')}}" />
    <link rel="apple-touch-icon" sizes="120x120" href="{{('assets/img/apple-touch-icon-120x120.png')}}" />
    <link rel="apple-touch-icon" sizes="144x144" href="{{('assets/img/apple-touch-icon-144x144.png')}}" />
    <link rel="apple-touch-icon" sizes="152x152" href="{{('assets/img/apple-touch-icon-152x152.png')}}" />
</head>

<body class="fixed-left login-page">
    <!-- Begin page -->
    <div class="container">
        <div class="full-content-center">
            <div class="maintenance animated flipInX">
                <div class="">
                    <h1><i class="icon-wrench"></i><br>
                    Under Maintenance</h1>
                    <h2>Please give us a moment to sort things out</h2>
                </div>
            </div>

        </div>
        <div align="center"> All Right Reserved <a href="{{ url('/home') }}">©</a> 2016 by DCK  </div>
    </div>
    <!-- the overlay modal element -->
    <div class="md-overlay"></div>
    <!-- End of eoverlay modal -->
    <script>
        var resizefunc = [];
    </script>
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="{{('assets/libs/jquery/jquery-1.11.1.min.js')}}"></script>
    <script src="{{('assets/libs/bootstrap/js/bootstrap.min.js')}}"></script>
    <script src="{{('assets/libs/jqueryui/jquery-ui-1.10.4.custom.min.js')}}"></script>
    <script src="{{('assets/libs/jquery-ui-touch/jquery.ui.touch-punch.min.js')}}"></script>
    <script src="{{('assets/libs/jquery-detectmobile/detect.js')}}"></script>
    <script src="{{('assets/libs/jquery-animate-numbers/jquery.animateNumbers.js')}}"></script>
    <script src="{{('assets/libs/ios7-switch/ios7.switch.js')}}"></script>
    <script src="{{('assets/libs/fastclick/fastclick.js')}}"></script>
    <script src="{{('assets/libs/jquery-blockui/jquery.blockUI.js')}}"></script>
    <script src="{{('assets/libs/bootstrap-bootbox/bootbox.min.js')}}"></script>
    <script src="{{('assets/libs/jquery-slimscroll/jquery.slimscroll.js')}}"></script>
    <script src="{{('assets/libs/jquery-sparkline/jquery-sparkline.js')}}"></script>
    <script src="{{('assets/libs/nifty-modal/js/classie.js')}}"></script>
    <script src="{{('assets/libs/nifty-modal/js/modalEffects.js')}}"></script>
    <script src="{{('assets/libs/sortable/sortable.min.js')}}"></script>
    <script src="{{('assets/libs/bootstrap-fileinput/bootstrap.file-input.js')}}"></script>
    <script src="{{('assets/libs/bootstrap-select/bootstrap-select.min.js')}}"></script>
    <script src="{{('assets/libs/bootstrap-select2/select2.min.js')}}"></script>
    <script src="{{('assets/libs/magnific-popup/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{('assets/libs/pace/pace.min.js')}}"></script>
    <script src="{{('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.js')}}"></script>
    <script src="{{('assets/libs/jquery-icheck/icheck.min.js')}}"></script>

    <!-- Demo Specific JS Libraries -->
    <script src="{{('assets/libs/prettify/prettify.js')}}"></script>

    <script src="{{('assets/js/init.js')}}"></script>
</body>

</html>
