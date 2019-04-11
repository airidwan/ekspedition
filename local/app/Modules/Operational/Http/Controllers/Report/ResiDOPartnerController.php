<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Transaction\DeliveryOrderLine;
use App\Modules\Operational\Model\Transaction\DeliveryOrderHeader;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;
use App\Modules\Accountreceivables\Model\Transaction\Invoice;

class ResiDOPartnerController extends Controller
{
    const RESOURCE  = 'Operational\Report\ResiDOPartner';
    const URL       = 'operational/report/resi-do-partner';
    const URL_PRINT = 'operational/report/resi-do-partner/print-excel';

    const RESI_DATE = 'Resi Date';
    const DO_DATE   = 'DO Date';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        if ($request->user()->cannot('access', [self::RESOURCE, 'view'])) {
            abort(403);
        }

        return view('operational::report.resi-do-partner.index', [
            'optionPeriode' => [self::DO_DATE, self::RESI_DATE],
            'resource' => self::RESOURCE,
            'urlPrint' => self::URL_PRINT,
        ]);
    }

    public function printExcel(Request $request)
    {
        $data = $this->getData($request);

        \Excel::create(trans('operational/menu.resi-do-partner'), function($excel) use ($data) {
            $excel->sheet('Sheet1', function($sheet) use ($data) {
                $sheet->cell('A1', function($cell) {
                    $cell->setFont(['size' => '14', 'bold' => true]);
                    $cell->setValue(trans('operational/menu.resi-do-partner'));
                });

                $currentRow = 1;
                $currentResiNumber = '';
                foreach($data as $doLine) {
                    if ($doLine->resi->resi_number != $currentResiNumber) {
                        $currentResiNumber = $doLine->resi->resi_number;

                        // NOMOR RESI
                        $currentRow += 2;
                        $sheet->cell('A'.$currentRow, function($cell) use ($doLine) {
                            $cell->setFont(['bold' => true]);
                            $cell->setValue($doLine->resi->resi_number);
                        });

                        // HEADER TABEL
                        $dataDetailRevenue = $this->getDataDetailRevenue($doLine->resi);
                        $headerColumns = [
                            trans('shared/common.customer'),
                            trans('operational/fields.sender'),
                            trans('shared/common.address'),
                            trans('shared/common.customer'),
                            trans('operational/fields.receiver'),
                            trans('shared/common.address'),
                            trans('operational/fields.route'),
                            trans('operational/fields.item-name'),
                            trans('operational/fields.total-coly'),
                            trans('operational/fields.weight'),
                            trans('operational/fields.total-price'),
                            trans('operational/fields.volume'),
                            trans('operational/fields.total-price'),
                            trans('accountreceivables/fields.discount'),
                            trans('accountreceivables/fields.total-amount-resi'),
                        ];

                        foreach($dataDetailRevenue as $cityCode => $revenue) {
                            $headerColumns[] = trans('accountreceivables/fields.revenue').' - '.$cityCode;
                        }

                        $headerColumns = array_merge(
                            $headerColumns,
                            [
                                trans('accountreceivables/fields.invoice'),
                                trans('shared/common.date'),
                                trans('accountreceivables/fields.amount-receipt'),
                                trans('operational/fields.do-partner'),
                                trans('shared/common.date'),
                                trans('operational/fields.partner'),
                                trans('accountreceivables/fields.amount-do'),
                                trans('operational/fields.amount-do-partner'),
                            ]
                        );

                        $currentRow += 2;
                        $letter = chr(ord('A') - 1 + count($headerColumns));
                        $sheet->cells('A'.$currentRow.':'.$letter.$currentRow, function($cells) {
                            $cells->setBackground('#dddddd');
                        });

                        $sheet->row($currentRow, $headerColumns);
                    }

                    // DATA TABLE
                    $currentRow ++;
                    $dataColumns = [
                        $doLine->resi->customer !== null ? $doLine->customer->customer_name : '',
                        $doLine->resi->sender_name,
                        $doLine->resi->sender_address,
                        $doLine->resi->customerReceiver !== null ? $doLine->customerReceiver->customer_name : '',
                        $doLine->resi->receiver_name,
                        $doLine->resi->receiver_address,
                        $doLine->resi->route->route_code,
                        $doLine->resi->item_name,
                        $doLine->resi->totalColy(),
                        $doLine->resi->totalWeight(),
                        $doLine->resi->totalWeightPrice(),
                        $doLine->resi->totalVolume(),
                        $doLine->resi->totalVolumePrice(),
                        $doLine->resi->discount,
                        $doLine->resi->totalAmount(),
                    ];

                    foreach($dataDetailRevenue as $cityCode => $revenue) {
                        $dataColumns[] = $revenue;
                    }

                    $invoiceResi = $doLine->resi->invoices()->where('type', '=', Invoice::INV_RESI)->first();
                    $invoiceDate = $invoiceResi !== null ? new \DateTime($invoiceResi->created_date) : null;
                    $doDate = new \DateTime($doLine->header->created_date);

                    $dataColumns = array_merge(
                        $dataColumns,
                        [
                            $invoiceResi !== null ? $invoiceResi->invoice_number : '',
                            $invoiceDate !== null ? $invoiceDate->format('d-m-Y') : '',
                            $invoiceResi !== null ? $invoiceResi->totalReceipt() : 0,
                            $doLine->header->delivery_order_number,
                            $doDate->format('d-m-Y'),
                            $doLine->header->partner !== null ? $doLine->header->partner->vendor_name : '',
                            $doLine->delivery_cost,
                            $doLine->invoiceLine !== null ? $doLine->invoiceLine->amountPlusTax() : 0,
                        ]
                    );

                    $sheet->row($currentRow, $dataColumns);
                }
            });

        })->export('xlsx');
    }

    protected function getDataDetailRevenue(TransactionResiHeader $resi)
    {
        $dataDetailRevenue = [];
        $route = $resi->route;

        if ($route->details->count() == 0) {
            $dataDetailRevenue[$resi->branch->city->city_code] = $resi->totalAmount();
        } else {
            foreach ($route->details as $detail) {
                $persen = round($detail->rate_kg / $route->rate_kg * 100);
                $dataDetailRevenue[$detail->cityStart->city_code] = $persen / 100 * $resi->totalAmount();
            }
        }

        return $dataDetailRevenue;
    }

    protected function getData(Request $request)
    {
        $query = \DB::table('op.trans_delivery_order_line')
                        ->select('op.trans_delivery_order_line.*')
                        ->join('op.trans_resi_header', 'trans_resi_header.resi_header_id', '=', 'trans_delivery_order_line.resi_header_id')
                        ->join('op.trans_delivery_order_header', 'trans_delivery_order_header.delivery_order_header_id', '=', 'trans_delivery_order_line.delivery_order_header_id')
                        ->join('ap.mst_vendor', 'mst_vendor.vendor_id', '=', 'trans_delivery_order_header.partner_id')
                        ->where('trans_resi_header.status', '=', TransactionResiHeader::APPROVED)
                        ->where('trans_delivery_order_header.type', '=', DeliveryOrderHeader::TRANSITION)
                        ->orderBy('trans_resi_header.created_date', 'desc')
                        ->orderBy('trans_delivery_order_header.created_date', 'desc')
                        ->orderBy('trans_delivery_order_line.delivery_order_line_id', 'asc');

        if (!empty($request->get('resiNumber'))){
            $query->where('trans_resi_header.resi_number', 'ilike', '%'.$request->get('resiNumber').'%');
        }

        if (!empty($request->get('doNumber'))){
            $query->where('trans_delivery_order_header.do_number', 'ilike', '%'.$request->get('doNumber').'%');
        }

        if (!empty($request->get('dateFrom'))){
            $dateFrom = new \DateTime($request->get('dateFrom'));
            if ($request->get('periode') == self::RESI_DATE) {
                $query->where('trans_resi_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            } elseif ($request->get('periode') == self::DO_DATE) {
                $query->where('trans_delivery_order_header.created_date', '>=', $dateFrom->format('Y-m-d 00:00:00'));
            }
        }

        if (!empty($request->get('dateTo'))){
            $dateTo = new \DateTime($request->get('dateTo'));
            if ($request->get('periode') == self::RESI_DATE) {
                $query->where('trans_resi_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            } elseif ($request->get('periode') == self::DO_DATE) {
                $query->where('trans_delivery_order_header.created_date', '<=', $dateTo->format('Y-m-d 23:59:59'));
            }
        }

        $data = [];
        foreach($query->get() as $doLine) {
            $data[] = DeliveryOrderLine::find($doLine->delivery_order_line_id);
        }

        return $data;
    }
}
