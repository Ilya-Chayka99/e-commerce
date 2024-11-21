<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function authorization(Request $request)
    {
        $code = $request->code;
        $device_id = $request->device_id;

        if (!$code || !$device_id) {
            return response()->json(['message' => 'Authorization code and deviceID is required'], 200);
        }

        $client = new Client();
        $response = $client->post( 'https://id.vk.com/oauth2/auth', [
            'form_params' => [
                'grant_type' =>  'authorization_code',
                'code' => $code,
                'code_verifier' => 'aFAwoQihpVpTYqeRqoTiBNCdBEsiOHdZlomIXcWvOtmgiLMbFS',
                'client_id' => '52559174',
                'device_id' => $device_id,
                'redirect_uri' => 'http://localhost',
            ]
        ]);

        $vkData = json_decode($response->getBody(), true);
        if(isset($vkData['error'])) {
            return response()->json($vkData['error'], 401);
        }
        $access_token = $vkData['access_token'];
        $userID = $vkData['user_id'];

        $user = User::where('vkID',$userID)->first();

        if($user){
            $user->token = $access_token;
            $user->save();
        }else{
            $user = User::create([
                'vkID' => $userID,
                'token' => $access_token,
                'money' => 0,
            ]);
        }

        $userResponse = $client->post('https://api.vk.com/method/users.get', [
            'form_params' => [
                'access_token' => $access_token,
                'v' => '5.199',
                'fields' => 'id,first_name,last_name,photo_max,email,bdate,nickname'
            ]
        ]);
        $userData = json_decode($userResponse->getBody(), true);

        return response()->json(['data' => $userData,'access_token' => $access_token]);

    }

    public function getInfo(Request $request){
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        return response()->json(['data' => $request['dataUser'],'access_token' => $request['access_token'],'money' => $user->money]);
    }

    public function getRentalsActive(Request $request){
        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();
        if (!$user){
            return response()->json(['message' => 'Authorization error'], 200);
        }
        $activeOrUpcomingRentals = $user->activeOrUpcomingRentals();
        return response()->json(['data' => array_values($activeOrUpcomingRentals),'access_token' => $request['access_token']]);
    }

}
