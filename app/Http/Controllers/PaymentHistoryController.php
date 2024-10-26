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
            return response()->json(['message' => 'Error receiving information'], 200);
        }
        if($userData['response'][0]['id'] != $user->vkID){
            return response()->json(['message' => 'Error receiving information'], 200);
        }
        if($user->money + $quantity < 0){
            return response()->json(['message' => 'Error not enough money'], 200);
        }
        $payment = PaymentHistory::create([
            'user_id' => $user->_id,
            'payment_type' => $payment_type,
            'quantity' => $quantity,
            'payment_date' => Carbon::now(),
            'payment_hash' => $payment_hash,
        ]);
        $user->money = $user->money + $quantity;
        $user->save();
        return response()->json(['money' => $user->money], 200);

    }

    public function history(Request $request){

        $access_token = $request->access_token;
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
            return response()->json(['message' => 'Error receiving information'], 200);
        }
        if($userData['response'][0]['id'] != $user->vkID){
            return response()->json(['message' => 'Error receiving information'], 200);
        }
        $paymentHistory = PaymentHistory::where('user_id', $user->_id)->get();
        return response()->json($paymentHistory, 200);
    }
}
