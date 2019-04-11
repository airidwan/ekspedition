@extends('layouts.master')

@section('title', 'Master Vendor/Supplier')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="widget">
            <div class="widget-header">
                <h2><i class="fa fa-usd"></i> {{ trans('payable/menu.vendor-supplier') }}</h2>
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
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="nama" class="col-sm-4 control-label">{{ trans('shared/common.nama') }}</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" id="nama" name="nama" value="{{ !empty($filters['nama']) ? $filters['nama'] : '' }}">
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('kategori') ? 'has-error' : '' }}">
                                <label for="kategori" class="col-sm-4 control-label">{{ trans('shared/common.kategori') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control" id="kategori" name="kategori">
                                        <?php $stringKategori = !empty($filters['kategori']) ? $filters['kategori'] : ''; ?>
                                        <option value="">ALL</option>
                                        @foreach($optionKategori as $kategori)
                                        <option value="{{ $kategori->lookup_code }}" {{ $kategori->lookup_code == $stringKategori ? 'selected' : '' }}>{{ $kategori->meaning }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('kategori'))
                                    <span class="help-block">{{ $errors->first('kategori') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 portlets">
                            <div class="form-group">
                                <label for="kota" class="col-sm-4 control-label">{{ trans('shared/common.kota') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control select2" name="kota" id="kota">
                                        <option value="">ALL</option>
                                        @foreach($optionKota as $kota)
                                        <option value="{{ $kota->city_id }}" {{ !empty($filters['kota']) && $filters['kota'] == $kota->city_id ? 'selected' : '' }}>{{ $kota->city_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
                                <label class="col-sm-4 control-label">{{ trans('shared/common.status') }}</label>
                                <div class="col-sm-8">
                                    <label class="checkbox-inline icheckbox">
                                        <?php $status = !empty($filters['status']) || !Session::has('filters') ?>
                                        <input type="checkbox" id="status" name="status" value="Y" {{ $status == 'Y' ? 'checked' : '' }}> {{ trans('shared/common.active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 data-table-toolbar text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-sm btn-info"><i class="fa fa-search"></i> {{ trans('shared/common.filter') }}</button>
                                <a href="{{ URL($url.'/print-excel-index') }}" class="button btn btn-sm btn-success" target="_blank">
                                    <i class="fa fa-file-excel-o"></i> {{ trans('shared/common.print-excel') }}
                                </a>
                                @can('access', [$resource, 'insert'])
                                <a href="{{ URL('payable/master/master-vendor/add') }}" class="btn btn-sm btn-primary"><i class="fa fa-plus-circle"></i> {{ trans('shared/common.add-new') }}</a>
                                @endcan
                            </div>
                        </div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th width="50px">{{ trans('shared/common.num') }}</th>
                                <th>{{ trans('shared/common.kode') }}</th>
                                <th>{{ trans('shared/common.nama') }}</th>
                                <th>{{ trans('shared/common.alamat') }}</th>
                                <th>{{ trans('shared/common.kota') }}</th>
                                <th>{{ trans('shared/common.telepon') }}</th>
                                <th>{{ trans('shared/common.keterangan') }}</th>
                                <th>{{ trans('shared/common.cp') }}</th>
                                <th>{{ trans('shared/common.contact-cp') }}</th>
                                <th>{{ trans('shared/common.kategori') }}</th>
                                <th>{{ trans('shared/common.kode-sub') }}</th>
                                <th>{{ trans('shared/common.active') }}</th>
                                <th style="min-width:70px;">{{ trans('shared/common.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = ($vendors->currentPage() - 1) * $vendors->perPage() + 1; ?>
                            @foreach($vendors as $vendor)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ $vendor->vendor_code }}</td>
                                <td>{{ $vendor->vendor_name }}</td>
                                <td>{{ $vendor->address }}</td>
                                <td>{{ $vendor->city_name }}</td>
                                <td>{{ $vendor->phone_number }}</td>
                                <td>{{ $vendor->description }}</td>
                                <td>{{ $vendor->contact_person }}</td>
                                <td>{{ $vendor->contact_phone }}</td>
                                <td>{{ $vendor->meaning }}</td>
                                <td>{{ $vendor->subaccount_code }}</td>
                                <td class="text-center">
                                    @if($vendor->active == 'Y')
                                    <i class="fa fa-check"></i>
                                    @else
                                    <i class="fa fa-remove"></i>
                                    @endif
                                </td>
                                <td class="text-center" >
                                    @can('access', [$resource, 'update'])
                                    <a href="{{ URL($url . '/edit/' . $vendor->vendor_id) }}" data-toggle="tooltip" class="btn btn-xs btn-warning" data-original-title="{{ trans('shared/common.edit') }}">
                                        <i class="fa fa-pencil"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="data-table-toolbar">
                    {!! $vendors->render() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
$(document).on('ready', function(){
    $('#kota').select2();
});
</script>
@endsection
