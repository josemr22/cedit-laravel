<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //
    public function index()
    {
        return response()->json(
            User::with('roles')->orderByDesc('created_at')->get()
        );
    }

    public function show(User $user)
    {
        $user = User::with('roles')->find($user->id);
        return response()->json(
            $user
        );
    }

    public function getRoles()
    {
        return response()->json(
            Role::get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'user' => 'required | unique:users',
            'password' => 'required',
            'role_id' => 'required',
        ]);

        $user = new User();

        $user->name = $data['name'];
        $user->user = $data['user'];
        $user->assignRole($data['role']);
        $user->password = bcrypt($data['password']);
        $user->save();

        return response()->json($user);
    }

    public function update(User $user, Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'user' => ["required", Rule::unique('users')->ignore($user->user, 'user')],
            'password' => 'nullable',
            'role' => 'required',
        ]);

        $user->name = $data['name'];
        $user->user = $data['user'];

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
