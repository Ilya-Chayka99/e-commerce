<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentHistoryController extends Controller
{
    public function replenishment(Request $request)
    {
        $payment_type = $request->payment_type;
        $quantity = $request->quantity;
        $access_token = $request->access_token;
        $payment_hash = $request->payment_hash;
        $client = new Client();
        $user = User::where('token',$access_token)->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 401);
        }
        $userResponse = $client->post('https://api.vk.com/method/users.get', [
            'form_params' => [
                'access_token' => $access_token,
                'v' => '5.199',
                'fields' => 'id,first_name'
            ]
        ]);
        $userData = json_decode($userResponse->getBody(), true);
        if(isset($userData['error'])){
            if($userData['error']['error_code'] == 5){
                return response()->json(['message' => 'Authorization error'], 401);
            }
            return response()->json(['message' => 'Error receiving information'], 500);
        }
        if($userData['response'][0]['id'] != $user->vkID){
            return response()->json(['message' => 'Error receiving information'], 500);
        }
        try{
            $payment = PaymentHistory::create([
                'user_id' => $user->vkID,
                'payment_type' => $payment_type,
                'quantity' => $quantity,
                'payment_date' => Carbon::now(),
                'payment_hash' => $payment_hash,
            ]);
            $user->money = $user->money + $quantity;
            $user->save();
            return response()->json(['money' => $user->money], 200);
        }catch (\Exception $e){
            return response()->json(['message' => 'Error receiving information'], 500);
        }
    }
}
