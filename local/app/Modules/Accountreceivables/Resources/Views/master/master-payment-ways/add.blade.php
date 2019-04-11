@extends('layouts.master')

@section('title', trans('accountreceivables/menu.payment-ways'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-credit-card"></i> {{ trans('accountreceivables/menu.payment-ways') }}</h2>
                <div class="additional-btn">
                    <a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
                    <a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
                    <a href="#" class="widget-help"><i class="icon-help-2"></i></a>
                </div>
            </div>
            <div class="widget-content padding">
                <div id="horizontal-form">
                    <form  role="form" id="add-form" class="form-horizontal" method="post" action="">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ !empty($params['id']) ? $params['id'] : '' }}">
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="nama-piutang" class="col-sm-4 control-label">{{ trans('shared/common.nama') }} </label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nama-piutang" name="nama-piutang" value="{{ !empty($params['nama-piutang']) ? $params['nama-piutang'] : '' }}" required>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('kas-bank') ? 'has-error' : '' }}">
                                        <label for="kolomString" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.kas-bank') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="kas-bank" name="kas-bank">
                                                <option value="Surabaya">Kas Kecil</option>
                                            </select>
                                            @if($errors->has('kas-bank'))
                                            <span class="help-block">{{ $errors->first('kas-bank') }}</span>
                                            @endif
                                        </div>
                                    </div>
                            <div class="form-group">
                                <label for="akun" class="col-sm-4 control-label">{{ trans('accountreceivables/fields.rekening-bank') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="akun" name="akun" value="{{ !empty($params['akun']) ? $params['akun'] : '' }}" required>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">Status</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <input type="checkbox" id="status" name="status" value="status" checked> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <a href="{{ URL('accountreceivables/master/master-payment-ways') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-save"></i> {{ trans('shared/common.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
