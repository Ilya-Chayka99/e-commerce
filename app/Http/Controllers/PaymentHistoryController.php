<?php

namespace App\Http\Controllers;

use App\Models\PaymentHistory;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentHistoryController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function replenishment(Request $request)
    {
        $payment_type = $request->payment_type;
        $quantity = $request->quantity;
        $payment_hash = $request->payment_hash;
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        if($user->money + $quantity < 0){
            return response()->json(['message' => 'Error not enough money'], 200);
        }

        $client = new Client();
        $linkKey = $request->link;
        while (true){
            $response = $client->post( "http://89.111.131.40:8080/getInfoByPaymentLink?linkKey=$linkKey");
//                'query' => [
//                    'linkKey' => $request->link
//                ]
//            ]);
            $link = json_decode($response->getBody(), true);
            if($link['state'] == 'error' || $link['paymentStatus'] == -1) return response()->json(['message' => $link['message']], 200);
            if ($link['paymentStatus'] == 1) break;
            sleep(3);
        }


        $payment = PaymentHistory::create([
            'user_id' => $user->id,
            'payment_type' => $payment_type,
            'quantity' => $quantity,
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
            'payment_hash' => $payment_hash,
        ]);
        $user->money = $user->money + $quantity;
        $user->save();
        return response()->json(['money' => $user->money,'payment' => $payment], 200);

    }

    /**
     * @throws GuzzleException
     */
    public function getLink(Request $request)
    {
        $quantity = $request->quantity;
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        if($user->money + $quantity < 0){
            return response()->json(['message' => 'Error not enough money'], 200);
        }

        $client = new Client();
        $params = [
            'livingTime' =>  3000000000,
            'priceType' => 4,
            'price' => $quantity,
            'apikey' => 'UGsosAgysjQgWgVL90xWiwOcb1UFxp1yksWj6PErkXCFQ4mIvJUk08mLHwhGbKxsw66UwYztjScgQjHFWbhfrgvs5uMAvUNN6xyvjhnEa93o3dKF6lMtebHN2vJzK2XRahiamc9aoz9Lp6TLtOaakKPv8k6xMqmvfKZPvpozVw4aXdJLtL2p7bPn5sr0hAmTJV3b87UHNR4omNFC8xTVE9l3FEzAv6aSXr4fgYejLPkCGLD9ywUwwvqRH6iUEjX'
        ];
        $response = $client->get( 'http://89.111.131.40:8080/createApiPaymentLink', [
            'query' => $params
        ]);
        $link = json_decode($response->getBody(), true);
        return response()->json(['link' => $link], 200);

    }

    public function history(Request $request){
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        $paymentHistory = PaymentHistory::where('user_id', $user->id)->get();
        return response()->json($paymentHistory, 200);
    }
}
