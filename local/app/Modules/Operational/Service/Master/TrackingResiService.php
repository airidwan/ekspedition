<?php

namespace App\Modules\Operational\Service\Master;

use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Master\MasterBranch;
use App\Modules\Operational\Model\Master\MasterCity;
use App\Modules\Operational\Model\Transaction\ManifestHeader;
use App\Modules\Operational\Model\Transaction\ManifestLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\CustomerTakingTransact;
use App\Modules\Operational\Model\Transaction\ReceiptOrReturnDeliveryLine;
use App\Service\TimezoneDateConverter;

class TrackingResiService
{
    public static function history(TransactionResiHeader $resi){
        $model = \DB::table('op.history_transaction')
                    ->leftJoin('adm.users', 'history_transaction.user_id', '=', 'users.id')
                    ->where('transaction_id', '=', $resi->resi_header_id)
                    ->where('transaction_number', 'ILIKE', '%'.$resi->resi_number.'%')
                    ->where(function($query){
                        $query->where('transaction_name', '=', 'Approve Resi')
                            ->orWhere('transaction_name', '=', 'Approve Nego Resi')
                            ->orWhere('transaction_name', '=', 'Manifest Shipped')
                            ->orWhere('transaction_name', '=', 'Manifest Arrived')
                            ->orWhere('transaction_name', '=', 'Receipt Manifest')
                            ->orWhere('transaction_name', '=', 'Close Receipt Manifest')
                            ->orWhere('transaction_name', '=', 'Shipped Delivery Order')
                            ->orWhere('transaction_name', '=', 'Receipt DO')
                            ->orWhere('transaction_name', '=', 'Return DO')
                            ->orWhere('transaction_name', '=', 'Letter of Goods Expenditure')
                            ->orWhere('transaction_name', '=', 'LGE Transact');
                    })
                    ->orderBy('transaction_date')
                    ->get();
        return $model;
    }

    public static function simpleHistory(TransactionResiHeader $resi){
        $model = \DB::table('op.history_transaction')
                    ->select(
                        'history_transaction.transaction_number as resi_number',
                        'history_transaction.transaction_name',
                        'history_transaction.transaction_date',
                        'history_transaction.description',
                        'users.full_name as admin_name'
                        )
                    ->leftJoin('adm.users', 'history_transaction.user_id', '=', 'users.id')
                    ->where('transaction_id', '=', $resi->resi_header_id)
                    ->where('transaction_number', 'ILIKE', '%'.$resi->resi_number.'%')
                    ->where(function($query){
                        $query->where('transaction_name', '=', 'Approve Resi')
                            ->orWhere('transaction_name', '=', 'Approve Nego Resi')
                            ->orWhere('transaction_name', '=', 'Manifest Shipped')
                            ->orWhere('transaction_name', '=', 'Manifest Arrived')
                            ->orWhere('transaction_name', '=', 'Receipt Manifest')
                            ->orWhere('transaction_name', '=', 'Close Receipt Manifest')
                            ->orWhere('transaction_name', '=', 'Shipped Delivery Order')
                            ->orWhere('transaction_name', '=', 'Receipt DO')
                            ->orWhere('transaction_name', '=', 'Return DO')
                            ->orWhere('transaction_name', '=', 'Letter of Goods Expenditure')
                            ->orWhere('transaction_name', '=', 'LGE Transact');
                    })
                    ->orderBy('transaction_date')
                    ->get();
        return $model;
    }

    public static function tracking(TransactionResiHeader $resi)
    {
        $resiPosition = [];
        $urutanBranch = self::getUrutanBranch($resi);

        foreach($urutanBranch as $branch) {
            $resiPosition = array_merge($resiPosition, self::getResiPositionArriveManifest($resi, $branch));
            $resiPosition = array_merge($resiPosition, self::getResiPositionResiStock($resi, $branch));
            $resiPosition = array_merge($resiPosition, self::getResiPositionManifestShipped($resi, $branch));
            $resiPosition = array_merge($resiPosition, self::getResiPositionDOShipped($resi, $branch));
            $resiPosition = array_merge($resiPosition, self::getResiPositionLGETransact($resi, $branch));
            $resiPosition = array_merge($resiPosition, self::getResiPositionReceiptDO($resi, $branch));
        }

        return $resiPosition;
    }

    /** URUTAN BRANCH **/
    protected static function getUrutanBranch(TransactionResiHeader $resi)
    {
        $urutanBranch = [];

        // if ($resi->branch !== null) {
        //     $urutanBranch[] = $resi->branch;
        // }

        $urutanBranch = array_merge($urutanBranch, self::getBranchsOnCity($resi->branch->city));
        
        $route = $resi->route;
        if($route === null){
            return $urutanBranch;
        }
        if ($route->details()->count() == 0) {
            $urutanBranch = array_merge($urutanBranch, self::getBranchsOnCity($route->cityEnd));
        } else {
            $cityStart = $route->cityStart;

            while(true) {
                $currentDetail = self::getCurrentDetailBranch($route, $cityStart);
                if ($currentDetail == null) {
                    break;
                }

                $urutanBranch = array_merge($urutanBranch, self::getBranchsOnCity($currentDetail->cityEnd));
                $cityStart = $currentDetail->cityEnd;
            }
        }

        return $urutanBranch;
    }

