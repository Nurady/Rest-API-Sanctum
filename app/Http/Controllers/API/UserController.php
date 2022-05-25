<?php

namespace App\Http\Controllers\API;

use App\Models\User;

use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\PasswordValidationRules;

class UserController extends Controller
{
    use PasswordValidationRules;
    
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ],'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ],'Authentication Failed', 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules()
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ],'User Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ],'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token,'Token Revoked');
    }

    public function getProfile(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data profile user berhasil diambil');
    }

    public function updateProfile(Request $request, User $user)
    {
        $this->validate($request, [
            'name'      => 'required',
            'email'     => 'required|email|unique:users,email,'.$user->id
        ]);

        $user = User::findOrFail($user->id);

        if($request->input('password') && $request->file('photo') == '') {
            $user->update([
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'password'  => bcrypt($request->input('password')),
                'address' => $request->address,
                'city' => $request->city,
            ]);
        }

        if ($request->input('password') == '' && $request->file('photo')) {
            $user = User::findOrFail($user->id);

            if ($user->photo) {
                Storage::disk('local')->delete('public/photoUser/'.$user->photo);
            }
            
            $photo = $request->file('photo');
            $photo->storeAs('public/photoUser', $photo->hashName());

            $user->update([
                'photo'     => $photo->hashName(),
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'address' => $request->address,
                'city' => $request->city,
            ]);
        }


        if ($request->input('password') == '' && $request->file('photo') == '') {
            $user->update([
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'address' => $request->address,
                'city' => $request->city,
            ]);
        }

        if ($request->input('password') && $request->file('photo')) {
            $user = User::findOrFail($user->id);

            if ($user->photo) {
                Storage::disk('local')->delete('public/photoUser/'.$user->photo);
            }
                        
            $photo = $request->file('photo');
            $photo->storeAs('public/photoUser', $photo->hashName());

            $user->update([
                'photo'     => $photo->hashName(),
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'password'  => bcrypt($request->input('password')),
                'address' => $request->address,
                'city' => $request->city,
            ]);
        }
        
        return response()->json([
            'response_code' => '00',
            'response_message' => 'data profile berhasil diupdate',
            'data' => $user,
        ], 200);  
    }
}
