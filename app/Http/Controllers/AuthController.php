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
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        //if (! $user || ! md5($password) == $user->password) {
        // return response()->json(['a' => strtoupper(md5($password)), 'b' => $user->password]);

        if (!$user || !Hash::check('password', $user->password)) {
            throw ValidationException::withMessages([
                'user' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($email);

        return response()->json([
            'ok' => true,
            'token' => $token->plainTextToken,
            'user' => $user
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
