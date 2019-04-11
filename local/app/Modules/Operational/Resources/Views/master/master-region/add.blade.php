@extends('layouts.master')

@section('title', trans('operational/menu.region'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-truck"></i> <strong>{{ $title }}</strong> {{ trans('operational/menu.region') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url($url . '/save') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $model->region_id }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('kode') ? 'has-error' : '' }}">
                                <label for="kode" class="col-sm-4 control-label">{{ trans('shared/common.kode') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="kode" name="kode" value="{{ count($errors) > 0 ? old('kode') : $model->region_code }}">
                                    @if($errors->has('kode'))
                                    <span class="help-block">{{ $errors->first('kode') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
                                <label for="nama" class="col-sm-4 control-label">{{ trans('shared/common.nama') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nama" name="nama" value="{{ count($errors) > 0 ? old('nama') : $model->region_name }}">
                                    @if($errors->has('nama'))
                                    <span class="help-block">{{ $errors->first('nama') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                    <label class="checkbox-inline icheckbox">
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> Aktif
                                    </label>
                                    @if($errors->has('status'))
                                    <span class="help-block">{{ $errors->first('status') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group {{ $errors->has('detailKota') ? 'has-error' : '' }}">
                                <label for="nama" class="col-sm-4 control-label">{{ trans('operational/fields.detail-kota') }} <span class="required">*</span></label>
                                <div class="col-sm-8">
                                    <select class="form-control" name="detailKota[]" id="detailKota" multiple="multiple">
                                        <?php
                                        if (count($errors) > 0) {
                                            $detailKotaId = old('detailKota', []);
                                        } else {
                                            $detailKotaId = [];
                                            $detailKota   = DB::table('op.dt_region_city')->where('region_id', '=', $model->region_id)->get();
                                            foreach ($detailKota as $dtlKota) {
                                                $detailKotaId[] = $dtlKota->city_id;
                                            }
                                        }
                                        ?>
                                        @foreach($optionKota as $kota)
                                        <option value="{{ $kota->city_id }}" {{ in_array($kota->city_id, $detailKotaId) ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('detailKota'))
                                    <span class="help-block">{{ $errors->first('detailKota') }}</span>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-12  data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL($url) }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function(){
    $("#detailKota").select2();
});
</script>
@endsection
