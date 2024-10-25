<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getAll()
    {
        $users =  User::with('userInfo')->get();
        return response()->json($users);
    }

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
            $userInfo = UserInfo::create([
                'user_id' => $user->_id,
                'name' => $validatedData['user_info']['name'],
                'last_name' => $validatedData['user_info']['last_name'],
                'middle_name' => $validatedData['user_info']['middle_name'] ?? null,
                'birthday' => $validatedData['user_info']['birthday'] ?? null,
                'phone' => $validatedData['user_info']['phone'] ?? null,
            ]);
            DB::commit();
            return response()->json([
                'user' => $user,
                'user_info' => $userInfo,
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
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('login', $request->login)->first();

        if ($user && Hash::check($request->password, $user->password)) {
           // $token = $user->createToken('auth_token')->plainTextToken;
            $userInfo = UserInfo::where('user_id', $user->_id)->first();
            return response()->json([
                'user' => $user,
                'user_info' => $userInfo,
            ]);
        } else {
            return response()->json(['message' => 'Неверный login или пароль'], 401);
        }
    }

}
