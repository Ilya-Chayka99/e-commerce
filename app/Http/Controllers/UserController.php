<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function getAll()
    {
        $users =  User::with('userInfo')->get();
        return response()->json($users);
    }

    public function create(Request $request)
    {
        // Валидация данных запроса
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

        try {

            $user = User::create([
                'login' => $validatedData['login'],
                'password' => bcrypt($validatedData['password']),
                'money' => $validatedData['money'] ?? 0,
            ]);
            $userInfo = UserInfo::create([
                'user_id' => $user->_id, // Привязываем созданного пользователя
                'name' => $validatedData['user_info']['name'],
                'last_name' => $validatedData['user_info']['last_name'],
                'middle_name' => $validatedData['user_info']['middle_name'] ?? null,
                'birthday' => $validatedData['user_info']['birthday'] ?? null,
                'phone' => $validatedData['user_info']['phone'] ?? null,
            ]);
            // Возвращаем созданного пользователя с информацией о нём
            return response()->json([
                'user' => $user,
                'user_info' => $userInfo,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating user and user info', 'error' => $e->getMessage()], 500);
        }
    }

    public function getById($id)
    {
//        $user = User::find($id);
//        if (!$user) {
//            return response()->json(['message' => 'User not found'], 404);
//        }
//        return response()->json($user);
    }

}
