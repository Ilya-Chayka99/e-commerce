<?php

namespace App\Http\Controllers;

use App\Models\Computer;
use App\Models\ComputerRental;
use App\Models\PaymentHistory;
use App\Models\Perm;
use App\Models\PermAdjacent;
use App\Models\Tarif;
use App\Models\User;
use Illuminate\Http\Request;

class ComputerRentalController extends Controller
{
    public function check(Request $request)
    {
        $validatedData = $request->validate([
            'computer_id' => 'required',
            'rent_start_time' => 'required',
            'rent_end_time' => 'required',
        ]);
        $computer = Computer::with('metadata')->findOrFail($validatedData['computer_id']);
        $computerPrice = $computer->metadata->price;

        $rentStartTime = strtotime($validatedData['rent_start_time']);
        $rentEndTime = strtotime($validatedData['rent_end_time']);
        $currentTime = strtotime('now');
        $existingRentals = ComputerRental::where('computer_id', $validatedData['computer_id'])->get();
        if($rentStartTime < $currentTime || $rentStartTime > $rentEndTime){
            return response()->json(['message' => 'The computer cannot be rented until the current time'], 200);
        }
        foreach ($existingRentals as $rental) {
            $existingRentStartTime = strtotime($rental->rent_time);
            $existingRentEndTime = strtotime($rental->rent_time) + ($rental->minutes * 60);

            // Если новая аренда начинается во время уже существующей аренды
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime&& $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the selected start time'], 200);
            }

            // Если новая аренда заканчивается во время уже существующей аренды
            if ($rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the selected end time'], 200);
            }

            // Если новая аренда полностью покрывает уже существующую
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the entire selected period'], 200);
            }
        }
//        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
//        $permAdjacentRecords = PermAdjacent::where('user_id', $user->id)->get();
//
//        $permissions = [];
//
//        foreach ($permAdjacentRecords as $permAdjacent) {
//            $permission = Perm::find($permAdjacent->perm_id);
//
//            if ($permission) {
//                $permissions[] = $permission;
//            }
//        }
//        $flag =false;
//        foreach ($permissions as $permAdjacent) {
//            $permission = Perm::find($permAdjacent->perm_id);
//
//            if ($permission && $permission->free_rentals) {
//                $flag = true;
//            }
//        }
//        if($flag){ return response()->json(['price' => 0], 200);}
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

        return response()->json(['price' => $endPrice], 200);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'computer_id' => 'required',
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
        $currentTime = strtotime('now');
        $existingRentals = ComputerRental::where('computer_id', $validatedData['computer_id'])->get();
        if($rentStartTime < $currentTime || $rentStartTime > $rentEndTime){
            return response()->json(['message' => 'The computer cannot be rented until the current time'], 200);
        }
        foreach ($existingRentals as $rental) {
            $existingRentStartTime = strtotime($rental->rent_time);
            $existingRentEndTime = strtotime($rental->rent_time) + ($rental->minutes * 60);

            // Если новая аренда начинается во время уже существующей аренды
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the selected start time'], 200);
            }

            // Если новая аренда заканчивается во время уже существующей аренды
            if ($rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the selected end time'], 200);
            }

            // Если новая аренда полностью покрывает уже существующую
            if ($rentStartTime >= $existingRentStartTime && $rentStartTime <= $existingRentEndTime && $rentEndTime >= $existingRentStartTime && $rentEndTime <= $existingRentEndTime && $rental->minutes !=0) {
                return response()->json(['message' => 'Computer is already rented during the entire selected period'], 200);
            }
        }
//        $permAdjacentRecords = PermAdjacent::where('user_id', $user->id)->get();
//        $flag =false;
//        foreach ($permAdjacentRecords as $permAdjacent) {
//            $permission = Perm::find($permAdjacent->perm_id);
//
//            if ($permission && $permission->free_rentals) {
//                $flag = true;
//            }
//        }
        $minutesDifference = (strtotime($validatedData['rent_end_time']) - strtotime($validatedData['rent_start_time'])) / 60;
//        if($flag){
//            $rent = date('Y-m-d H:i:s', strtotime($validatedData['rent_start_time']));
//            $computerRental = ComputerRental::create([
//                'computer_id' => $validatedData['computer_id'],
//                'user_id' => $user->id,
//                'rent_time' => $rent,
//                'minutes' => $minutesDifference,
//                'end_price' => 0,
//            ]);
//
//            return response()->json([
//                'message' => 'good',
//            ]);
//        }
        $rentStartTimeFormatted = sprintf('%02d:%02d:%02d', getdate($rentStartTime)['hours'], getdate($rentStartTime)['minutes'], getdate($rentStartTime)['seconds']);
        $tariff = Tarif::where('from', '<=', $rentStartTimeFormatted)
            ->where('to', '>=', $rentStartTimeFormatted)
            ->first();

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
            'user_id' => $user->id,
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
