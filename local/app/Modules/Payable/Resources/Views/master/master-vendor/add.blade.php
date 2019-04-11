@extends('layouts.master')

@section('title', 'Master Vendor/Supplier')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> <strong>{{ $title }}</strong> {{ trans('payable/menu.vendor-supplier') }}</h2>
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
                        <input type="hidden" name="id" value="{{ !empty($model->vendor_id) ? $model->vendor_id : '' }}">
                        <ul id="demo1" class="nav nav-tabs">
                            <li class="active">
                                <a href="#tabVendor" data-toggle="tab">{{ trans('payable/menu.vendor-supplier') }} <span class="label label-success"></span></a>
                            </li>
                            <li class="">
                                <a href="#tabAktifasi" data-toggle="tab">{{ trans('shared/common.activation') }} <span class="badge badge-primary"></span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade active in" id="tabVendor">
                                <div class="col-sm-5 portlets">
                                    <div class="form-group {{ $errors->has('kode') ? 'has-error' : '' }}">
                                        <label for="kode" class="col-sm-4 control-label">{{ trans('shared/common.kode') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="kode" name="kode"  value="{{ !empty($model->vendor_code) ? $model->vendor_code : '' }}" disabled>
                                            @if($errors->has('kode'))
                                            <span class="help-block">{{ $errors->first('kode') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('nama') ? 'has-error' : '' }}">
                                        <label for="nama" class="col-sm-4 control-label">{{ trans('shared/common.nama') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="nama" name="nama" value="{{ count($errors) > 0 ? old('nama') : $model->vendor_name }}">
                                            @if($errors->has('nama'))
                                            <span class="help-block">{{ $errors->first('nama') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('alamat') ? 'has-error' : '' }}">
                                        <label for="alamat" class="col-sm-4 control-label">{{ trans('shared/common.alamat') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="alamat" name="alamat" rows="4">{{ count($errors) > 0 ? old('alamat') : $model->address }}</textarea>
                                            @if($errors->has('alamat'))
                                            <span class="help-block">{{ $errors->first('alamat') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('kota') ? 'has-error' : '' }}">
                                        <label for="kota" class="col-sm-4 control-label">{{ trans('shared/common.kota') }} <span class="required">*</span></label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="kota" name="kota">
                                                <?php $kotaId = count($errors) > 0 ? old('kota') : $model->city_id; ?>
                                                <option value="">{{ trans('shared/common.select-city') }}</option>
                                                @foreach($optionKota as $kota)
                                                <option value="{{ $kota->city_id }}" {{ $kota->city_id == $kotaId ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('kota'))
                                            <span class="help-block">{{ $errors->first('kota') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('telp') ? 'has-error' : '' }}">
                                        <label for="telp" class="col-sm-4 control-label">{{ trans('shared/common.telepon') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="telp" name="telp" value="{{ count($errors) > 0 ? old('telp') : $model->phone_number }}">
                                            @if($errors->has('telp'))
                                            <span class="help-block">{{ $errors->first('telp') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                        <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                        <div class="col-sm-4">
                                            <label class="checkbox-inline icheckbox">
                                                <?php $status = count($errors) > 0 ? old('status') : $model->active; ?>
                                                <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 portlets">
                                    <div class="form-group {{ $errors->has('contactPerson') ? 'has-error' : '' }}">
                                        <label for="contactPerson" class="col-sm-4 control-label">{{ trans('shared/common.cp') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="contactPerson" name="contactPerson" value="{{ count($errors) > 0 ? old('contactPerson') : $model->contact_person }}">
                                            @if($errors->has('contactPerson'))
                                            <span class="help-block">{{ $errors->first('contactPerson') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('telpContactPerson') ? 'has-error' : '' }}">
                                        <label for="telpContactPerson" class="col-sm-4 control-label">{{ trans('shared/common.contact-cp') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="telpContactPerson" name="telpContactPerson" value="{{ count($errors) > 0 ? old('telpContactPerson') : $model->contact_phone }}">
                                            @if($errors->has('telpContactPerson'))
                                            <span class="help-block">{{ $errors->first('telpContactPerson') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('kategori') ? 'has-error' : '' }}">
                                        <label for="kategori" class="col-sm-4 control-label">{{ trans('shared/common.kategori') }}</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="kategori" name="kategori">
                                                <?php $stringKategori = count($errors) > 0 ? old('kategori') : $model->category; ?>
                                                @foreach($optionKategori as $kategori)
                                                <option value="{{ $kategori->lookup_code }}" {{ $kategori->lookup_code == $stringKategori ? 'selected' : '' }}>{{ $kategori->meaning }}</option>
                                                @endforeach
                                            </select>
                                            @if($errors->has('kategori'))
                                            <span class="help-block">{{ $errors->first('kategori') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group {{ $errors->has('keterangan') ? 'has-error' : '' }}">
                                        <label for="keterangan" class="col-sm-4     control-label">{{ trans('shared/common.keterangan') }}</label>
                                        <div class="col-sm-8">
                                            <textarea type="text" class="form-control" id="keterangan" name="keterangan" rows="4">{{ count($errors) > 0 ? old('keterangan') : $model->description }}</textarea>
                                            @if($errors->has('keterangan'))
                                            <span class="help-block">{{ $errors->first('keterangan') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="hidden" class="form-control" id="kodeSub" name="kodeSub" value="{{ count($errors) > 0 ? old('kodeSub') : $model->subaccount_code }}">
                                    <div class="form-group {{ $errors->has('viewKodeSub') ? 'has-error' : '' }}">
                                        <label for="kolomString" class="col-sm-4 control-label">{{ trans('shared/common.kode-sub') }}</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" id="viewKodeSub" name="viewKodeSub" value="{{ count($errors) > 0 ? old('kodeSub') : $model->subaccount_code }}" disabled>
                                            @if($errors->has('viewKodeSub'))
                                            <span class="help-block">{{ $errors->first('viewKodeSub') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="tabAktifasi">
                                <div class="table-responsive">
                                    <div class="col-sm-12 portlets">
                                        <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('shared/common.cabang') }}</th>
                                                    <th><input name="all-cabang" id="all-cabang" type="checkbox" ></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $detailCabangId = [];
                                                if (count($errors) > 0) {
                                                    $detailCabangId = old('detailCabang',[]);
                                                }else{
                                                    $detailCabang   = DB::table('ap.dt_vendor_branch')->where('vendor_id', '=', $model->vendor_id)->get();
                                                    foreach ($detailCabang as $dtlCabang) {
                                                        $detailCabangId[] = $dtlCabang->branch_id;
                                                    }
                                                }
                                                ?>
                                                @foreach($daftarCabang as $cabang)
                                                <tr>
                                                    <td>{{ $cabang->branch_name }} </td>
                                                    <td class="text-center">
                                                        <input name="detailCabang[]" value="{{ $cabang->branch_id }}" type="checkbox" class="rows-check" {{ in_array($cabang->branch_id, $detailCabangId) ? 'checked' : '' }}  >
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                            <a href="{{ URL('payable/master/master-vendor') }}" class="btn btn-sm btn-warning"><i class="fa fa-reply"></i> {{ trans('shared/common.cancel') }}</a>
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

@section('script')
@parent()
<script type="text/javascript">
    $(document).on('ready', function(){
        $('#kota').select2();
        $('#all-cabang').on('ifChanged', function(){
            var $inputs = $(this).parent().parent().parent().parent().parent().find('input[type="checkbox"]');
            if (!$(this).parent('[class*="icheckbox"]').hasClass("checked")) {
                $inputs.iCheck('check');
            } else {
                $inputs.iCheck('uncheck');
            }
        });
    });
</script>
@endsection