<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\FarmerOrder;
use App\Models\Invoice;
use App\Models\InvoiceCost;
use App\Models\InvoiceInformation;
use App\Models\Merchant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function papayaPalntedTalukas(Request $request)
    {
        $mostFrequentTalukas = DB::table('farmers')
        ->join('talukas', 'farmers.taluka_id', '=', 'talukas.id')

        ->select('farmers.taluka_id', 'talukas.name', DB::raw('COUNT(*) as count'))
        ->groupBy('farmers.taluka_id', 'talukas.name')
        ->orderByDesc('count')
        ->take(3)
        ->get();


        if ($mostFrequentTalukas->isEmpty()) {
            $farmers_array = [];
        }

        $myObj = new \stdClass();

        foreach ($mostFrequentTalukas as $key => $value) {
            $myObj->name=$value->name;
            $myObj->count=$value->count;



            $farmers_array[] = $myObj;
            $myObj = new \stdClass();


        }


        return  response()->json(array(
            'data' => $farmers_array,
        ), 200);


        dd($mostFrequentTalukas);

    }
    public function profitLossInfoDashboard(Request $request)
    {

        if($request->date!=null || $request->date!='')
        {
            $invoice=Invoice::whereYear('created_at', '=', substr($request->date, 0, 4))
                        ->whereMonth('created_at', '=', substr($request->date, 5, 2));
        }
        else
        {
            $invoice=Invoice::all();
        }



        $gross_profit=InvoiceCost::whereIn('invoice_id',$invoice->where('type',1)->pluck('id')->toArray())->sum('overall_total');

        $debit_money=InvoiceCost::whereIn('invoice_id',$invoice->where('type',2)->pluck('id')->toArray())->sum('overall_total');

        $net_profit=$gross_profit-$debit_money;

        return  response()->json(array(
            'gross_profit' => $gross_profit,
            'debit_money' => $debit_money,
            'net_profit' => $net_profit,
        ), 200);


    }
    public function dashboardCount(Request $request)
    {

        $farmer=FarmerOrder::all();

        $farmerCount=Farmer::all()->count();



        return  response()->json(array(
            'farmer_count' => $farmerCount,
            'papaya_planted_farmer' => $farmer->where('is_planted', 1)->groupBy('farmer_id')->count(),
            'without_papaya_planted_farmer' => $farmer->where('is_planted', 0)->groupBy('farmer_id')->count(),
        ), 200);
    }

    public function purchaseDashboardDetail(Request $request)
    {

        $query = Farmer::query();

        $query_ivoice=Invoice::query();


        if ($request->start_date!=null && $request->end_date!=null){
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$start_datetime, $end_datetime]);
            $query_ivoice->whereBetween('date', [$request->start_date, $request->end_date]);

        }
        elseif ($request->start_date!=null) {
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();

            $query->where('created_at', '>=', $start_datetime);
            $query_ivoice->where('date', '>=', $request->start_date);

        }
        elseif ($request->end_date!=null) {
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->where('created_at', '<=', $end_datetime);
            $query_ivoice->where('date', '<=', $request->end_date);

        }

        $farmers = $query->get();


        $query_ivoice->where('type',2);
        $invoice=$query_ivoice->get();



        // $farmers=Farmer::all();

        // $invoice=Invoice::where('type',2);
        $invoiceIds=$invoice->pluck('id')->toArray();

        // dd($invoiceIds);
        $invoiceInformation=InvoiceInformation::whereIn('invoice_id',$invoiceIds);

        $invoiceCost=InvoiceCost::whereIn('invoice_id',$invoiceIds);



        return  response()->json(array(
            'farmer_count' => $farmers->count(),
            'total_weight' => ($invoice->count()>0) ?round($invoiceInformation->sum('actual_weight')/$invoice->count(), 2):0,
            'total_rate' => ($invoice->count()>0) ?round($invoiceInformation->sum('rate')/$invoice->count(), 2):0,
            'overall_total' => $invoiceCost->sum('overall_total'),
        ), 200);


    }

    public function sellDashboardDetail(Request $request)
    {


        $query = Merchant::query();

        $query_ivoice=Invoice::query();


        if ($request->start_date!=null && $request->end_date!=null){
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->whereBetween('created_at', [$start_datetime, $end_datetime]);
            $query_ivoice->whereBetween('date', [$request->start_date, $request->end_date]);

        }
        elseif ($request->start_date!=null) {
            $start_datetime = Carbon::parse($request->start_date)->startOfDay();

            $query->where('created_at', '>=', $start_datetime);
            $query_ivoice->where('date', '>=', $request->start_date);

        }
        elseif ($request->end_date!=null) {
            $end_datetime = Carbon::parse($request->end_date)->endOfDay();

            $query->where('created_at', '<=', $end_datetime);
            $query_ivoice->where('date', '<=', $request->end_date);

        }

        $merchants = $query->get();

        $query_ivoice->where('type',1);
        $invoice=$query_ivoice->get();



        // $merchants=Merchant::all();

        // $invoice=Invoice::where('type',1);
        $invoiceIds=$invoice->pluck('id')->toArray();
        $invoiceInformation=InvoiceInformation::whereIn('invoice_id',$invoiceIds);

        $invoiceCost=InvoiceCost::whereIn('invoice_id',$invoiceIds);



        return  response()->json(array(
            'merchant_count' => $merchants->count(),
            'total_weight' => ($invoice->count()>0) ?round($invoiceInformation->sum('actual_weight')/$invoice->count(), 2):0,
            'total_rate' => ($invoice->count()>0) ?round($invoiceInformation->sum('rate')/$invoice->count(), 2):0,
            'overall_total' => $invoiceCost->sum('overall_total'),
        ), 200);
    }
}
