<?php

namespace App\Http\Controllers;

use App\Events\POStatusUpdated;
use App\Models\DiamondPacks;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function updatePO(Request $request)
    {
        /**
         * {
            "refno": "101169",
            "orderno": "109197245",
            "merchantid": "39148222",
            "customeremail": "contact@ibig.dev",
            "productdetail": "1",
            "cardtype": "PP",
            "total": 1.0000,
            "status": "CP",
            "statusname": "COMPLETED"
            }
         */
        $refno = $request->refno;
        $prefix = substr($refno,0,4);
        $po_id = substr($refno,4);
        $po = PurchaseOrder::find((int)$po_id);
        
        if (!$po) {
            return response()->json(['error' => 'PO not found'], 404);
        }

        $diamondPack = DiamondPacks::find($po->diamond_pack_id); 
        $po->update([
            'status' => $request->status,
            'payment_method'=> $request->cardtype
        ]); 

        if ($request->status === 'CP') { 
            $user = $po->user;
            $user->wallet += $diamondPack->amount;
            $user->save();
            // create invoice
            $event = new POStatusUpdated($po);
            event($event);
            return response()->json([
                'message' => 'PO updated successfully',
                'po' => $po,
                'invoice' => $event->invoice,
            ]);
        }
    }
}
