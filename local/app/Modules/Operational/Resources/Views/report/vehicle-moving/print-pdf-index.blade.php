@extends('layouts.print')
<?php use App\Modules\Operational\Model\Transaction\ManifestHeader; ?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="75%" cellpadding="0" cellspacing="0">
            <table>
                @if (!empty($filters['truckCode']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.truck-code') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['truckCode'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['policeNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.nopol-truck') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['policeNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['manifestNumber']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.manifest-number') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['manifestNumber'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['driver']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.driver') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['driver'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['driverAssistant']))
                    <tr>
                        <td width="18%">{{ trans('operational/fields.driver-assistant') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['driverAssistant'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['truckCategory']))
                    <?php $truck = \DB::table('adm.mst_lookup_values')->select('meaning')->where('lookup_code', '=', $filters['truckCategory'])->first(); ?>
                    <tr>
                        <td width="18%">{{ trans('operational/fields.truck-category') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $truck->meaning }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateFrom']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-from') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateFrom'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['dateTo']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.date-to') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['dateTo'] }}</td>
                    </tr>
                @endif
                @if (!empty($filters['status']))
                    <tr>
                        <td width="18%">{{ trans('shared/common.status') }}</td>
                        <td width="2%">:</td>
                        <td width="80%">{{ $filters['status'] }}</td>
                    </tr>
                @endif
            </table>
            <br/>
        </td>
        <td width="25%" cellpadding="0" cellspacing="0">
            <table>
                <?php $date = new \DateTime(); ?>
                <tr>
                    <td width="25%">{{ trans('shared/common.date') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ $date->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.user') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Auth::user()->full_name }}</td>
                </tr>
                <tr>
                    <td width="25%">{{ trans('shared/common.branch') }}</td>
                    <td width="5%">:</td>
                    <td width="70%">{{ \Session::get('currentBranch')->branch_name }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" rowspan="2">{{ trans('shared/common.num') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.police-number') }}</th>
            <th width="15%">{{ trans('operational/fields.truck-code') }}</th>
            <th width="10%" rowspan="2">{{ trans('operational/fields.moving-type') }}</th>
            <th width="10%">{{ trans('shared/common.date') }}</th>
            <th width="15%" rowspan="2">{{ trans('operational/fields.manifest-number') }}</th>
            <th width="20%">{{ trans('operational/fields.driver') }}</th>
            <th width="15%" rowspan="2">{{ trans('shared/common.status') }}</th>
        </tr>
        <tr>
            <th >{{ trans('shared/common.category') }}</th>
            <th >{{ trans('shared/common.time') }}</th>
            <th >{{ trans('operational/fields.assistant') }}</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; ?>
        @foreach($models as $model)
        <?php
            $date = !empty($model->obook_time) ? \App\Service\TimezoneDateConverter::getClientDateTime($model->obook_time) : null;
        ?>
        <tr>
            <td width="5%" rowspan="2" align="center">{{ $no++ }}</td>
            <td width="10%" rowspan="2">{{ $model->police_number }}</td>
            <td width="15%" >{{ $model->truck_code }}</td>
            <td width="10%" rowspan="2">{{ $model->in_out }}</td>
            <td width="10%" >{{ $date->format('d-m-Y') }}</td>
            <td width="15%" rowspan="2">{{ $model->manifest_number }}</td>
            <td width="20%" >{{ $model->driver_name }}</td>
            <td width="15%" rowspan="2">{{ $model->status }}</td>
        </tr>
        <tr>
            <?php $truck = \DB::table('adm.mst_lookup_values')->select('meaning')->where('lookup_code', '=', $model->category)->first(); ?>
            <td>{{ !empty($truck) ? $truck->meaning : '' }}</td>
            <td>{{ $date->format('H:i') }}</td>
            <td>{{ $model->assistant_name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
