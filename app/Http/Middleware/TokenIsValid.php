<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws GuzzleException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = new Client();
        $access_token = $request->access_token;
        $user = User::where('token',$access_token)->first();
        if (!$user){
            return response()->json(['message' => 'Error Authorization'], 200);
        }

        $userResponse = $client->post('https://api.vk.com/method/users.get', [
            'form_params' => [
                'access_token' => $access_token,
                'v' => '5.199',
                'fields' => 'id,first_name,last_name,photo_max,email,bdate,screen_name'
            ]
        ]);
        $userData = json_decode($userResponse->getBody(), true);

        if(isset($userData['error'])){
            return response()->json(['message' => 'Error Authorization'], 200);
        }
        if($userData['response'][0]['id'] != $user->vkID){
            return response()->json(['message' => 'Error Authorization'], 200);
        }
        $request['dataUser'] = $userData;
        return $next($request);
    }
}
