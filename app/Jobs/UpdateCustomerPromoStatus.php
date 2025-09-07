<?php

namespace App\Jobs;

use App\Customer\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class UpdateCustomerPromoStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public $customer_id;
    public function __construct($customer_id)
    {
        $this->customer_id = $customer_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer_id=$this->customer_id;
        if($customer_id){

        $customer = Customer::where('id',$customer_id)->first();
         if($customer->loyalty_program=='active'){
      
            Customer::where('id',$customer_id)->update(['is_discount_qualified'=>'active']);
       
    }

   
    }
    }
}
