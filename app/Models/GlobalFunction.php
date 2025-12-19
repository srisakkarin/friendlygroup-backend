<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Google\Client;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GlobalFunction extends Model
{
    use HasFactory;

    public static function sendSimpleResponse($status, $msg)
    {
        return response()->json(['status' => $status, 'message' => $msg]);
    }
    public static function sendDataResponse($status, $msg, $data)
    {
        return response()->json(['status' => $status, 'message' => $msg, 'data' => $data]);
    }

    public static function sendPushNotificationToAllUsers($title, $message)
    {
        $client = new Client();
        $client->setAuthConfig('googleCredentials.json');
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken();
        $accessToken = $accessToken['access_token'];

        $contents = File::get(base_path('googleCredentials.json'));
        $json = json_decode($contents, true);

        $url = 'https://fcm.googleapis.com/v1/projects/' . $json['project_id'] . '/messages:send';
        $notificationArray = array('title' => $title, 'body' => $message);

        // Construct message for iOS
        $fields_ios = array(
            'message' => [
                'topic' => env('NOTIFICATION_TOPIC') . '_ios',
                'data' => $notificationArray,
                'notification' => $notificationArray,
                'apns' => [
                    'payload' => [
                        'aps' => ['sound' => 'default']
                    ]
                ]
            ],
        );

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields_ios));

        $result = curl_exec($ch);


        $fields_android = array(
            'message' => [
                'topic' => env('NOTIFICATION_TOPIC') . '_android',
                'data' => $notificationArray,
                'apns' => [
                    'payload' => [
                        'aps' => ['sound' => 'default']
                    ]
                ]
            ],
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields_android));

        $result = curl_exec($ch);

        if ($result === false) {
            die('FCM Send Error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($result) {
            return json_encode(['status' => true, 'message' => 'Notification sent successfully']);
        } else {
            return json_encode(['status' => false, 'message' => 'Not sent!']);
        }
    }

    public static function createMediaUrl($media)
    {
        if (env('FILESYSTEM_DRIVER') === 's3') {
            $url = env('image') . $media;
        } else {
            $url = url('public/storage/' . $media);
        }
        return $url;
    }

    public static function uploadFileToS3($request, $key)
    {
        $s3 = Storage::disk('s3');
        $environment = env('APP_ENV', 'local');
        $appName = env('APP_NAME', 's3Storage'); // Default value for APP_NAME

        $file = $request->file($key);
        $fileContent = null;
        $fileName = null;
        $mimeType = null;

        if ($file) {
            // กรณีเป็นไฟล์อัปโหลดปกติ
            $fileName = time() . str_replace(" ", "_", $file->getClientOriginalName());
            $fileContent = file_get_contents($file);
            $mimeType = $file->getMimeType();
        } elseif ($request->has($key)) {
            // กรณีเป็น base64
            $base64Data = $request->input($key);

            if (preg_match('/^data:(.*?);base64,(.*)$/', $base64Data, $matches)) {
                $mimeType = $matches[1];
                $fileContent = base64_decode($matches[2]);
                $extension = explode('/', $mimeType)[1] ?? 'bin';
                $fileName = time() . '_' . uniqid() . '.' . $extension;
            } else {
                throw new \Exception("Invalid base64 format.");
            }
        } else {
            throw new \Exception("No file or base64 data found in the request.");
        }

        // ตรวจสอบประเภทไฟล์เพื่อกำหนด path
        if (str_starts_with($mimeType, 'image/')) {
            $filePath = "{$appName}/{$environment}/uploads/images/{$fileName}";
        } elseif (str_starts_with($mimeType, 'video/')) {
            $filePath = "{$appName}/{$environment}/uploads/video/{$fileName}";
        } else {
            $filePath = "{$appName}/{$environment}/uploads/other/{$fileName}";
        }

        $s3->put($filePath, $fileContent, [
            'visibility' => 'public',
        ]);

        // เอาค่าจาก ENV
        $bucket = env('AWS_BUCKET');
        $endpoint = env('AWS_ENDPOINT');
        $cdnEndpoint = env('AWS_CDN_ENDPOINT');
        $useCdn = env('AWS_CDN', false);

        $endpoint = str_replace('https://', '', $endpoint);
        $finalEndpoint = $useCdn ? $cdnEndpoint : $endpoint;

        return 'https://' . $bucket . '.' . $finalEndpoint . '/' . $filePath;
    }


    public static function point2point_distance($lat1, $lon1, $lat2, $lon2, $unit = 'K', $radius)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return (($miles * 1.609344) <= $radius);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public static function cleanString($string)
    {
        return  str_replace(array('<', '>', '{', '}', '[', ']', '`'), '', $string);
    }

    // public static function deleteFile($filename)
    // {
    //     if ($filename != null && file_exists(storage_path('app/public/' . $filename))) {
    //         unlink(storage_path('app/public/' . $filename));
    //     }
    // }

    // public static function deleteFile($filename)
    // {
    //     if ($filename != null) {
    //         $s3 = Storage::disk('s3');
    //         $bucket = env('AWS_BUCKET');

    //         // Remove https:// from the endpoint
    //         $endpoint = str_replace('https://', '', env('AWS_ENDPOINT'));
    //         $cdnEndpoint = str_replace('https://', '', env('AWS_CDN_ENDPOINT'));
    //         $useCdn = env('AWS_CDN', false);

    //         // Choose the correct endpoint based on AWS_CDN
    //         $finalEndpoint = $useCdn ? $cdnEndpoint : $endpoint;

    //         $filePath = 'matchme/' . env('APP_ENV', 'local') . '/uploads/' . $filename;

    //         if ($s3->exists($filePath)) {
    //             $s3->delete($filePath);
    //         }
    //     }
    // }

    public static function deleteFile($url)
    {
        if ($url != null) {
            // Parse the URL to extract the path
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? null;

            if ($path) {
                // Remove leading slash from the path
                $path = ltrim($path, '/');

                // Initialize S3 storage
                $s3 = Storage::disk('s3');

                // Check if the file exists in S3/Spaces
                if ($s3->exists($path)) {
                    $s3->delete($path);
                    Log::info("File deleted successfully: " . $path);
                } else {
                    Log::error("File does not exist in S3: " . $path);
                }
            } else {
                Log::error("Invalid URL provided: " . $url);
            }
        }
    }


    public static function saveFileAndGivePath($file)
    {
        if ($file != null) {
            $mockRequest = new \Illuminate\Http\Request();
            $mockRequest->files->set('file', $file);
            $path = self::uploadFileToS3($mockRequest, 'file');
            // $path = $file->store('uploads');
            return $path;
        } else {
            return null;
        }
    }

    public static function minusCoinsFromWallet($user_id, $amount, $transaction_type = 0, $walletTagId = 2)
    {
        /**
         * $transaction_type 
         * 2 = deduct
         * 3 = withdraw
         * ------------------
         * $walletTagId
         * #default 2 = watch_live 
         * อ้างอิง id จากตาราง wallet_tags
         */

        try {
            // ค้นหาผู้ใช้จาก user_id
            $user = Users::where('id', $user_id)->first();
            if ($user == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found!',
                ], 404); // ส่ง HTTP status code 404 (Not Found)
            }

            // ตรวจสอบว่าจำนวนเงินใน wallet พอหรือไม่
            if ($user->wallet < $amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Not enough coins in the wallet!',
                    'wallet' => $user->wallet,
                ], 400); // ส่ง HTTP status code 400 (Bad Request)
            }

            // ลดจำนวนเงินใน wallet
            $user->wallet = $user->wallet - $amount;
            if ($user->save()) {
                // สร้างรายการ transaction
                $walletTransaction = new WalletTransactions();
                $walletTransaction->user_id = $user_id;
                $walletTransaction->wallet_tag_id = $walletTagId;
                $walletTransaction->amount = $amount;
                $walletTransaction->transaction_type = $transaction_type;
                $walletTransaction->balance_after_transaction = $user->wallet;
                $walletTransaction->save();

                Log::info('Minus coin success. Amount: ' . $amount . ', Transaction type: ' . $transaction_type . ', Balance after transaction: ' . $user->wallet . ', Transaction ID: ' . $walletTransaction->id);

                // ส่งผลลัพธ์สำเร็จกลับ
                return response()->json([
                    'status' => true,
                    'message' => 'Coins deducted successfully.',
                    'user' => $user,
                    'transaction' => $walletTransaction->load('walletTag'),
                ], 200); // ส่ง HTTP status code 200 (OK)
            }
        } catch (\Throwable $e) {
            Log::error('Something went wrong! : ' . $e->getMessage());

            // ส่งข้อความผิดพลาดกลับ
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! : ' . $e->getMessage(),
            ], 500); // ส่ง HTTP status code 500 (Internal Server Error)
        }
    }

    public static function addCoinsToWallet($user_id, $amount, $transaction_type = 0, $walletTagId = 1)
    {
        /**
         * $transaction_type 
         * 1 = add
         * ------------------
         * $walletTagId
         * #default 1 = top_up
         * อ้างอิง id จากตาราง wallet_tags
         */
        try {
            // ค้นหาผู้ใช้จาก user_id
            $user = Users::where('id', $user_id)->first();
            if ($user == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found!',
                ], 404); // ส่ง HTTP status code 404 (Not Found)
            }

            // เพิ่มจำนวน coin ใน wallet
            $user->wallet = $user->wallet + $amount;
            if ($user->save()) {
                // สร้างรายการ transaction
                $walletTransaction = new WalletTransactions();
                $walletTransaction->user_id = $user_id;
                $walletTransaction->wallet_tag_id = $walletTagId;
                $walletTransaction->amount = $amount;
                $walletTransaction->transaction_type = $transaction_type;
                $walletTransaction->balance_after_transaction = $user->wallet;
                $walletTransaction->save();

                Log::info('Add coin success. Amount: ' . $amount . ', Transaction type: ' . $transaction_type . ', Balance after transaction: ' . $user->wallet . ', Transaction ID: ' . $walletTransaction->id);

                // ส่งผลลัพธ์สำเร็จกลับ
                return response()->json([
                    'status' => true,
                    'message' => 'Coins deducted successfully.',
                    'user' => $user,
                    'transaction' => $walletTransaction->load('walletTag'),
                ], 200); // ส่ง HTTP status code 200 (OK)
            }
        } catch (\Exception $e) {
            Log::error('Something went wrong! : ' . $e->getMessage());

            // ส่งข้อความผิดพลาดกลับ
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong! : ' . $e->getMessage(),
            ], 500); // ส่ง HTTP status code 500 (Internal Server Error)
        }
    }
}
