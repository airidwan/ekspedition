@extends('layouts.master')

@section('title', trans('sys-admin/menu.dummy'))

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="widget">
			<div class="widget-header">
				<h2><i class="fa fa-laptop"></i> <strong>{{ $title }}</strong> {{ trans('sys-admin/menu.dummy') }}</h2>
				<div class="additional-btn">
					<a href="#" class="widget-maximize"><i class="icon-resize-full-1"></i></a>
					<a href="#" class="widget-toggle"><i class="icon-down-open-2"></i></a>
					<a href="#" class="widget-help"><i class="icon-help-2"></i></a>
				</div>
			</div>
			<div class="widget-content padding">
				<div id="horizontal-form">
					<form  role="form" id="add-form" class="form-horizontal" method="post" action="{{ url('sys-admin/master/dummy/save') }}" enctype="multipart/form-data">
						{{ csrf_field() }}
						<input type="hidden" name="id" value="{{ $model->id }}">
						<div class="col-sm-6 portlets">
							<div class="form-group {{ $errors->has('kolomString') ? 'has-error' : '' }}">
								<label for="kolomString" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-string') }} <span class="required">*</span></label>
								<div class="col-sm-8">
									<input type="text" class="form-control" id="kolomString" name="kolomString" value="{{ count($errors) > 0 ? old('kolomString') : $model->kolom_string }}">
									@if($errors->has('kolomString'))
									<span class="help-block">{{ $errors->first('kolomString') }}</span>
									@endif
								</div>
							</div>
							<div class="form-group">
								<label for="kolomSelect" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-select') }}</label>
								<div class="col-sm-8">
									<select class="form-control" name="kolomSelect" id="kolomSelect">
										<?php $kolomSelect = count($errors) > 0 ? old('kolomSelect') : $model->kolom_select; ?>
										<option value="Kolom Select 1" {{ $kolomSelect == 'Kolom Select 1' ? 'selected' : '' }}>Kolom Select 1</option>
										<option value="Kolom Select 2" {{ $kolomSelect == 'Kolom Select 2' ? 'selected' : '' }}>Kolom Select 2</option>
										<option value="Kolom Select 3" {{ $kolomSelect == 'Kolom Select 3' ? 'selected' : '' }}>Kolom Select 3</option>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="kolomAutocomplete" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-autocomplete') }}</label>
								<div class="col-sm-8">
									<input class="form-control" id="autocomplete" name="kolomAutocomplete" value="{{ count($errors) > 0 ? old('kolomAutocomplete') : $model->kolom_autocomplete }}">
								</div>
							</div>
							<div class="form-group">
								<label for="kolomCurrency" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-currency') }}</label>
								<div class="col-sm-4">
									<input id="kolomCurrency" type="text" class="form-control text-right" name="kolomCurrency" value="{{ count($errors) > 0 ? old('kolomCurrency') : $model->kolom_currency }}">
								</div>
							</div>
							<div class="form-group">
								<label for="kolomTextarea" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-textarea') }}</label>
								<div class="col-sm-8">
									<textarea id="kolomTextarea" name="koloTextarea" class="form-control" rows="4" cols="40">{{ count($errors) > 0 ? old('kolomTextarea') : $model->kolom_textarea }}</textarea>
								</div>
							</div>
						</div>
						<div class="col-sm-6 portlets">
							<div class="form-group {{ $errors->has('kolomDate') ? 'has-error' : '' }}">
								<label for="kolomDate" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-date') }} <span class="required">*</span></label>
								<div class="col-sm-5">
									<?php
									if (count($errors) > 0) {
										$kolomDate = !empty(old('kolomDate')) ? new \DateTime(old('kolomDate')) : null;
									} else {
										$kolomDate = !empty($model->kolom_date) ? new \DateTime($model->kolom_date) : null;
									}
									?>
									<div class="input-group">
										<input type="text" id="kolomDate" name="kolomDate" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy" value="{{ $kolomDate !== null ? $kolomDate->format('d-m-Y') : '' }}">
										<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
									</div>
									@if($errors->has('kolomDate'))
									<span class="help-block">{{ $errors->first('kolomDate') }}</span>
									@endif
								</div>
							</div>
							<div class="form-group">
								<label for="kolomDate" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-lov') }}</label>
								<div class="col-sm-6">
									<div class="input-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="kolomLov" name="kolomLov">
                                            <span class="btn input-group-addon md-trigger" data-modal="modal-lov"><i class="fa fa-search"></i></span>
                                        </div>
								    </div>
							    </div>
						    </div>
							<div class="form-group">
								<label for="isianLOV" class="col-sm-4 control-label">{{ trans('sys-admin/fields.isian-lov') }}</label>
								<div class="col-sm-6">
									<input id="isianLov" type="text" class="form-control" name="isianLov" >
								</div>
							</div>
							<div class="form-group">
								<label for="select2" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-select2') }}</label>
								<div class="col-sm-4">
                                    <select id="select2" name="select2" class="form-control">
                                        <option value="AL">Alabama</option>
                                        <option value="LA">Los Angles</option>
                                        <option value="NY">New York</option>
                                        <option value="WY">Wyoming</option>
                                    </select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-checkbox') }}</label>
								<div class="col-sm-8">
									<label class="checkbox-inline icheckbox">
										<?php $kolomCheckbox = count($errors) > 0 ? old('kolomCheckbox') : $model->kolom_checkbox; ?>
										<input type="checkbox" id="kolomCheckbox" name="kolomCheckbox" value="Checkbox 1" {{ $kolomCheckbox == 'Checkbox 1' ? 'checked' : '' }}> Checkbox 1
									</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-radio') }}</label>
								<div class="col-sm-8">
									<?php $kolomRadio = count($errors) > 0 ? old('kolomRadio') : $model->kolom_radio; ?>
									<label class="radio-inline iradio">
										<input type="radio" name="kolomRadio" id="radio1" value="Radio 1" {{ $kolomRadio == 'Radio 1' ? 'checked' : '' }}> Radio 1
									</label>
									<label class="radio-inline iradio">
										<input type="radio" name="kolomRadio" id="radio2" value="Radio 2" {{ $kolomRadio == 'Radio 2' ? 'checked' : '' }}> Radio 2
									</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-foto') }}</label>
								<div class="col-sm-8">
                                    <input type="file" id="kolomFoto" name="kolomFoto" style="display:none">
                                    <div id="btn-kolomFoto" class="btn well text-center" style="padding: 5px;">
                                        @if(!empty($model->kolom_foto))
                                        <img height="150" src="{{ asset(Config::get('app.paths.kolom-foto-dummy').'/'.$model->kolom_foto) }}"/><span></span>
                                        @else
                                        <img height="150" hidden/><span>{{ trans('shared/common.choose-file') }}</span>
                                        @endif
                                    </div>
								</div>
							</div>
						</div>
						<div class="clearfix"></div>
						<hr>
						<div class="col-sm-12 portlets">
							<div class="data-table-toolbar">
								<div class="row">
									<div class="col-md-12">
										<div class="toolbar-btn-action">
											<a class="btn btn-sm btn-primary md-trigger delete-action" data-modal="modal-add-line">
												<i class="fa fa-plus-circle"></i> {{ trans('shared/common.add') }} {{ trans('sys-admin/fields.line') }}
											</a>
											<a id="clear-lines" href="#" class="btn btn-sm btn-danger"><i class="fa fa-remove"></i> {{ trans('shared/common.clear') }} {{ trans('sys-admin/fields.line') }}</a>
										</div>
									</div>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table table-hover table-striped" id="table-line">
									<thead>
										<tr>
											<th>{{ trans('sys-admin/fields.kolom-string-line') }}</th>
											<th>{{ trans('sys-admin/fields.kolom-select-line') }}</th>
											<th>{{ trans('sys-admin/fields.kolom-currency-line') }}</th>
											<th>{{ trans('sys-admin/fields.kolom-date-line') }}</th>
											<th>{{ trans('shared/common.action') }}</th>
										</tr>
									</thead>
									<tbody>
										@if(count($errors) > 0)
										@if(!empty(old('kolomStringLines')))
										@for($i = 0; $i < count(old('kolomStringLines')); $i++)
										<tr>
											<td>{{ old('kolomStringLines')[$i] }}</td>
											<td>{{ old('kolomSelectLines')[$i] }}</td>
											<td>{{ old('kolomCurrencyLines')[$i] }}</td>
											<td>{{ old('kolomDateLines')[$i] }}</td>
											<td>
												<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
												<input type="hidden" name="kolomStringLines[]" value="{{ old('kolomStringLines')[$i] }}">
												<input type="hidden" name="kolomSelectLines[]" value="{{ old('kolomSelectLines')[$i] }}">
												<input type="hidden" name="kolomCurrencyLines[]" value="{{ old('kolomCurrencyLines')[$i] }}">
												<input type="hidden" name="kolomDateLines[]" value="{{ old('kolomDateLines')[$i] }}">
											</td>
										</tr>
										@endfor
										@endif
										@else
										@foreach($model->lines()->get() as $line)
										<?php $kolomDate = !empty($line->kolom_date) ? new \DateTime($line->kolom_date) : null; ?>
										<tr>
											<td>{{ $line->kolom_string }}</td>
											<td>{{ $line->kolom_select }}</td>
											<td>{{ $line->kolom_currency }}</td>
											<td>{{ $kolomDate !== null ? $kolomDate->format('d-m-Y'): '' }}</td>
											<td>
												<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>
												<input type="hidden" name="kolomStringLines[]" value="{{ $line->kolom_string }}">
												<input type="hidden" name="kolomSelectLines[]" value="{{ $line->kolom_select }}">
												<input type="hidden" name="kolomCurrencyLines[]" value="{{ $line->kolom_currency }}">
												<input type="hidden" name="kolomDateLines[]" value="{{ $kolomDate !== null ? $kolomDate->format('d-m-Y') : '' }}">
											</td>
										</tr>
										@endforeach
										@endif
									</tbody>
								</table>
							</div>
						</div>
						<div class="clearfix"></div>
						<hr>
						<div class="col-sm-offset-3 col-sm-9 portlets">
							<div class="form-group text-right">
								<a href="{{ url('sys-admin/master/dummy') }}" class="btn btn-sm btn-warning">{{ trans('shared/common.cancel') }}</a>
								<button type="submit" class="btn btn-sm btn-primary">{{ trans('shared/common.save') }}</button>
							</div>
                            <br>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('modal')
