<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\ComputerRental;
use App\Models\PaymentHistory;
use App\Models\Tarif;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ComputerRentalController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'computer_id' => 'required|exists:computers,id',
            'rent_start_time' => 'required',
            'rent_end_time' => 'required',
            'access_token' => 'required'
        ]);

        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }

        $computer = Computer::with('metadata')->findOrFail($validatedData['computer_id']);
        $computerPrice = $computer->metadata->price;

        $rentTime = Carbon::parse($validatedData['rent_start_time'])->setTimezone('Europe/Saratov')->addHours(4);
        $tariff = Tarif::whereTime('from', '<=', $rentTime)
            ->whereTime('to', '>=', $rentTime)
            ->first();
        $minutesDifference = $rentTime->diffInMinutes(Carbon::parse($validatedData['rent_end_time'])->setTimezone('Europe/Saratov')->addHours(4));
        if (!$tariff) {
            $endPrice = $computerPrice * 1 * $minutesDifference ;
        }else{
            $endPrice = $computerPrice * $tariff->coefficient * $minutesDifference;
        }
        if($user->money < $endPrice){
            return response()->json(['message' => 'no money'], 200);
        }
        $user->money = $user->money - $endPrice;
        $user->save();
        $payment = PaymentHistory::create([
            'user_id' => $user->_id,
            'payment_type' => "rental",
            'quantity' => -$endPrice,
            'payment_date' => Carbon::now()->addHours(4),
        ]);
        $rent = Carbon::parse($validatedData['rent_start_time'])->setTimezone('Europe/Saratov');
        $computerRental = ComputerRental::create([
            'computer_id' => $validatedData['computer_id'],
            'user_id' => $user->id,
            'rent_time' => $rent,
            'minutes' => $minutesDifference,
            'end_price' => $endPrice,
        ]);

        return response()->json([
            'message' => 'good',
        ]);
    }
}
