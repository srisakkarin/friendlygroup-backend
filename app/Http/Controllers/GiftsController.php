<?php

namespace App\Http\Controllers;

use App\Models\GiftHistory;
use App\Models\Gifts;
use App\Models\GlobalFunction;
use App\Models\RevenueSharingRule;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GiftsController extends Controller
{
    public function getAllGifts(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Default to 10 items per page if not specified
            $page = $request->input('page', 1); // Default to page 1 if not specified

            $gifts = Gifts::paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => 'Gifts fetched successfully.',
                'data' => $gifts->items(),
                'pagination' => [
                    'total' => $gifts->total(),
                    'per_page' => $gifts->perPage(),
                    'current_page' => $gifts->currentPage(),
                    'last_page' => $gifts->lastPage(),
                    'from' => $gifts->firstItem(),
                    'to' => $gifts->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('GiftsController: getAllGifts - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching gifts.',
            ], 500);
        }
    }

    public function getGiftById(Request $request)
    {
        try {
            $id = $request->gift_id;

            $gift = Gifts::find($id);

            if (!$gift) {
                Log::error('GiftsController: getGiftById - Gift not found');
                return response()->json([
                    'status' => false,
                    'message' => 'Gift not found.',
                ], 404);
            }
            Log::info('GiftsController: getGiftById - Gift fetched successfully.', ['id' => $id]);
            return response()->json([
                'status' => true,
                'message' => 'Gift fetched successfully.',
                'data' => $gift,
            ]);
        } catch (\Exception $e) {
            Log::error('GiftsController: getGiftById - An error occurred.', ['error' => $e->getMessage(), 'request' => $request->all()]);

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching the gift.',
            ], 500);
        }
    }

    public function buyGift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gift_id' => 'required|integer',
            'user_id' => 'required|integer',
            'recipient_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $userId = $request->user_id; //ผู้ส่งของขวัญ
            $recipientId = $request->input('recipient_id'); // ผู้รับของขวัญ
            $giftId = $request->gift_id;
            $gift = Gifts::find($giftId);

            if (!$gift) {
                Log::error('GiftsController: buyGift - Gift not found');
                return response()->json([
                    'status' => false,
                    'message' => 'Gift not found.',
                ], 404);
            }
            $user = Users::find($userId);
            $result = GlobalFunction::minusCoinsFromWallet($user->id, $gift->coin_price, 2, 5);
            if ($result->getStatusCode() !== 200) {
                return response()->json([
                    'status' => false,
                    'message' => $result->getData()->message,
                ], $result->getStatusCode());
            } else {
                /**
                 * เพิ่มเหรียญให้ผู้รับของขวัญ (อาจจะเป็นแบบนี้)
                 * code ...
                 */
                if ($recipientId) {
                    $recipient = Users::find($recipientId);
                    if (!$recipient) {
                        Log::error('GiftsController: buyGift - Recipient not found');
                        return response()->json([
                            'status' => false,
                            'message' => 'Recipient not found.',
                        ], 404);
                    }
                    // เพิ่มจำนวนการ รับ-ส่ง ของขวัญในตาราง users
                    $user->total_gifts_sent += 1;
                    $user->save();

                    $recipient->total_gifts_received += 1;
                    $recipient->save();
                    // สร้างรายการในตาราง gift_histories
                    $history = GiftHistory::create([
                        'sender_id' => $userId,
                        'recipient_id' => $recipientId,
                        'gift_id' => $giftId,
                        'amount' => $gift->coin_price,
                    ]);
                    // เพิ่ม coin เข้า wallet ของผู้รับ
                    $giftIncomeConfig = RevenueSharingRule::where('action_key', 'send_gift')->first();
                    if ($giftIncomeConfig->calculate_with === "percentage") {
                        $recipientIncome =  ($giftIncomeConfig->customer_percent / 100) * $gift->coin_price;
                    } else {
                        $recipientIncome = $giftIncomeConfig->customer_amount;
                    }

                    $addCoinToWallet = GlobalFunction::addCoinsToWallet($recipientId, $recipientIncome, 1, 8);
                    if ($addCoinToWallet->getStatusCode() !== 200) {
                        return response()->json([
                            'status' => false,
                            'message' => $addCoinToWallet->getData()->message,
                        ], $addCoinToWallet->getStatusCode());
                    } else {
                        // Log success message
                        Log::info('GiftsController: add coin to wallet - Success [recipient_id][' . $recipientId . ']');
                    }
                    Log::info('GiftsController: buyGift - Gift purchased and sent successfully. [HistoryID][' . $history->id . ']');
                    return response()->json([
                        'status' => true,
                        'message' => 'Gift purchased and sent successfully.',
                        'data' => [
                            'gift' => $gift,
                            'remaining_coins' => $user->wallet,
                        ],
                    ], 200);
                }
            }
        } catch (\Exception $e) {
            Log::error('GiftsController: buyGift - General error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }

    public function getSenderGifts(Request $request)
    {
        try {
            $userId = $request->user_id;
            $perPage = $request->input('per_page', 10); // Default to 10
            $page = $request->input('page', 1); // Default to page 1
            $user = Users::find($userId);
            if (!$user) {
                Log::error('GiftsController: getSenderGifts - User not found');
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }
            $gifts = GiftHistory::query()->with('gift')->where('sender_id', $userId)->paginate($perPage, ['*'], 'page', $page);
            return response()->json([
                'status' => true,
                'message' => 'Gifts fetched successfully.',
                'data' => $gifts->items(),
                'pagination' => [
                    'total' => $gifts->total(),
                    'per_page' => $gifts->perPage(),
                    'current_page' => $gifts->currentPage(),
                    'last_page' => $gifts->lastPage(),
                    'from' => $gifts->firstItem(),
                    'to' => $gifts->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('GiftsController: getSenderGifts - General error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching gifts.',
            ], 500);
        }
    }

    public function getRecipientGifts(Request $request)
    {
        try {
            $userId = $request->user_id;
            $perPage = $request->input('per_page', 10); // Default to 10
            $page = $request->input('page', 1); // Default to page 1
            $user = Users::find($userId);
            if (!$user) {
                Log::error('GiftsController: getRecipientGifts - User not found');
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }
            $gifts = GiftHistory::query()->with('gift')->where('recipient_id', $userId)->paginate($perPage, ['*'], 'page', $page);
            return response()->json([
                'status' => true,
                'message' => 'Gifts fetched successfully.',
                'data' => $gifts->items(),
                'pagination' => [
                    'total' => $gifts->total(),
                    'per_page' => $gifts->perPage(),
                    'current_page' => $gifts->currentPage(),
                    'last_page' => $gifts->lastPage(),
                    'from' => $gifts->firstItem(),
                    'to' => $gifts->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('GiftsController: getRecipientGifts - General error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching gifts.',
            ], 500);
        }
    }
}