@parent
<div class="md-modal md-fade-in-scale-up" id="modal-add-line">
	<div class="md-content">
		<h3><strong>{{ trans('shared/common.add') }}</strong> {{ trans('sys-admin/fields.line') }}</h3>
		<div>
			<div class="row">
				<div class="col-sm-12">
					<form role="form" class="form-horizontal">
						<div class="form-group">
							<label for="kolomStringLine" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-string-line') }}</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" id="kolomStringLine" name="kolomStringLine">
								<span class="help-block"></span>
							</div>
						</div>
						<div class="form-group">
							<label for="kolomSelectLine" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-select-line') }}</label>
							<div class="col-sm-8">
								<select class="form-control" name="kolomSelectLine" id="kolomSelectLine">
									<option value="Kolom Select Line 1">Kolom Select Line 1</option>
									<option value="Kolom Select Line 2">Kolom Select Line 2</option>
									<option value="Kolom Select Line 3">Kolom Select Line 3</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="kolomCurrencyLine" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-currency-line') }}</label>
							<div class="col-sm-4">
								<input id="kolomCurrencyLine" type="text" data-mask="999.999.999" class="form-control" name="kolomCurrencyLine">
							</div>
						</div>
						<div class="form-group">
							<label for="kolomDateLine" class="col-sm-4 control-label">{{ trans('sys-admin/fields.kolom-date-line') }}</label>
							<div class="col-sm-4">
								<input type="text" id="kolomDateLine" name="kolomDateLine" class="form-control datepicker-input" data-mask="99-99-9999" placeholder="dd-mm-yyyy">
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-4 col-sm-8">
								<a class="btn btn-sm btn-warning md-close">{{ trans('shared/common.cancel') }}</a>
								<a id="add-line" class="btn btn-sm btn-primary">{{ trans('shared/common.add') }}</a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="md-modal-lg md-fade-in-scale-up" id="modal-lov">
	<div class="md-content">
		<div class="md-close-btn"><a class="md-close"><i class="fa fa-times"></i></a></div>
		<h3>{{ trans('sys-admin/fields.kolom-lov') }}</h3>
		<div>
			<div class="row">
				<div class="col-sm-12">
	                <table id="datatables-lov" class="table table-hover table-striped table-bordered" cellspacing="0" width="100%">
	                    <thead>
	                        <tr>
	                            <th>ID</th>
	                            <th>{{ trans('sys-admin/fields.username') }}</th>
	                            <th>{{ trans('sys-admin/fields.email') }}</th>
	                        </tr>
	                    </thead>
	                    <tbody>
	                        @foreach ($lovs as $lov)
	                        <tr style="cursor: pointer;" class="tr-lov">
	                            <td>{{ $lov->id}} <input type="hidden" value="{{ $lov->id}}" name="id[]"></td>
	                            <td>{{ $lov->name}}<input type="hidden" value="{{ $lov->name}}" name="name[]"></td>
	                            <td>{{ $lov->email}}<input type="hidden" value="{{ $lov->email}}" name="email[]"></td>
	                        </tr>
	                        @endforeach
	                    </tbody>
	                </table>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
