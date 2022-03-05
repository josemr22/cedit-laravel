<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function index()
    {
        return response()->json(
            User::with('roles')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required | unique:users',
            'password' => 'required',
            'role' => 'required',
        ]);

        $user = new User();

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->assignRole($data['role']);
        $user->password = bcrypt($data['password']);
        $user->save();

        return response()->json($user);
    }

    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required | unique:users',
            'password' => 'nullable',
            'role' => 'required',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        $user->assignRole($data['role']);

        if (isset($data['password'])) {
            $user->password = bcrypt($data['password']);
        }
        $user->save();

        return response()->json($user);
    }

    public function delete(User $user)
    {
        $user->delete();

        return response()->json();
    }
}
