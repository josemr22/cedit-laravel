<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        $user = $request->input('user');
        $password = $request->input('password');

        $userModel = User::where('user', $user)->first();

        if (!$userModel || (!Hash::check($password, $userModel->password))) {
            throw ValidationException::withMessages([
                'user' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $userModel->createToken($user);

        return response()->json([
            'ok' => true,
            'token' => $token->plainTextToken,
            'user' => $userModel
        ]);
    }

    public function getUser(Request $request)
    {
        $menu = request('menu');
        $user = $request->user();
        if (!$user->can($menu)) {
            abort(403);
        }
        $menuResp = array_map(function ($m, $k) use ($user) {
            $m['can'] = $user->can($k);
            return $m;
        }, Menu::getList(), array_keys(Menu::getList()));
        $resp = [
            'user' => $user->toArray(),
            'menu' => $menuResp
        ];
        return response()->json($resp);
    }
}
