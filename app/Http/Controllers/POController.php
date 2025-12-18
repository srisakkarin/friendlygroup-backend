<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\AppData;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class POController extends Controller
{

    protected $client;
    protected $baseUrl;
    protected $merchantId;
    protected $authKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = 'https://apis.paysolutions.asia/tep/api/v2/';
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'diamond_pack_id' => 'required|integer',
            'user_id' => 'required|integer',
            'status' => 'required|string',
            // 'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $po = PurchaseOrder::create([
            'diamond_pack_id' => $request->diamond_pack_id,
            'user_id' => $request->user_id,
            'status' => $request->status
        ]);

        $setting = AppData::first();
        if (!$setting) {
            return response()->json(['error' => 'Merchant settings not found'], 500);
        }

        $merchantId = $setting->merchantID;
        $authKey = $setting->authKey;

        // Ensure the related models exist
        if (!$po->diamondPack || !$po->user) {
            return response()->json([
                'error' => 'Invalid purchase order data',
            ], 500);
        }

        /*
        // create qr code
        curl --location --request POST
        'https://apis.paysolutions.asia/tep/api/v2/promptpaynew?merchantID=12345678&productDe
        tail=test&customerEmail=customer@email.com&customerName=customer&total=1.12&refere
        nceNo=123456789012'
        --header 'accept: application/json'
        --header 'authorization: Bearer sdfgdsfgdsfgdsfgdsfgdsgf'
        */

        // Generate QR Code Payment Request
        try {
            $response = $this->client->post($this->baseUrl . 'promptpaynew', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $authKey,
                    'Accept'        => 'application/json',
                ],
                'form_params' => [ // Change from 'query' to 'form_params' to ensure proper POST request
                    'merchantID'    => $merchantId,
                    'productDetail' => $po->diamondPack->amount,
                    'customerEmail' => 'contact@ibig.dev', // $po->user->email,
                    'customerName'  => $po->user->fullname,
                    'total'         => $po->diamondPack->price,
                    'referenceNo'   => "1011" . $po->id,
                ],
            ]);

            // Decode API response
            $qrResponse = json_decode($response->getBody(), true);

            // Check if API response contains the expected data
            if (!isset($qrResponse['status']) || $qrResponse['status'] !== 'success') {
                return response()->json([
                    'error' => 'Failed to generate QR code',
                    'qrResponse' => $qrResponse
                ], 500);
            }

            $po->data = json_encode($qrResponse['data']);
            $po->save();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => [
                    'id' => $po->id,
                    'diamond_pack_id' => $po->diamond_pack_id,
                    'user_id' => $po->user_id,
                    'status' => $po->status,
                    'payment_method' => 'QR Code',
                ],
                'qr_code' => $qrResponse['data']['image'], // Base64 QR code
                'referenceNo' => $qrResponse['data']['referenceNo'],
                'total' => $qrResponse['data']['total'],
                'expiredate' => $qrResponse['data']['expiredate']
            ], 201);
        } catch (RequestException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'e' => $e
            ], 500);
        }
    }
}
