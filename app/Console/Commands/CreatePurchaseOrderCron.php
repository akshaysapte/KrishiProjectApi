<?php

namespace App\Console\Commands;

use App\Models\FarmerOrder;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreatePurchaseOrderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CreatePurchaseOrderCron:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $current_date_time=Carbon::now()->toDateTimeString();

        $farmerOrders=FarmerOrder::where('is_completed',0)->whereDate('next_visible_date', '<=', $current_date_time)->get();

        if($farmerOrders->isEmpty())
        {
            $this->info('Farmer Order Not Found');
            return true;
        }

        foreach ($farmerOrders as $key => $farmerOrder) {

            // dd($farmerOrder);

            $purchase=new PurchaseOrder();
            $purchase->farmer_id=$farmerOrder->farmer_id;
            $purchase->farmer_order_id=$farmerOrder->id;
            $purchase->note='test';
            $purchase->purchase_date=$farmerOrder->next_cutting_date;
            $purchase->save();

            $next_cuttting_date = Carbon::parse($farmerOrder->next_cutting_date)->addDays($farmerOrder->cutting_ratio)->format('Y-m-d');

            if($next_cuttting_date >= $farmerOrder->cutting_end_month)
            {
                $farmerOrder->is_completed=1;
            }
            else
            {
                $threeDaysBefore = Carbon::parse($next_cuttting_date)->subDays(3)->format('Y-m-d');


                $farmerOrder->next_cutting_date=$next_cuttting_date;
                $farmerOrder->next_visible_date=$threeDaysBefore;

            }
                $farmerOrder->save();


        }

        $this->info('Succefull add purchase order');


        return Command::SUCCESS;
    }
}
