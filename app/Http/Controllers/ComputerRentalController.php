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
            'rent_time' => 'required',
            'minutes' => 'required|integer|min:1',
            'access_token' => 'required'
        ]);

        $client = new Client();
        $user = User::where('token',$validatedData['access_token'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 401);
        }
        $userResponse = $client->post('https://api.vk.com/method/users.get', [
            'form_params' => [
                'access_token' => $validatedData['access_token'],
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
        $computer = Computer::with('metadata')->findOrFail($validatedData['computer_id']);
        $computerPrice = $computer->metadata->price;

        $rentTime = $validatedData['rent_time'];

        $tariff = Tarif::whereTime('from', '<=', $rentTime)
            ->whereTime('to', '>=', $rentTime)
            ->first();

        if (!$tariff) {
            $endPrice = $computerPrice * 1 * $validatedData['minutes'] ;
        }else{
            $endPrice = $computerPrice * $tariff->coefficient * $validatedData['minutes'];
        }

        if($user->money < $endPrice){
            return response()->json(['message' => 'no money'], 200);
        }
        $user->money = $user->money - $endPrice;
        $user->save();
        $payment = PaymentHistory::create([
            'user_id' => $user->_id,
            'payment_type' => "rentall",
            'quantity' => -$endPrice,
            'payment_date' => Carbon::now()->setTimezone('Europe/Saratov'),
        ]);
        $rent = Carbon::parse($validatedData['rent_time'])->setTimezone('Europe/Saratov');
        $rent = $rent->subHours(4);
        $computerRental = ComputerRental::create([
            'computer_id' => $validatedData['computer_id'],
            'user_id' => $user->id,
            'rent_time' => $rent,
            'minutes' => $validatedData['minutes'],
            'end_price' => $endPrice,
        ]);

        return response()->json([
            'message' => 'good',
        ]);
    }
}
