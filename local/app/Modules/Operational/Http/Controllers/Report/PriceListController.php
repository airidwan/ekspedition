<?php

namespace App\Modules\Operational\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Modules\Operational\Model\Master\MasterRoute;
use App\Modules\Operational\Model\Transaction\TransactionResiHeader;

class PriceListController extends Controller
{
    public function calculatePrice(Request $request)
    {
        $filters = [];
        $price = 0;
        $route = null;
        if ($request->isMethod('post')) {
            $filters = $request->all();

            $this->validate($request, [
                'cityStartId' => 'required',
                'cityEndId' => 'required',
            ]);

            $cityStartId = intval($request->get('cityStartId', ''));
            $cityEndId = intval($request->get('cityEndId', ''));

            $route = MasterRoute::where('city_start_id', '=', $cityStartId)
                                    ->where('city_end_id', '=', $cityEndId)
                                    ->orderBy('route_id', 'desc')
                                    ->first();

            if ($route === null) {
                return redirect(\URL::previous())->withInput($request->all())->withErrors(['errorMessage' => 'Route not found']);
            }

            $price = $this->getPrice($request, $route);
        }
        return view('operational::report.price-list.calculate-price', [
            'filters' => $filters,
            'price'   => $price,
            'route'   => $route,
            'optionCityStart' => $this->getOptionCityStart(),
            'optionCityEnd' => $this->getOptionCityEnd(),
        ]);
    }

    protected function getOptionCityStart()
    {
        $query = \DB::table('op.mst_city')
                        ->select('mst_city.*')
                        ->join('op.mst_route', 'mst_city.city_id', '=', 'mst_route.city_start_id')
                        ->distinct()
                        ->orderBy('mst_city.city_name');
        
        return $query->get();
    }

    protected function getOptionCityEnd()
    {
        $query = \DB::table('op.mst_city')
                        ->select('mst_city.*')
                        ->join('op.mst_route', 'mst_city.city_id', '=', 'mst_route.city_end_id')
                        ->distinct()
                        ->orderBy('mst_city.city_name');
        
        return $query->get();
    }

    protected function getPrice(Request $request, MasterRoute $route)
    {
        $weight = floatval(str_replace(',', '', $request->get('weight', 0)));
        $long = str_replace(',', '', $request->get('long', 0));
        $width = str_replace(',', '', $request->get('width', 0));
        $height = str_replace(',', '', $request->get('height', 0));

        $volume = $long * $width * $height / 1000000;


        $priceWeight = $weight * $route->rate_kg;
        $priceVolume = $volume * $route->rate_m3;
        $winPrice = $priceWeight > $priceVolume ? $priceWeight : $priceVolume;

        $price = $winPrice > $route->minimum_rates ? $winPrice : $route->minimum_rates;

        return floatval(floor($price / TransactionResiHeader::PEMBULATAN) * TransactionResiHeader::PEMBULATAN);
    }

    protected function getCity()
    {
        $startCity = \DB::table('op.mst_city')
                        ->select('mst_city.city_id', 'mst_city.city_name', 'mst_city.city_code', 'mst_city.province')
                        ->join('op.mst_route', 'mst_city.city_id', '=', 'mst_route.city_start_id')
                        ->distinct()
                        ->orderBy('mst_city.city_name')
                        ->get();

        $destinationCity = \DB::table('op.mst_city')
                        ->select('mst_city.city_id', 'mst_city.city_name', 'mst_city.city_code', 'mst_city.province')
                        ->join('op.mst_route', 'mst_city.city_id', '=', 'mst_route.city_end_id')
                        ->distinct()
                        ->orderBy('mst_city.city_name')
                        ->get();

        return response()->json([
            'status'            => 'success',
            'message'           => 'Success',
            'start_city'        => $startCity,
            'destination_city'  => $destinationCity,
        ], 200);
    }

    public function getCalculatePrice(Request $request)
    {
        $filters = [];
        $price = 0;
        $route = null;
        if ($request->isMethod('post')) {
            $filters = $request->all();

            if(empty($request->startCity) || empty($request->destinationCity)){
                return response()->json([
                    'status'            => 'failed',
                    'message'           => 'Kota asal dan kota tujuan harus diisi',
                ], 200);
            }

            $startCity       = intval($request->get('startCity', ''));
            $destinationCity = intval($request->get('destinationCity', ''));

            $route = MasterRoute::where('city_start_id', '=', $startCity)
                                    ->where('city_end_id', '=', $destinationCity)
                                    ->orderBy('route_id', 'desc')
                                    ->first();

            if ($route === null) {
                return response()->json([
                    'status'            => 'failed',
                    'message'           => 'Rute tidak ditemukan',
                ], 200);
            }

            $price = $this->getPrice($request, $route);
        }

        $arrRoute = [
            'route_id'            => $route->route_id,
            'route_code'          => $route->route_code,
            'route_desc'          => $route->cityStart->city_name.'-'.$route->cityEnd->city_name,
            'rate_kg'             => $route->rate_kg,
            'rate_m3'             => $route->rate_m3,
            'minimum_weight'      => $route->minimum_weight,
            'minimum_rates'       => $route->minimum_rates,
            'delivery_estimation' => $route->delivery_estimation,
            'description'         => $route->description,
        ];

        return response()->json([
            'status'            => 'success',
            'message'           => 'Success',
            'price_result'      => $price,
            'description'       => $arrRoute,
        ], 200);
    }

}
