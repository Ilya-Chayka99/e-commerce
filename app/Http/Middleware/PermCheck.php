<?php

namespace App\Http\Middleware;

use App\Models\Perm;
use App\Models\PermAdjacent;
use App\Models\User;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @throws GuzzleException
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = User::where('vkID',$request['dataUser']['response'][0]['id'])->first();

        $permAdjacentRecords = PermAdjacent::where('user_id', $user->id)->get();
        $flag =false;
        foreach ($permAdjacentRecords as $permAdjacent) {
            $permission = Perm::find($permAdjacent->perm_id);

            if ($permission && $permission->name == 'admin') {
               $flag = true;
            }
        }
        if ($flag) return $next($request);
        return response()->json(['message' => 'Error not perm'], 200);
    }
}
