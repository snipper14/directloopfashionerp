<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use App\PurchaseOrder\PurchaseOrderReceivableWithBatch;

class ValidateExpiryOnSale implements Rule
{
    protected $request;
    protected $item_name;
    protected $blocking_expiry_date; // for a nicer message

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function passes($attribute, $value)
    {
        $stock_id = $value;

        // attribute is like "order_data_offline.0.stock_id"
        $index    = explode('.', (string) $attribute)[1] ?? null;

        // pull from request safely
        $batch_no         = data_get($this->request, "order_data_offline.$index.batch_no");
        $this->item_name  = data_get($this->request, "order_data_offline.$index.product_name");

        // If no batch is provided, don't block the sale
        if (!$batch_no) {
            return true;
        }

        // Find current batch row
        $current = PurchaseOrderReceivableWithBatch::query()
            ->where('stock_id', $stock_id)
            ->where('batch_no', $batch_no)
            ->first();

        // If we can't resolve the current batch or it has no expiry_date, let it pass
        // (change this behavior if you want to *force* a expiry_date to exist)
        if (!$current || !$current->expiry_date) {
            return true;
        }

        // Is there any other batch (same stock) with qty left that was received earlier?
        $earlierReceivedDate = PurchaseOrderReceivableWithBatch::query()
            ->where('stock_id', $stock_id)
            ->where('batch_no', '<>', $batch_no)
            ->whereColumn('qty_sold', '<', 'qty_delivered')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', $current->expiry_date)
            ->orderBy('expiry_date', 'asc')
            ->value('expiry_date'); // earliest older date or null

        if ($earlierReceivedDate) {
            $this->blocking_expiry_date = $earlierReceivedDate;
            return false; // block: there is an older received batch with stock remaining
        }

        return true; // OK to sell this batch
    }

    public function message()
    {
        $date = $this->blocking_expiry_date
            ? Carbon::parse($this->blocking_expiry_date)->toDateString()
            : 'an earlier date';

        return "There is another batch received earlier ({$date}) for {$this->item_name}. Please sell older batches first.";
    }
}
