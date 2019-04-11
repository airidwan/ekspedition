@extends('layouts.print')

@section('header')
@parent
<style type="text/css">
.resi-number { font-size: 15px; font-weight: bold; }
.city-coly { font-size: 12px; font-weight: bold; }
</style>
@endsection

@section('content')
<table width="100%" cellspacing="0" cellpadding="2" border="1">
    <tr>
        <td align="center"><img src="images/ar-karyati.png" height="50"/></td>
    </tr>
    <tr>
        <td align="center" class="resi-number">{{ $model->resi_number }}</td>
    </tr>
    <tr>
        <td width="70%" align="center" class="city-coly">{{ $model->route->cityEnd->city_name }}</td>
        <td width="30%" align="center" class="city-coly">{{ $model->totalColy() }} Coly</td>
    </tr>
</table>
@endsection
