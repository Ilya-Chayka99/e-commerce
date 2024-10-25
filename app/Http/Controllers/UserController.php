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

    public function create(Request $request)
    {

        $validatedData = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'money' => 'nullable|numeric',
            'user_info.name' => 'required|string|max:255',
            'user_info.last_name' => 'required|string|max:255',
            'user_info.middle_name' => 'nullable|string|max:255',
            'user_info.birthday' => 'nullable|date',
            'user_info.phone' => 'nullable|string|max:255',
        ]);
        DB::beginTransaction();
        try {

            $user = User::create([
                'login' => $validatedData['login'],
                'password' => bcrypt($validatedData['password']),
                'money' => $validatedData['money'] ?? 0,
            ]);
            DB::commit();
            return response()->json([
                'user' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating user and user info', 'error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,_id',
            'login' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'user_info' => 'sometimes|array',
            'user_info.name' => 'nullable|string|max:255',
            'user_info.last_name' => 'nullable|string|max:255',
            'user_info.middle_name' => 'nullable|string|max:255',
            'user_info.birthday' => 'nullable|date',
            'user_info.phone' => 'nullable|string|max:255',
        ]);

        $user = User::find($request->user_id);
        if ($request->filled('login')) $user->login = $request->login;
        if ($request->filled('password')) $user->password = Hash::make($request->password);
        $user->save();

        if ($request->has('user_info')) {
            $userInfo = UserInfo::where('user_id', $user->_id)->first();
            $userInfo->update($request->user_info);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function authorization(Request $request)
    {
        $code = $request->code;
        $device_id = $request->device_id;

        if (!$code) {
            return response()->json(['error' => 'Code is required'], 400);
        }


        $client = new Client();
        $response = $client->post( 'https://id.vk.com/oauth2/auth', [
            'form_params' => [
                'grant_type' =>  'authorization_code',
                'code' => $code,
                'code_verifier' => 'aFAwoQihpVpTYqeRqoTiBNCdBEsiOHdZlomIXcWvOtmgiLMbFS',
                'client_id' => '52559174',
                'device_id' => $device_id,
                'redirect_uri' => 'https://vk.com',
            ]
        ]);


        $vkData = json_decode($response->getBody(), true);

        return response()->json([$vkData , $device_id , $code]);


    }

}
