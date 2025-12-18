<?php

namespace App\Listeners;

use App\Events\POStatusUpdated;
use App\Models\DiamondPacks;
use App\Models\Invoice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateInvoiceAfterPOStatusUpdated
{
    public function handle(POStatusUpdated $event)
    {
        $po = $event->po;
        if ($po->status === 'CP') {
            $diamondPack = DiamondPacks::find($po->diamond_pack_id); 
            $invoice = Invoice::create([
                'po_id' => $po->id,
                'amount' => $diamondPack->price,
                'status' => 'paid',
            ]);

            $event->invoice = $invoice;
        }
    }
}
