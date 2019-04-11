@extends('layouts.print')

<?php 
    use App\Modules\Operational\Model\Master\MasterCity;
    use App\User;
    $driver = $model->driver;
    $truck  = $model->truck;
    $lines  = $model->lines;
    $date = !empty($model->created_date) ? new \DateTime($model->created_date) : new \DateTime();
?>

@section('content')
<table id="filtes" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.document-transfer-number') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->document_transfer_number }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.to-city') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->toCity->city_name }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.description') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->description }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.date') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $date->format('d-m-Y') }}</td>
                </tr>
            </table>
            <br/>
        </td>
        <td width="50%" cellpadding="0" cellspacing="0">
            <table>
                <tr>
                    <td width="38%">{{ trans('operational/fields.driver') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($driver) ? $driver->driver_name : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('operational/fields.truck') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ !empty($truck) ? $truck->police_number : '' }}</td>
                </tr>
                <tr>
                    <td width="38%">{{ trans('shared/common.status') }}</td>
                    <td width="2%">:</td>
                    <td width="60%">{{ $model->status }}</td>
                </tr>
            </table>
            <br/>
        </td>
    </tr>
</table>
<table class="table" cellspacing="0" cellpadding="2" border="1">
    <thead>
        <tr>
            <th width="5%" >{{ trans('shared/common.num') }}</th>
            <th width="15%">{{ trans('operational/fields.resi-number') }}</th>
            <th width="25%">{{ trans('operational/fields.item-name') }}</th>
            <th width="15%">{{ trans('operational/fields.sender-name') }}</th>
            <th width="15%">{{ trans('operational/fields.receiver-name') }}</th>
            <th width="10%">{{ trans('operational/fields.branch') }}</th>
            <th width="15%">{{ trans('shared/common.description') }}</th>
        </tr>
    </thead>
    <tbody>
         <?php $no = 1; ?>
         @foreach($lines as $line)
        <tr>
            <td width="5%"  align="center">{{ $no++ }}</td>
            <td width="15%" >{{ $line->resi->resi_number }}</td>
            <td width="25%" >{{ $line->resi->item_name }}</td>
            <td width="15%" >{{ $line->resi->sender_name }}</td>
            <td width="15%" >{{ $line->resi->receiver_name }}</td>
            <td width="10%" >{{ $line->resi->branch->branch_code }}</td>
            <td width="15%" >{{ $line->resi->description }}</td>
        </tr>
        @endforeach
    </tbody>
</table>


<?php
$city = MasterCity::find(\Session::get('currentBranch')->city_id);
$createdDate  = new \DateTime($model->created_date);
$userCreated  = User::find($model->created_by);
?>
<br>
<br>
<table class="table" cellspacing="0" cellpadding="2">
    <tbody>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ $city->city_name }}, {{ $createdDate->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">{{ trans('shared/common.created-by') }}</td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" height="40px" align="center"></td>
        </tr>
        <tr>
            <td width="75%"></td>
            <td width="25%" align="center">( {{ strtoupper($userCreated->full_name) }} )</td>
        </tr>
    </tbody>
</table>
@endsection
