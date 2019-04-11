@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<!-- ============================================================== -->
<!-- Start Content here -->
<!-- ============================================================== -->
<div class="content">
 <!-- Start info box -->
 <div class="row top-summary">
    <div class="col-lg-3 col-md-6">
        <div class="widget orange-3 animated fadeInDown">
            <div class="widget-content padding">
                <div class="widget-icon">
                    <i class="glyphicon glyphicon-transfer"></i>
                </div>
                <div class="text-box">
                    <p class="maindata">{{ strtoupper(trans('shared/dashboard.total-resi')) }}</p>
                    <h2><span class="animate-number" data-value="{{ $totalResi }}" data-duration="1000">0</span></h2>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="widget darkblue-3 animated fadeInDown">
            <div class="widget-content padding">
                <div class="widget-icon">
                    <i class="fa fa-truck"></i>
                </div>
                <div class="text-box">
                    <p class="maindata">{{ strtoupper(trans('shared/dashboard.total-manifest')) }}</p>
                    <h2><span class="animate-number" data-value="{{ $totalManifest }}" data-duration="1000">0</span></h2>

                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="widget lightblue-1 animated fadeInDown">
            <div class="widget-content padding">
                <div class="widget-icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="text-box">
                    <p class="maindata">{{ strtoupper(trans('shared/dashboard.total-do')) }}</p>
                    <h2><span class="animate-number" data-value="{{ $totalDO }}" data-duration="1000">0</span></h2>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="widget green-1 animated fadeInDown">
            <div class="widget-content padding">
                <div class="widget-icon">
                    <i class="icon-home"></i>
                </div>
                <div class="text-box">
                    <p class="maindata">{{ strtoupper(trans('shared/dashboard.total-resi-received')) }}</p>
                    <h2><span class="animate-number" data-value="{{ $totalResiReceived }}" data-duration="1000">0</span></h2>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- End of info box -->
 <div class="row">
    <div class="col-md-12 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2>{{ strtoupper(trans('shared/dashboard.total-resi-per-month')) }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="totalResiPerMonth"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2>{{ strtoupper(trans('shared/dashboard.total-do-per-month')) }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="totalDOPerMonth"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2>{{ strtoupper(trans('shared/dashboard.total-resi')) }} VS {{ strtoupper(trans('shared/dashboard.total-resi-received')) }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="totalResiVsResiReceived"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2>{{ strtoupper(trans('shared/dashboard.total-resi')) }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="totalResiThisMonth"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 portlets">
        <div class="widget">
            <div class="widget-header">
                <h2>{{ strtoupper(trans('shared/dashboard.total-resi-received')) }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="totalResiReceivedThisMonth"></div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(function(){
    Morris.Line({
      element: 'totalResiPerMonth',
      resize: true,
      data: {!! json_encode($dataGraphResiPerMonth['data']) !!},
      xkey: '{!! $dataGraphResiPerMonth["xKey"] !!}',
      ykeys: {!! json_encode($dataGraphResiPerMonth['yKeys']) !!},
      labels: {!! json_encode($dataGraphResiPerMonth['labels']) !!},
      parseTime:false
    });

    Morris.Line({
      element: 'totalDOPerMonth',
      resize: true,
      data: {!! json_encode($dataGraphDOPerMonth['data']) !!},
      xkey: '{!! $dataGraphDOPerMonth["xKey"] !!}',
      ykeys: {!! json_encode($dataGraphDOPerMonth['yKeys']) !!},
      labels: {!! json_encode($dataGraphDOPerMonth['labels']) !!},
      parseTime:false
    });

    Morris.Bar({
      element: 'totalResiVsResiReceived',
      resize: true,
      data: {!! json_encode($dataGraphResiVsReceived['data']) !!},
      xkey: '{!! $dataGraphResiVsReceived["xKey"] !!}',
      ykeys: {!! json_encode($dataGraphResiVsReceived['yKeys']) !!},
      labels: {!! json_encode($dataGraphResiVsReceived['labels']) !!}
    });

    <?php
    $resiExist = false;
    foreach ($dataGraphResiThisMonth['data'] as $data) {
        if (!empty($data['value'])) {
            $resiExist = true;
        }
    }
    ?>

    @if($resiExist)
        Morris.Donut({
          element: 'totalResiThisMonth',
          resize: true,
          data: {!! json_encode($dataGraphResiThisMonth['data']) !!}
        });
    @endif

    <?php
    $resiReceivedExist = false;
    foreach ($dataGraphResiReceivedThisMonth['data'] as $data) {
        if (!empty($data['value'])) {
            $resiReceivedExist = true;
        }
    }
    ?>

    @if($resiReceivedExist)
        Morris.Donut({
          element: 'totalResiReceivedThisMonth',
          resize: true,
          data: {!! json_encode($dataGraphResiReceivedThisMonth['data']) !!}
        });
    @endif
});
</script>
@endsection
