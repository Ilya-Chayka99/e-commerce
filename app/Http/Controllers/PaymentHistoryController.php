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
        $payment_hash = $request->payment_hash;
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        if($user->money + $quantity < 0){
            return response()->json(['message' => 'Error not enough money'], 200);
        }
        $payment = PaymentHistory::create([
            'user_id' => $user->_id,
            'payment_type' => $payment_type,
            'quantity' => $quantity,
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
            'payment_hash' => $payment_hash,
        ]);
        $user->money = $user->money + $quantity;
        $user->save();
        return response()->json(['money' => $user->money,'payment' => $payment], 200);

    }

    public function history(Request $request){
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        $paymentHistory = PaymentHistory::where('user_id', $user->_id)->get();
        return response()->json($paymentHistory, 200);
    }
}