    protected static function getBranchsOnCity(MasterCity $city)
    {
        $arraybranchs = [];
        $branchs = MasterBranch::where('city_id', '=', $city->city_id)->orderBy('branch_code')->get();
        foreach($branchs as $branch) {
            $arraybranchs[] = $branch;
        }

        return $arraybranchs;
    }

    protected static function getCurrentDetailBranch(MasterRoute $route, MasterCity $city)
    {
        foreach($route->details as $detail) {
            if ($detail->city_start_id == $city->city_id) {
                return $detail;
            }
        }
    }

    /** ARRIVE MANIFEST NOT RECEIVED **/
    protected static function getResiPositionArriveManifest(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];
        $arriveManifestLines = self::getArriveManifestResiOnBranch($resi, $branch);
        foreach($arriveManifestLines as $manifestLine) {
            $manifestLine = ManifestLine::find($manifestLine->manifest_line_id);
            $receiptRemaining = $manifestLine->remainingColyReceipt();
            $date = TimezoneDateConverter::getClientDateTime($manifestLine->header->arrive_date);

            if ($receiptRemaining > 0) {
                $resiPosition[] = [
                    'position'=> 'Arrived at '.$branch->branch_name,
                    'date' => $date->format('d-M-Y H:i:s'),
                    'coly' => $receiptRemaining,
                    'description' => 'Manifest No. '.$manifestLine->header->manifest_number
                ];
            }
        }

