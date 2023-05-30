<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function registerUser(Request $request){
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'dob' => 'required|date_format:d-m-Y',
                'phone' => 'required|unique:users,phone',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'password' => Hash::make($request->password)
            ]);
            if($user){
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'message' => 'User Created Successfully',
                    'data' => $user,
                    'token' => $user->createToken("authToken")->plainTextToken
                ], 200);
            }
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Something went wrong',
                'data' => [],
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request){
        try {
            $validateUser = Validator::make($request->all(), 
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'validation error',
                    'data' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'success' => false,
                    'code' => 401,
                    'message' => 'Email & Password does not match with our record.',
                    'data' => [],
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'success' => true,
                'code' => 200,
                'message' => 'User Logged In Successfully',
                'data' => $user,
                'token' => $user->createToken("authToken")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get The User Profile
     * @param Request $request
     * @return User
     */
    public function profileUser(Request $request){
        try {
            $user = $request->user();
            if($user){
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'message' => 'User Details',
                    'data' => $user,
                ], 200);
            }
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'No user found',
                'data' => [],
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'code' => 500,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
