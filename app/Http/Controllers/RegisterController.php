<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if (User::where('email', $request->email)->exists()) {
            return $this->sendError('User already exist.', ['error' => 'Duplicated'], 409);
        }

        $scopes = [
            "scopes" => [
                "rol" => "customer",
                "subscription" => "free",
                "post" => [
                    "create" => false,
                    "read" => true,
                    "update" => false,
                    "delete" => false
                ],
                "globals_comments" => [
                    "create" => false,
                    "read" => true,
                    "update" => false,
                    "delete" => false
                ],
                "my_comments" => [
                    "create" => true,
                    "read" => true,
                    "update" => true,
                    "delete" => false
                ]
            ]
        ];

        $input = $request->all();
        $input['scopes'] = isset($request->scopes) ? json_encode($request->scopes) : json_encode($scopes);
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('ElSilbon')->plainTextToken;
        $success['name'] =  $user->name;
        $success['scopes'] =  json_decode($user->scopes);

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('ElSilbon')->plainTextToken;
            $success['name'] =  $user->name;
            $success['scopes'] =  json_decode(json_encode(json_decode($user->scopes)->scopes));

            return $this->sendResponse($success, 'User login successfully.');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse("Success", 'User logout successfully.');
    }
}
