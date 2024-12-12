<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableRental;
use App\Models\PaymentHistory;
use App\Models\Perm;
use App\Models\PermAdjacent;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Http\Request;

class TableRentalController extends Controller
{
    public function check(Request $request)
    {
        $validatedData = $request->validate([
            'table_id' => 'required',
            'rent_start_time' => 'required',
            'rent_end_time' => 'required',
        ]);
        $table = Table::with('metadata')->findOrFail($validatedData['table_id']);
        $tablePrice = $table->metadata->price;

        $rentStartTime = strtotime($validatedData['rent_start_time']);
        $rentEndTime = strtotime($validatedData['rent_end_time']);
        $currentTime = strtotime('now');
        $existingRentals = TableRental::where('table_id', $validatedData['table_id'])->get();
        if($rentStartTime < $currentTime || $rentStartTime > $rentEndTime){
            return response()->json(['message' => 'Не корректное время'], 200);
        }
        foreach ($existingRentals as $rental) {
            $existingRentStartTime = strtotime($rental->rent_time);
            $existingRentEndTime = strtotime($rental->rent_time) + ($rental->minutes * 60);

            // Если новая аренда начинается во время уже существующей аренды
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол уже занят, выберите другое время.'], 200);
            }

            // Если новая аренда заканчивается во время уже существующей аренды
            if ($rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол занят, выберите другое время.'], 200);
            }

            // Если новая аренда полностью покрывает уже существующую
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол занят, выберите другое время.'], 200);
            }
        }

        $rentStartTimeFormatted = sprintf('%02d:%02d:%02d', getdate($rentStartTime)['hours'], getdate($rentStartTime)['minutes'], getdate($rentStartTime)['seconds']);
        $tariff = Tarif::where('from', '<=', $rentStartTimeFormatted)
            ->where('to', '>=', $rentStartTimeFormatted)
            ->first();
        $minutesDifference = (strtotime($validatedData['rent_end_time']) - strtotime($validatedData['rent_start_time'])) / 60;
        if (!$tariff) {
            $endPrice = $tablePrice * 1 * $minutesDifference ;
        }else{
            $endPrice = $tablePrice * $tariff->coefficient * $minutesDifference;
        }

        return response()->json(['price' => $endPrice], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'table_id' => 'required',
            'rent_start_time' => 'required',
            'rent_end_time' => 'required',
            'access_token' => 'required'
        ]);

        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }

        $table = Table::with('metadata')->findOrFail($validatedData['table_id']);
        $tablePrice = $table->metadata->price;

        $rentStartTime = strtotime($validatedData['rent_start_time']);
        $rentEndTime = strtotime($validatedData['rent_end_time']);
        $currentTime = strtotime('now');
        $existingRentals = TableRental::where('table_id', $validatedData['table_id'])->get();
        if($rentStartTime < $currentTime || $rentStartTime > $rentEndTime){
            return response()->json(['message' => 'Не корректное время'], 200);
        }
        foreach ($existingRentals as $rental) {
            $existingRentStartTime = strtotime($rental->rent_time);
            $existingRentEndTime = strtotime($rental->rent_time) + ($rental->minutes * 60);

            // Если новая аренда начинается во время уже существующей аренды
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол уже занят, выберите другое время.'], 200);
            }

            // Если новая аренда заканчивается во время уже существующей аренды
            if ($rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол занят, выберите другое время.'], 200);
            }

            // Если новая аренда полностью покрывает уже существующую
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Стол занят, выберите другое время.'], 200);
            }
        }

        $minutesDifference = (strtotime($validatedData['rent_end_time']) - strtotime($validatedData['rent_start_time'])) / 60;

        $rentStartTimeFormatted = sprintf('%02d:%02d:%02d', getdate($rentStartTime)['hours'], getdate($rentStartTime)['minutes'], getdate($rentStartTime)['seconds']);
        $tariff = Tarif::where('from', '<=', $rentStartTimeFormatted)
            ->where('to', '>=', $rentStartTimeFormatted)
            ->first();

        if (!$tariff) {
            $endPrice = $tablePrice * 1 * $minutesDifference ;
        }else{
            $endPrice = $tablePrice * $tariff->coefficient * $minutesDifference;
        }
        if($user->money < $endPrice){
            return response()->json(['message' => 'no money'], 200);
        }
        $user->money = $user->money - $endPrice;
        $user->save();
        $payment = PaymentHistory::create([
            'user_id' => $user->id,
            'payment_type' => "rental",
            'quantity' => -$endPrice,
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
        ]);
        $rent = date('Y-m-d H:i:s', strtotime($validatedData['rent_start_time']));
        $computerRental = TableRental::create([
            'table_id' => $validatedData['table_id'],
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

        $rental = TableRental::find($validatedData['rental_id']);
        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $rentStartTime = getdate(strtotime($rental->rent_time));

        $rentEndTime = getdate(strtotime($rental->rent_time) + ($rental->minutes *60));

        $currentTime = getdate(strtotime('now'));


        $rentStartUnix = mktime($rentStartTime['hours'], $rentStartTime['minutes'], 0, $rentStartTime['mon'], $rentStartTime['mday'], $rentStartTime['year']);

        $rentEndUnix = mktime($rentEndTime['hours'], $rentEndTime['minutes'], 0, $rentEndTime['mon'], $rentEndTime['mday'], $rentEndTime['year']);


        if (strtotime('now') > $rentEndUnix) {
            return response()->json(['message' => 'Rental has already ended'], 200);
        }

        $currentTime = mktime($currentTime['hours'], $currentTime['minutes'], 0, $currentTime['mon'], $currentTime['mday'], $currentTime['year']);
        $remainingTime = ($rentEndUnix - $currentTime) / 60;


        $endPrice = $rental->end_price;

        if($rentStartUnix > $currentTime) {
            $refundAmount = $endPrice / 2;
        } else{
            $remainingAmount = ($remainingTime / $rental->minutes) * $endPrice;

            $refundAmount = $remainingAmount / 2;
        }


        $user->money += $refundAmount;
        $user->save();
        $payment = PaymentHistory::create([
            'user_id' => $user->id,
            'payment_type' => "rental_refund",
            'quantity' => $refundAmount,
            'payment_date' => date('Y-m-d H:i:s', strtotime('now')),
        ]);
        if($rentStartUnix > $currentTime) {
            $rental->minutes = 0;
            $rental->end_price = $refundAmount;
        } else{
            $rental->minutes = ($currentTime - $rentStartUnix) / 60;
            $rental->end_price = $rental->end_price - $refundAmount;
        }
        $rental->save();
        return response()->json([
            'message' => 'Refund processed successfully',
            'money' => $user->money,
        ]);
    }
}
