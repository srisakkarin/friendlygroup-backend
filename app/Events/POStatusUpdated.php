<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Queue\SerializesModels;

class POStatusUpdated
{
    use SerializesModels;
    public $po;
    public $invoice;
    public function __construct(PurchaseOrder $po,Invoice $invoice = null)
    {
        $this->po = $po;
        $this->invoice = $invoice;
    }
}
