<?php

namespace App\Traits;

use App\Models\User;
use App\Traits\AdminGraphTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserAuthStoreRequest;

trait AdminAuthTrait
{
    use AdminGraphTrait;
    public function store(UserAuthStoreRequest $request)
    {
        return $this->safeCall(function () use ($request) {

            if (!Auth::user()->is_admin) {
                return $this->errorResponse('You are not authorized to perform this action.', 403);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('myToken')->accessToken;

            $cookie = cookie('access_token', $token, 60 * 24);

            return $this->successResponse(
                'User created successfully',
                ['token' => $token]
            )->cookie($cookie);
        });
    }

    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {

            if (!Auth::user()->is_admin) {
                return $this->errorResponse('You are not authorized to perform this action.', 403);
            }

            // Find the user by ID or throw an exception if not found
            $user = User::findOrFail($id);

            // Delete the user
            $user->delete();

            return $this->successResponse(
                'User deleted successfully',
                ['user' => $user]
            );
        });
    }

}
