@extends('layouts.web')

@section('title', trans('operational/fields.calculate-price'))

@section('header')
@parent
<style type="text/css">
.full-content-center {
    max-width: none;
    margin-top: 10px;
    text-align: inherit;
}
.price {
    font-weight: bold;
    font-size: 48px;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> {{ trans('operational/fields.calculate-price') }}</h2>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <div class="col-sm-6 portlets">
                            <?php
                            $cityStartId  = '';
                            if (!empty(old('cityStartId'))) {
                                $cityStartId = old('cityStartId');
                            } elseif (!empty($filters['cityStartId'])) {
                                $cityStartId = $filters['cityStartId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('cityStartId') ? 'has-error' : '' }}">
                                <label for="cityStartId" class="col-sm-4 control-label">{{ trans('operational/fields.kota-asal') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="cityStartId" id="cityStartId">
                                        <option value="" >Select City</option>
                                        @foreach($optionCityStart as $city)
                                        <option value="{{ $city->city_id }}" {{ $cityStartId == $city->city_id ? 'selected' : '' }}>{{ $city->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('cityStartId'))
                                        <span class="help-block">{{ $errors->first('cityStartId') }}</span>
                                    @endif
                                </div>
                            </div>

                            <?php
                            $cityEndId  = '';
                            if (!empty(old('cityEndId'))) {
                                $cityEndId = old('cityEndId');
                            } elseif (!empty($filters['cityEndId'])) {
                                $cityEndId = $filters['cityEndId'];
                            }
                            ?>
                            <div class="form-group {{ $errors->has('cityEndId') ? 'has-error' : '' }}">
                                <label for="cityEndId" class="col-sm-4 control-label">{{ trans('operational/fields.kota-tujuan') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="cityEndId" id="cityEndId">
                                        <option value="" >Select City</option>
                                        @foreach($optionCityEnd as $city)
                                        <option value="{{ $city->city_id }}" {{ $cityEndId == $city->city_id ? 'selected' : '' }}>{{ $city->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('cityEndId'))
                                        <span class="help-block">{{ $errors->first('cityEndId') }}</span>
                                    @endif
                                </div>
                            </div>

                            <?php
                            $weight  = '';
                            if (!empty(old('weight'))) {
                                $weight = old('weight');
                            } elseif (!empty($filters['weight'])) {
                                $weight = $filters['weight'];
                            }
                            ?>
                            <div class="form-group">
                                <label for="weight" class="col-sm-4 control-label">{{ trans('operational/fields.weight') }} (kg)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal" id="weight" name="weight" value="{{ $weight }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">

                            <?php
                            $long  = '';
                            if (!empty(old('long'))) {
                                $long = str_replace(',', '',old('long'));
                            } elseif (!empty($filters['long'])) {
                                $long = str_replace(',', '', $filters['long']);
                            }
                            ?>
                            <div class="form-group">
                                <label for="long" class="col-sm-4 control-label">{{ trans('operational/fields.dimension-long') }} (cm)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="long" name="long" value="{{ $long }}">
                                </div>
                            </div>

                            <?php
                            $width  = '';
                            if (!empty(old('width'))) {
                                $width = str_replace(',', '',old('width'));
                            } elseif (!empty($filters['width'])) {
                                $width = str_replace(',', '', $filters['width']);
                            }
                            ?>
                            <div class="form-group">
                                <label for="width" class="col-sm-4 control-label">{{ trans('operational/fields.dimension-width') }} (cm)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="width" name="width" value="{{ $width }}">
                                </div>
                            </div>

                            <?php
                            $height  = '';
                            if (!empty(old('height'))) {
                                $height = str_replace(',', '',old('height'));
                            } elseif (!empty($filters['height'])) {
                                $height = str_replace(',', '', $filters['height']);
                            }
                            ?>
                            <div class="form-group">
                                <label for="height" class="col-sm-4 control-label">{{ trans('operational/fields.dimension-height') }} (cm)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control currency" id="height" name="height" value="{{ $height }}">
                                </div>
                            </div>

                            <?php
                            $volume  = '';
                            if (!empty(old('volume'))) {
                                $volume = str_replace(',', '',old('volume'));
                            } elseif (!empty($filters['volume'])) {
                                $volume = str_replace(',', '', $filters['volume']);
                            }
                            ?>
                            <div class="form-group">
                                <label for="volume" class="col-sm-4 control-label">{{ trans('operational/fields.volume') }} (m3)</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control decimal6" id="volume" name="volume" value="{{ $volume }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-money"></i> {{ trans('shared/common.calculate') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>

                @if (!empty($price))
                <h3>Calculate Price Result</h3>
                    <div class="text-center"><span class="price">Rp {{ number_format($price) }}</span></div>
                <hr>
                <h3>Description</h3>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{{ trans('operational/fields.route') }}</label>
                    <label for="weight" class="col-sm-9 control-label">: {{ $route->cityStart->city_name.' - '.$route->cityEnd->city_name }}</label>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{{ trans('operational/fields.price-per-kg') }}</label>
                    <label for="weight" class="col-sm-9 control-label">: Rp. {{ number_format($route->rate_kg) }}</label>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{!! trans('operational/fields.price-per-m3') !!}</label>
                    <label for="weight" class="col-sm-9 control-label">: Rp. {{ number_format($route->rate_m3) }}</label>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{{ trans('operational/fields.minimum-weight') }}</label>
                    <label for="weight" class="col-sm-9 control-label">: {{ $route->minimum_weight }} Kg</label>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{{ trans('operational/fields.minimum-price') }}</label>
                    <label for="weight" class="col-sm-9 control-label">: Rp. {{ number_format($route->minimum_rates) }}</label>
                </div>
                <div class="form-group">
                    <label for="weight" class="col-sm-3 control-label">{{ trans('operational/fields.delivery-estimation') }} </label>
                    <label for="weight" class="col-sm-9 control-label">: {{ $route->delivery_estimation }}</label>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function() {
    $('#long').on('keyup', calculateVolume);
    $('#width').on('keyup', calculateVolume);
    $('#height').on('keyup', calculateVolume);
});

var calculateVolume = function() {
    var long = currencyToInt($('#long').val());
    var width = currencyToInt($('#width').val());
    var height = currencyToInt($('#height').val());
    var convertM3 = 1000000;
    var volume = long * width * height / convertM3;

    $('#volume').val(volume).autoNumeric('update', {mDec: 6});
};
</script>
@endsection