        return $resiPosition;
    }

    protected static function getArriveManifestResiOnBranch(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $query = \DB::table('op.trans_manifest_line')
                        ->select('op.trans_manifest_line.*')
                        ->join('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
                        ->where(function($query) {
                            $query->where('trans_manifest_header.status', '=', ManifestHeader::ARRIVED)
                                    ->orWhere('trans_manifest_header.status', '=', ManifestHeader::CLOSED_WARNING);
                        })
                        ->where('trans_manifest_line.resi_header_id', '=', $resi->resi_header_id)
                        ->where('trans_manifest_header.arrive_branch_id', '=', $branch->branch_id)
                        ->orderBy('trans_manifest_header.manifest_header_id')
                        ->orderBy('trans_manifest_line.manifest_line_id');

        return $query->get();
    }

    /** RESI STOCK **/
    protected static function getResiPositionResiStock(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];
        $query = \DB::table('op.mst_stock_resi')
                        ->select('op.mst_stock_resi.*')
                        ->where('mst_stock_resi.resi_header_id', '=', $resi->resi_header_id)
                        ->where('mst_stock_resi.branch_id', '=', $branch->branch_id)
                        ->orderBy('mst_stock_resi.stock_resi_id');

        foreach($query->get() as $stockResi) {
            if ($stockResi->coly > 0) {
                $date = TimezoneDateConverter::getClientDateTime($stockResi->created_date);

                $resiPosition[] = [
                    'position'=> 'Resi Stock '.$branch->branch_name,
                    'date' => $date->format('d-M-Y H:i:s'),
                    'coly' => $stockResi->coly,
                    'description' => ''
                ];
            }
        }

        return $resiPosition;
    }

    /** MANIFEST SHIPPED **/
    protected static function getResiPositionManifestShipped(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];
        $query = \DB::table('op.trans_manifest_line')
                        ->select('op.trans_manifest_line.*')
                        ->join('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
                        ->where('trans_manifest_header.status', '=', ManifestHeader::OTR)
                        ->where('trans_manifest_line.resi_header_id', '=', $resi->resi_header_id)
                        ->where('trans_manifest_header.branch_id', '=', $branch->branch_id)
                        ->orderBy('trans_manifest_header.manifest_header_id')
                        ->orderBy('trans_manifest_line.manifest_line_id');

        foreach($query->get() as $manifestLine) {
            $manifestLine = ManifestLine::find($manifestLine->manifest_line_id);
            $date = TimezoneDateConverter::getClientDateTime($manifestLine->header->shipment_date);

            $position = [
                'position'=> 'On The Way to '.$manifestLine->header->route->cityEnd->city_name,
                'date' => $date->format('d-M-Y H:i:s'),
                'coly' => $manifestLine->coly_sent,
                'description' => 'Manifest Number '.$manifestLine->header->manifest_number
            ];

            if ($manifestLine->header->route->city_start_id == $manifestLine->header->route->city_end_id) {
                $position['position']    = 'Resi Stock '.$branch->branch_name;
                $position['description'] = '';
            }

            $resiPosition[] = $position;
        }

        return $resiPosition;
    }

    /** DELIVERY ORDER SHIPPED **/
    protected static function getResiPositionDOShipped(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];

        $sqlReceiptOrReturn = 'SELECT delivery_order_line_id FROM op.trans_receipt_or_return_delivery_line';
        $query = \DB::table('op.trans_delivery_order_line')
                        ->select('op.trans_delivery_order_line.*')
                        ->join('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                        ->where('trans_delivery_order_header.status', '=', ManifestHeader::OTR)
                        ->where('trans_delivery_order_line.resi_header_id', '=', $resi->resi_header_id)
                        ->where('trans_delivery_order_header.branch_id', '=', $branch->branch_id)
                        ->whereRaw('trans_delivery_order_line.delivery_order_line_id NOT IN (' . $sqlReceiptOrReturn . ')')
                        ->orderBy('trans_delivery_order_header.delivery_order_header_id')
                        ->orderBy('trans_delivery_order_line.delivery_order_line_id');

        foreach($query->get() as $doLine) {
            $doLine = DeliveryOrderLine::find($doLine->delivery_order_line_id);
            $date = TimezoneDateConverter::getClientDateTime($doLine->header->created_date);

            $resiPosition[] = [
                'position'=> 'On The Way to Deliver',
                'date' => $date->format('d-M-Y H:i:s'),
                'coly' => $doLine->total_coly,
                'description' => 'DO Number '.$doLine->header->delivery_order_number
            ];
        }

        return $resiPosition;
    }

    /** LGE TRANSACT **/
    protected static function getResiPositionLGETransact(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];

        $query = \DB::table('op.trans_customer_taking_transact')
                        ->select('op.trans_customer_taking_transact.*')
                        ->join('op.trans_customer_taking', 'trans_customer_taking.customer_taking_id', '=', 'trans_customer_taking_transact.customer_taking_id')
                        ->where('trans_customer_taking.resi_header_id', '=', $resi->resi_header_id)
                        ->where('trans_customer_taking_transact.branch_id', '=', $branch->branch_id)
                        ->orderBy('trans_customer_taking_transact.customer_taking_transact_id')
                        ->orderBy('trans_customer_taking.customer_taking_id');

        foreach($query->get() as $customerTakingTransact) {
            $customerTakingTransact = CustomerTakingTransact::find($customerTakingTransact->customer_taking_transact_id);
            $date = TimezoneDateConverter::getClientDateTime($customerTakingTransact->customer_taking_transact_time);

            $resiPosition[] = [
                'position'=> 'Taken by '.$customerTakingTransact->taker_name.' at '.$branch->branch_name,
                'date' => $date->format('d-M-Y H:i:s'),
                'coly' => $customerTakingTransact->coly_taken,
                'description' => 'LGE Transact Number '.$customerTakingTransact->customer_taking_transact_number
            ];
        }

        return $resiPosition;
    }

    /** RECEIPT DO **/
    protected static function getResiPositionReceiptDO(TransactionResiHeader $resi, MasterBranch $branch)
    {
        $resiPosition = [];

        $query = \DB::table('op.trans_receipt_or_return_delivery_line')
                        ->select('op.trans_receipt_or_return_delivery_line.*')
                        ->join(
                            'op.trans_receipt_or_return_delivery_header',
                            'trans_receipt_or_return_delivery_header.receipt_or_return_delivery_header_id',
                            '=',
                            'trans_receipt_or_return_delivery_line.receipt_or_return_delivery_header_id'
                        )
                        ->join(
                            'op.trans_delivery_order_line',
                            'trans_delivery_order_line.delivery_order_line_id',
                            '=',
                            'trans_receipt_or_return_delivery_line.delivery_order_line_id'
                        )
                        ->where('trans_receipt_or_return_delivery_line.status', '=', ReceiptOrReturnDeliveryLine::RECEIVED)
                        ->where('trans_delivery_order_line.resi_header_id', '=', $resi->resi_header_id)
                        ->where('trans_receipt_or_return_delivery_header.branch_id', '=', $branch->branch_id)
                        ->orderBy('trans_receipt_or_return_delivery_header.receipt_or_return_delivery_header_id')
                        ->orderBy('trans_receipt_or_return_delivery_line.receipt_or_return_delivery_line_id');

        foreach($query->get() as $receiptReturnDOLine) {
            $receiptReturnDOLine = ReceiptOrReturnDeliveryLine::find($receiptReturnDOLine->receipt_or_return_delivery_line_id);
            $date = TimezoneDateConverter::getClientDateTime($receiptReturnDOLine->received_date);

            $resiPosition[] = [
                'position'=> 'Received by '.$receiptReturnDOLine->received_by,
                'date' => $date->format('d-M-Y'),
                'coly' => $receiptReturnDOLine->total_coly,
                'description' => 'Receipt or Return Delivery Number '.$receiptReturnDOLine->header->receipt_or_return_delivery_number
            ];
        }

        return $resiPosition;
    }


}
