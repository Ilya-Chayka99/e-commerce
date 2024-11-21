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
use Illuminate\Support\Facades\DB;

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

        $rentStartTime = Carbon::parse($validatedData['rent_start_time'])->addHours(4);
        $rentEndTime = Carbon::parse($validatedData['rent_end_time'])->addHours(4);

        $existingRentals = ComputerRental::where('computer_id', $validatedData['computer_id'])->get();

        foreach ($existingRentals as $rental) {
            $existingRentStartTime = Carbon::parse($rental->rent_time);
            $existingRentEndTime = $existingRentStartTime->copy()->addMinutes($rental->minutes);
            // Если новая аренда начинается во время уже существующей аренды
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime) {
                return response()->json(['message' => 'Computer is already rented during the selected start time'], 200);
            }

            // Если новая аренда заканчивается во время уже существующей аренды
            if ($rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime) {
                return response()->json(['message' => 'Computer is already rented during the selected end time'], 200);
            }

            // Если новая аренда полностью покрывает уже существующую
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime) {
                return response()->json(['message' => 'Computer is already rented during the entire selected period'], 200);
            }
        }



        $rentTime = Carbon::parse($validatedData['rent_start_time']);
        $tariff = Tarif::where('from', '<=', $rentTime->format('H:i:s'))
            ->where('to', '>=', $rentTime->format('H:i:s'))
            ->first();
        $minutesDifference = $rentTime->diffInMinutes(Carbon::parse($validatedData['rent_end_time']));
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
        $rent = Carbon::parse($validatedData['rent_start_time'])->addHours(4);
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
