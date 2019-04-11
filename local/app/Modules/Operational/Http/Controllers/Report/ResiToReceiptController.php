<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;

class ResiToReceiptController extends Controller
{
    const RESOURCE  = 'Operational\Report\ResiToReceipt';
    const URL       = 'operational/report/resi-to-receipt';
    const URL_PRINT = 'operational/report/resi-to-receipt/print-excel';

    const RESI_DATE         = 'Resi Date';
    const PICKUP_DATE       = 'Pickup Date';
    const MANIFEST_DATE     = 'Manifest Date';
    const DO_DATE           = 'DO Date';
    const LGE_DATE          = 'LGE Date';
    const LGE_TRANSACT_DATE = 'LGE Transact Date';
    const RECEIPT_DATE      = 'Receipt Date';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        return view('operational::report.resi-to-receipt.index', [
            'optionPeriode' => $this->getOptionsPeriod(),
            'resource' => self::RESOURCE,
            'urlPrint' => self::URL_PRINT,
        ]);
    }

    public function printExcel(Request $request)
    {
        $data = $this->getData($request);

        \Excel::create(trans('operational/menu.resi-to-receipt'), function($excel) use ($data) {
            $excel->sheet('Sheet1', function($sheet) use ($data) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.resi-to-receipt'));
                });

                // HEADER
                $headerColumns = [
                    trans('operational/fields.resi-number'),
                    trans('shared/common.customer'),
                    trans('operational/fields.sender'),
                    trans('shared/common.address'),
                    trans('shared/common.customer'),
                    trans('operational/fields.sender'),
                    trans('shared/common.address'),
                    trans('operational/fields.route'),
                    trans('operational/fields.item-name'),
                    trans('operational/fields.total-coly'),
                    trans('operational/fields.weight'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.volume'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.unit'),
                    trans('operational/fields.total-price'),
                    trans('operational/fields.total-amount'),
                    trans('accountreceivables/fields.discount'),
                    trans('accountreceivables/fields.total'),
                    trans('operational/fields.pickup-number'),
                    trans('operational/fields.pickup-date'),
                    trans('operational/fields.manifest-number'),
                    trans('operational/fields.manifest-date'),
                    trans('operational/fields.coly-sent'),
                    trans('operational/fields.receipt-manifest-date'),
                    trans('operational/fields.coly-receipt'),
                    trans('operational/fields.customer-taking-number'),
                    trans('operational/fields.customer-taking-date'),
                    trans('operational/fields.customer-taking-transact-number'),
                    trans('operational/fields.customer-taking-transact-date'),
                    trans('operational/fields.coly-taken'),
                    trans('operational/fields.do-number'),
                    trans('operational/fields.do-date'),
                    trans('operational/fields.partner'),
                    trans('operational/fields.coly-sent'),
                    trans('operational/fields.receipt-or-return-number'),
                    trans('operational/fields.received-by'),
                    trans('operational/fields.received-date'),
                    trans('operational/fields.coly-received'),
                    trans('operational/fields.returned-date'),
                    trans('operational/fields.coly-returned'),
                ];

                $sheet->cells('A3:AO3', function($cells) {
                    $cells->setBackground('#dddddd');
                    $cells->setFont(['size' => '12', 'bold' => true]);
                });

                $sheet->row(3, $headerColumns);

                $currentRow = 4;
                foreach($data as $item) {
                    $sheet->row($currentRow, array_values($item));
                    $currentRow++;
                }
            });

        })->export('xlsx');
    }

    protected function getData(Request $request)
    {
        $query = \DB::table('op.trans_resi_header')
                        ->select('op.trans_resi_header.*')
                        ->leftJoin('mrk.trans_pickup_request', 'trans_pickup_request.pickup_request_id', '=', 'trans_resi_header.pickup_request_id')
                        ->leftJoin('op.trans_pickup_form_line', 'trans_pickup_form_line.pickup_request_id', '=', 'trans_pickup_request.pickup_request_id')
                        ->leftJoin('op.trans_pickup_form_header', 'trans_pickup_form_header.pickup_form_header_id', '=', 'trans_pickup_form_line.pickup_form_header_id')
                        ->leftJoin('op.trans_manifest_line', 'trans_manifest_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.trans_manifest_header', 'trans_manifest_header.manifest_header_id', '=', 'trans_manifest_line.manifest_header_id')
                        ->leftJoin('op.trans_delivery_order_line', 'trans_delivery_order_line.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                        ->leftJoin('op.trans_customer_taking', 'trans_customer_taking.resi_header_id', '=', 'trans_resi_header.resi_header_id')
                        ->leftJoin('op.trans_customer_taking_transact', 'trans_customer_taking_transact.customer_taking_id', '=', 'trans_customer_taking.customer_taking_id')
                        ->leftJoin('op.trans_receipt_or_return_delivery_line', 'trans_receipt_or_return_delivery_line.delivery_order_line_id', '=', 'trans_delivery_order_line.delivery_order_line_id')
                        ->leftJoin('op.trans_receipt_or_return_delivery_header', 'trans_receipt_or_return_delivery_header.receipt_or_return_delivery_header_id', '=', 'trans_receipt_or_return_delivery_line.receipt_or_return_delivery_header_id')
                        ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                        ->distinct()
                        ->orderBy('trans_resi_header.created_date', 'desc')
                        ->orderBy('trans_resi_header.resi_number', 'desc')
                        ->orderBy('trans_resi_header.resi_header_id', 'desc');
 
        if (!empty($request->get('resiNumber'))){
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$request->get('resiNumber').'%');
        }

        if (!empty($request->get('pickupNumber'))){
            $query->where('trans_pickup_request.pickup_request_number', 'ilike', '%'.$request->get('pickupNumber').'%');
        }

        if (!empty($request->get('manifestNumber'))){
            $query->where('trans_manifest_header.manifest_number', 'ilike', '%'.$request->get('manifestNumber').'%');
        }

        if (!empty($request->get('doNumber'))){
            $query->where('trans_delivery_order_header.delivery_order_number', 'ilike', '%'.$request->get('doNumber').'%');
        }

        if (!empty($request->get('lgeNumber'))){
            $query->where('trans_customer_taking.customer_taking_number', 'ilike', '%'.$request->get('lgeNumber').'%');
        }

        if (!empty($request->get('lgeTransactNumber'))){
            $query->where('trans_customer_taking_transact.customer_taking_transact_number', 'ilike', '%'.$request->get('lgeTransactNumber').'%');
        }

        if (!empty($request->get('receiptNumber'))){
            $query->where('trans_receipt_or_return_delivery_header.receipt_or_return_delivery_number', 'ilike', '%'.$request->get('receiptNumber').'%');
        }

        if (!empty($request->get('dateFrom'))){
            $dateFrom = new \DateTime($request->get('dateFrom'));
            if ($request->get('periode') == self::RESI_DATE) {
                $query->where('trans_resi_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::PICKUP_DATE) {
                $query->where('trans_pickup_form_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::MANIFEST_DATE) {
                $query->where('trans_manifest_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::DO_DATE) {
                $query->where('trans_delivery_order_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::LGE_DATE) {
                $query->where('trans_customer_taking.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::LGE_TRANSACT_DATE) {
                $query->where('trans_customer_taking_transact.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::RECEIPT_DATE) {
                $query->where('trans_receipt_or_return_delivery_line.received_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            }
        }

        if (!empty($request->get('dateTo'))){
            $dateTo = new \DateTime($request->get('dateTo'));
            if ($request->get('periode') == self::RESI_DATE) {
                $query->where('trans_resi_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::PICKUP_DATE) {
                $query->where('trans_pickup_form_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::MANIFEST_DATE) {
                $query->where('trans_manifest_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::DO_DATE) {
                $query->where('trans_delivery_order_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::LGE_DATE) {
                $query->where('trans_customer_taking.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::LGE_TRANSACT_DATE) {
                $query->where('trans_customer_taking_transact.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::RECEIPT_DATE) {
                $query->where('trans_receipt_or_return_delivery_line.received_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            }
        }

        $data = [];
        foreach($query->get() as $resi) {
            $resi = TransactionResiHeader::find($resi->resi_header_id);
            $dataResi = $this->getDataResi($resi);
            $dataManifest = $this->getDataManifest($resi);
            $dataLGE = $this->getDataLGE($resi);
            $dataDO = $this->getDataDO($resi);

            $countRow = max(1, count($dataManifest), count($dataLGE), count($dataDO));
            for($i = 0; $i < $countRow ; $i++) {
                $data[] = [
                    'resi_number' => $dataResi['resi_number'],
                    'customer_sender' => $dataResi['customer_sender'],
                    'sender_name' => $dataResi['sender_name'],
                    'sender_address' => $dataResi['sender_address'],
                    'customer_receiver' => $dataResi['customer_receiver'],
                    'receiver_name' => $dataResi['receiver_name'],
                    'receiver_address' => $dataResi['receiver_address'],
                    'route_code' => $dataResi['route_code'],
                    'item_name' => $dataResi['item_name'],
                    'total_coly' => $dataResi['total_coly'],
                    'total_weight' => $dataResi['total_weight'],
                    'total_weight_price' => $dataResi['total_weight_price'],
                    'total_volume' => $dataResi['total_volume'],
                    'total_volume_price' => $dataResi['total_volume_price'],
                    'unit' => $dataResi['unit'],
                    'total_unit_price' => $dataResi['total_unit_price'],
                    'total_amount' => $dataResi['total_amount'],
                    'discount' => $dataResi['discount'],
                    'total' => $dataResi['total'],
                    'pickup_number' => $dataResi['pickup_number'],
                    'pickup_date' => $dataResi['pickup_date'],
                    'manifest_number' => !empty($dataManifest[$i]['manifest_number']) ? $dataManifest[$i]['manifest_number'] : null,
                    'manifest_date' => !empty($dataManifest[$i]['manifest_date']) ? $dataManifest[$i]['manifest_date'] : null,
                    'manifest_coly_sent' => !empty($dataManifest[$i]['manifest_coly_sent']) ? $dataManifest[$i]['manifest_coly_sent'] : null,
                    'manifest_receipt_date' => !empty($dataManifest[$i]['manifest_receipt_date']) ? $dataManifest[$i]['manifest_receipt_date'] : null,
                    'manifest_coly_receipt' => !empty($dataManifest[$i]['manifest_coly_receipt']) ? $dataManifest[$i]['manifest_coly_receipt'] : null,
                    'lge_number' => !empty($dataLGE[$i]['lge_number']) ? $dataLGE[$i]['lge_number'] : null,
                    'lge_date' => !empty($dataLGE[$i]['lge_date']) ? $dataLGE[$i]['lge_date'] : null,
                    'lge_transact_number' => !empty($dataLGE[$i]['lge_transact_number']) ? $dataLGE[$i]['lge_transact_number'] : null,
                    'lge_transact_date' => !empty($dataLGE[$i]['lge_transact_date']) ? $dataLGE[$i]['lge_transact_date'] : null,
                    'lge_coly_taken' => !empty($dataLGE[$i]['lge_coly_taken']) ? $dataLGE[$i]['lge_coly_taken'] : null,
                    'do_number' => !empty($dataDO[$i]['do_number']) ? $dataDO[$i]['do_number'] : null,
                    'do_date' => !empty($dataDO[$i]['do_date']) ? $dataDO[$i]['do_date'] : null,
                    'do_partner' => !empty($dataDO[$i]['do_partner']) ? $dataDO[$i]['do_partner'] : null,
                    'do_coly_sent' => !empty($dataDO[$i]['do_coly_sent']) ? $dataDO[$i]['do_coly_sent'] : null,
                    'do_receipt_number' => !empty($dataDO[$i]['do_receipt_number']) ? $dataDO[$i]['do_receipt_number'] : null,
                    'do_received_by' => !empty($dataDO[$i]['do_received_by']) ? $dataDO[$i]['do_received_by'] : null,
                    'do_received_date' => !empty($dataDO[$i]['do_received_date']) ? $dataDO[$i]['do_received_date'] : null,
                    'do_received_coly' => !empty($dataDO[$i]['do_received_coly']) ? $dataDO[$i]['do_received_coly'] : null,
                    'do_returned_date' => !empty($dataDO[$i]['do_returned_date']) ? $dataDO[$i]['do_returned_date'] : null,
                    'do_returned_coly' => !empty($dataDO[$i]['do_returned_coly']) ? $dataDO[$i]['do_returned_coly'] : null,
                ];
            }
        }

        return $data;
    }

    protected function getDataResi(TransactionResiHeader $resi)
    {
        $units = [];
        foreach($resi->lineUnit as $lineUnit) {
            $units[] = $lineUnit->total_unit.' '.$lineUnit->item_name;
        }

        $pickupForm = null;
        if ($resi->pickupRequest !== null) {
            foreach($resi->pickupRequest->pickupForm as $pickupFormLine) {
                if ($pickupFormLine->header !== null && $pickupFormLine->header->isClosed()) {
                    $pickupForm = $pickupFormLine->header;
                }
            } 
        }

        $pickupDate = $pickupForm !== null ? new \DateTime($pickupForm->created_date) : null;

        return [
            'resi_number' => $resi->resi_number,
            'customer_sender' => $resi->customer !== null ? $resi->customer->customer_name : '',
            'sender_name' => $resi->sender_name,
            'sender_address' => $resi->sender_address,
            'customer_receiver' => $resi->customerReceiver !== null ? $resi->customerReceiver->customer_name : '',
            'receiver_name' => $resi->receiver_name,
            'receiver_address' => $resi->receiver_address,
            'route_code' => $resi->route !== null ? $resi->route->route_code : '',
            'item_name' => $resi->getItemAndUnitNames(),
            'total_coly' => $resi->totalColy(),
            'total_weight' => $resi->totalWeight(),
            'total_weight_price' => $resi->totalWeightPrice(),
            'total_volume' => $resi->totalVolume(),
            'total_volume_price' => $resi->totalVolumePrice(),
            'unit' => implode(', ', $units),
            'total_unit_price' => $resi->totalUnitPrice(),
            'total_amount' => $resi->totalAmount(),
            'discount' => $resi->discount,
            'total' => $resi->total(),
            'pickup_number' => $resi->pickupRequest !== null ? $resi->pickupRequest->pickup_request_number : '',
            'pickup_date' => $pickupDate !== null ? $pickupDate->format('d-m-Y') : '',
        ];
    }

    protected function getDataManifest(TransactionResiHeader $resi)
    {
        $dataManifest = [];
        foreach($resi->manifestLine as $manifestLine) {
            $manifestHeader = $manifestLine->header;
            $manifestDate = $manifestHeader !== null ? new \DateTime($manifestHeader->created_date) : null;
            if ($manifestLine->receiptManifestLines()->count() > 0) {
                foreach($manifestLine->receiptManifestLines as $receiptLine) {
                    $manifestReceiptDate = $receiptLine->header !== null ? new \DateTime($receiptLine->header->created_date) : null;

                    $dataManifest[] = [
                        'manifest_number' => $manifestHeader !== null ? $manifestHeader->manifest_number : '',
                        'manifest_date' => $manifestDate !== null ? $manifestDate->format('d-m-Y') : '',
                        'manifest_coly_sent' => $manifestLine->coly_sent,
                        'manifest_receipt_date' => $manifestReceiptDate !== null ? $manifestReceiptDate->format('d-m-Y') : '',
                        'manifest_coly_receipt' => $receiptLine->coly_receipt,
                    ];
                }

            } else {
                $dataManifest[] = [
                    'manifest_number' => $manifestHeader !== null ? $manifestHeader->manifest_number : '',
                    'manifest_date' => $manifestDate !== null ? $manifestDate->format('d-m-Y') : '',
                    'manifest_coly_sent' => $manifestLine->coly_sent,
                    'manifest_receipt_date' => null,
                    'manifest_coly_receipt' => null,
                ];
            }
        }

        return $dataManifest;
    }

    protected function getDataLGE(TransactionResiHeader $resi)
    {
        $dataLGE = [];
        foreach($resi->customerTaking as $customerTaking) {
            $customerTakingDate = new \DateTime($customerTaking->created_date);
            if ($customerTaking->transact()->count() > 0) {
                foreach($customerTaking->transact as $transact) {
                    $transactDate = new \DateTime($transact->created_date);

                    $dataLGE[] = [
                        'lge_date' => $customerTakingDate->format('d-m-Y'),
                        'lge_number' => $customerTaking->customer_taking_number,
                        'lge_transact_date' => $transactDate->format('d-m-Y'),
                        'lge_transact_number' => $transact->customer_taking_transact_number,
                        'lge_coly_taken' => $transact->coly_taken,
                    ];
                }

            } else {
                $dataLGE[] = [
                    'lge_date' => $customerTakingDate->format('d-m-Y'),
                    'lge_number' => $customerTaking->customer_taking_number,
                    'lge_transact_date' => null,
                    'lge_transact_number' => null,
                    'lge_coly_taken' => null,
                ];
            }
        }

        return $dataLGE;
    }

    protected function getDataDO(TransactionResiHeader $resi)
    {
        $dataDO = [];
        foreach($resi->deliveryOrder as $doLine) {
            $doHeader = $doLine->header;
            $doDate = $doHeader !== null ? new \DateTime($doHeader->created_date) : null;
            $receiptReturnLine = $doLine->receiptReturn;

            $qtyReceived = $receiptReturnLine !== null ? intval($receiptReturnLine->total_coly) : null;
            $qtyReturned = $receiptReturnLine !== null ? $doLine->total_coly - $qtyReceived : null;
            $receivedDate = $receiptReturnLine !== null && $receiptReturnLine->isReceived() ? new \DateTime($receiptReturnLine->received_date) : null;
            $returnedDate = $receiptReturnLine !== null && $receiptReturnLine->isReturned() || $qtyReturned > 0 ? new \DateTime($receiptReturnLine->received_date) : null;

            $dataDO[] = [
                'do_number' => $doHeader !== null ? $doHeader->delivery_order_number : '',
                'do_date' => $doDate !== null ? $doDate->format('d-m-Y') : '',
                'do_partner' => $doHeader !== null && $doHeader->partner !== null ? $doHeader->partner->vendor_name : '',
                'do_coly_sent' => $doLine->total_coly,
                'do_receipt_number' => $receiptReturnLine !== null && $receiptReturnLine->header !== null ? $receiptReturnLine->header->receipt_or_return_delivery_number : '',
                'do_received_by' => $receiptReturnLine !== null ? $receiptReturnLine->received_by : '',
                'do_received_date' => $receivedDate !== null ? $receivedDate->format('d-m-Y') : '',
                'do_received_coly' => $qtyReceived,
                'do_returned_date' => $returnedDate !== null ? $returnedDate->format('d-m-Y') : '',
                'do_returned_coly' => $qtyReturned,
            ];
        }

        return $dataDO;
    }

    protected function getOptionsPeriod()
    {
        return [
            self::RESI_DATE,
            self::PICKUP_DATE,
            self::MANIFEST_DATE,
            self::DO_DATE,
            self::LGE_DATE,
            self::LGE_TRANSACT_DATE,
            self::RECEIPT_DATE,
        ];
    }
}