@parent
<script type="text/javascript">
	$(document).on('ready', function() {
        $('#kolomSelectLine').select2();
		$("#kolomCurrency").maskMoney({thousands:'.', decimal:',', precision:0});
		$("#select2").select2();

		$('.delete-line').on('click', function() {
			$(this).parent().parent().remove();
		});

		$('#add-line').on('click', function() {
			var kolomStringLine   = $('#kolomStringLine').val();
			var kolomSelectLine   = $('#kolomSelectLine').val();
			var kolomCurrencyLine = $('#kolomCurrencyLine').val();
			var kolomDateLine     = $('#kolomDateLine').val();

			if (kolomStringLine == '') {
				$('#kolomStringLine').parent().parent().addClass('has-error');
				$('#kolomStringLine').parent().find('span').html('Kolom String Line tidak boleh kosong');
				return;
			} else {
				$('#kolomStringLine').parent().parent().removeClass('has-error');
				$('#kolomStringLine').parent().find('span').html('');
			}

			$('#table-line tbody').append(
				'<tr>' +
				'<td >' + kolomStringLine + '</td>' +
				'<td >' + kolomSelectLine + '</td>' +
				'<td >' + kolomCurrencyLine + '</td>' +
				'<td >' + kolomDateLine + '</td>' +
				'<td >' +
				'<a data-toggle="tooltip" class="btn btn-danger btn-xs delete-line" ><i class="fa fa-remove"></i></a>' +
				'<input type="hidden" name="kolomStringLines[]" value="' + kolomStringLine + '">' +
				'<input type="hidden" name="kolomSelectLines[]" value="' + kolomSelectLine + '">' +
				'<input type="hidden" name="kolomCurrencyLines[]" value="' + kolomCurrencyLine + '">' +
				'<input type="hidden" name="kolomDateLines[]" value="' + kolomDateLine + '">' +
				'</td>' +
				'</tr>'
				);

			$('#kolomStringLine').val('');
			$('#kolomSelectLine').val('Kolom Select Line 1');
			$('#kolomCurrencyLine').val('');
			$('#kolomDateLine').val('');

			$('.delete-line').on('click', function() {
				$(this).parent().parent().remove();
			});

			$('#modal-add-line').removeClass("md-show");
		});

		$('#clear-lines').on('click', function() {
			$('#table-line tbody').html('');
		});

		$("#datatables-lov").dataTable(
			{
				"pagelength" : 10,
				"lengthChange": false
			}
		);
		$(".tr-lov").on('click', function(){
			var name = $(this).find('input[name="name[]"]').val();
			var email = $(this).find('input[name="email[]"]').val();
			$('#kolomLov').val(name);
			$('#isianLov').val(email);
			$('#modal-lov').removeClass("md-show");
		});

		$("#autocomplete").autocomplete({
			source: "{{ url('sys-admin/master/dummy/get-header') }}",
			minLength: 1
		})
	    .autocomplete( "instance" )._renderItem = function( ul, item ) {
	      return $( "<li>" )
	        .append("<div>" + item.label + " <b>" + item.value + "</b></div>" )
	        .appendTo( ul );
	    };

        $('#btn-kolomFoto').on('click', function(){
            $('#kolomFoto').click();
        });

        $("#kolomFoto").on('change', function () {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                var $img    = $(this).parent().find('img');
                var $span   = $(this).parent().find('span');
                reader.onload = function (e) {
                    $img.attr('src', e.target.result);
                    $img.show();
                    $span.hide();
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
	});
</script>
@endsection
