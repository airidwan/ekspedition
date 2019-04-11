<?php

namespace App\Modules\Operational\Service\Transaction;

use App\Modules\Operational\Model\Transaction\HistoryTransaction;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiLine;
use App\Modules\Operational\Service\Transaction\HistoryTransactionService;

class HistoryResiService
{
    public static function saveHistory($resiId, $transactionName, $description = null)
    {
        $resi = TransactionResiHeader::find($resiId);
        if ($resi === null) {
            return;
        }

        $data = self::getDataResi($resi);

        HistoryTransactionService::saveHistory(HistoryTransaction::RESI, $resiId, $resi->resi_number, $transactionName, $description, $data);
    }

    protected static function getDataResi(TransactionResiHeader $resi)
    {
        $resiStd = new \StdClass();
        $resiStd->resi_number = $resi->resi_number;
        $resiStd->customer_sender = !empty($resi->customer) ? $resi->customer->customer_name : '';
        $resiStd->sender_name = $resi->sender_name;
        $resiStd->sender_address = $resi->sender_address;
        $resiStd->sender_phone = $resi->sender_phone;
        $resiStd->customer_receiver = !empty($resi->customerReceiver) ? $resi->customerReceiver->customer_name : '';
        $resiStd->receiver_name = $resi->receiver_name;
        $resiStd->receiver_address = $resi->receiver_address;
        $resiStd->receiver_phone = $resi->receiver_phone;
        $resiStd->route_code = !empty($resi->route) ? $resi->route->route_code : '';
        $resiStd->item_name = $resi->item_name;
        $resiStd->total_weight = number_format($resi->totalWeight(), 2);
        $resiStd->total_volume = number_format($resi->totalVolume(), 6);
        $resiStd->type = $resi->type;
        $resiStd->payment = $resi->payment;
        $resiStd->total_amount = number_format($resi->totalAmount());
        $resiStd->discount = number_format($resi->discount);
        $resiStd->total = number_format($resi->total());
        $resiStd->description = $resi->description;
        $resiStd->pickup_request_number = !empty($resi->pickupRequest) ? $resi->pickupRequest->pickup_request_number : '';
        $resiStd->status = $resi->status;
        $resiStd->branch_code = !empty($resi->branch) ? $resi->branch->branch_code : '';

        $resiStd->line_details = self::getLineDetailResi($resi);
        $resiStd->line_units   = self::getLineUnitResi($resi);

        return $resiStd;
    }

    protected static function getLineDetailResi(TransactionResiHeader $resi)
    {
        $lineDetails = [];
        foreach ($resi->lineDetail as $line) {
            $lineDetail = new \StdClass();
            $lineDetail->item_name = $line->item_name;
            $lineDetail->coly = number_format($line->coly);
            $lineDetail->qty_weight = number_format($line->qty_weight);
            $lineDetail->weight_unit = number_format($line->weight_unit, 2);
            $lineDetail->weight = number_format($line->weight, 2);
            $lineDetail->price_weight = number_format($line->price_weight);
            $lineDetail->volume = number_format($line->totalVolume());
            $lineDetail->price_volume = number_format($line->totalPriceVolume());
            $lineDetail->price = number_format($line->total_price);
            $lineDetail->volumes = self::getDetailVolumeResi($line);

            $lineDetails[] = $lineDetail;
        }

        return $lineDetails;
    }

    protected static function getDetailVolumeResi(TransactionResiLine $line)
    {
        $volumes = [];
        foreach ($line->lineVolume as $lineVolume) {
            $volume = new \StdClass;
            $volume->qty_volume = number_format($lineVolume->qty_volume);
            $volume->long = number_format($lineVolume->dimension_long);
            $volume->width = number_format($lineVolume->dimension_width);
            $volume->height = number_format($lineVolume->dimension_height);
            $volume->volume = number_format($lineVolume->volume, 6);
            $volume->total_volume = number_format($lineVolume->total_volume, 6);

            $volumes[] = $volume;
        }

        return $volumes;
    }

    protected static function getLineUnitResi(TransactionResiHeader $resi)
    {
        $lineDetails = [];
        foreach ($resi->lineUnit as $line) {
            $lineDetail = new \StdClass();
            $lineDetail->item_name = $line->item_name;
            $lineDetail->coly = number_format($line->coly);
            $lineDetail->price = number_format($line->total_price);

            $lineDetails[] = $lineDetail;
        }

        return $lineDetails;
    }
}
