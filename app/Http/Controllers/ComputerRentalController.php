<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\ComputerRental;
use App\Models\PaymentHistory;
use App\Models\Tarif;
use App\Models\User;
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

        $rentStartTime = strtotime($validatedData['rent_start_time']);
        $rentEndTime = strtotime($validatedData['rent_end_time']);
        $existingRentals = ComputerRental::where('computer_id', $validatedData['computer_id'])->get();

        foreach ($existingRentals as $rental) {
            $existingRentStartTime = strtotime($rental->rent_time);
            $existingRentEndTime = strtotime($rental->rent_time) + ($rental->minutes * 60);

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

        $rentStartTimeFormatted = sprintf('%02d:%02d:%02d', getdate($rentStartTime)['hours'], getdate($rentStartTime)['minutes'], getdate($rentStartTime)['seconds']);
        $tariff = Tarif::where('from', '<=', $rentStartTimeFormatted)
            ->where('to', '>=', $rentStartTimeFormatted)
            ->first();
        $minutesDifference = (strtotime($validatedData['rent_end_time']) - strtotime($validatedData['rent_start_time'])) / 60;
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
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
        ]);
        $rent = date('Y-m-d H:i:s', strtotime($validatedData['rent_start_time']));
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

    public function cancelRental(Request $request)
    {

        $validatedData = $request->validate([
            'access_token' => 'required',
            'rental_id' => 'required',
        ]);

        $user = User::where('token', $validatedData['access_token'])->first();
        if (!$user) {
            return response()->json(['message' => 'User not found or invalid token'], 200);
        }

        $rental = ComputerRental::find($validatedData['rental_id']);
        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $rentStartTime = getdate(strtotime($rental->rent_time));

        $rentEndTime = getdate(strtotime($rental->rent_time) + $rental->minutes);

        $currentTime = getdate(strtotime('now'));


        $rentStartUnix = mktime($rentStartTime['hours'], $rentStartTime['minutes'], 0, $rentStartTime['mon'], $rentStartTime['mday'], $rentStartTime['year']);

        $rentEndUnix = mktime($rentEndTime['hours'], $rentEndTime['minutes'], 0, $rentEndTime['mon'], $rentEndTime['mday'], $rentEndTime['year']);

        return  $rentEndTime['hours'];
        if ($currentTime > $rentEndUnix) {
            return response()->json(['message' => 'Rental has already ended'], 200);
        }


        $remainingTime = ($rentEndUnix - mktime($currentTime['hours'], $currentTime['minutes'], 0, $currentTime['mon'], $currentTime['mday'], $currentTime['year'])) / 60;


        $endPrice = $rental->end_price;


        $remainingAmount = ($remainingTime / $rental->minutes) * $endPrice;

        $refundAmount = $remainingAmount / 2;

        return $refundAmount;
        $user->money += $refundAmount;
        $user->save();

        // Создаем запись в истории платежей
        $payment = PaymentHistory::create([
            'user_id' => $user->_id,
            'payment_type' => "rental_refund",
            'quantity' => $refundAmount,
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
        ]);

        return response()->json([
            'message' => 'Refund processed successfully',
            'money' => $user->money,
        ]);
    }
}
