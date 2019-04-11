<?php 
    $org = \DB::table('op.mst_organization')->first();
?>
<br/><br/>
<table id="header" width="100%">
    <tr>
        <td width="22%" rowspan="3">
            <img src="images/ar-karyati.png" height="30">
        </td>
        <td colspan="2" width="78%" style="font-size: 10px; font-weight: bold;">{{ $org->org_name }}</td>
    </tr>
    <tr>
        <td colspan="2" width="78%" style="font-size: 8px;">{{ trans('shared/common.address') }}: {{ $org->address }}</td>
    </tr>
    <tr>
        <td width="38%" style="font-size: 8px;">{{ trans('shared/common.phone') }}: {{ $org->phone_number }}</td>
        <td width="40%" align="right" style="font-weight: bold;">{{ strtoupper($title) }}</td>
    </tr>
</table>
<hr/>
