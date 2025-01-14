<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::all();

        if ($users) {
            return $this->sendResponse($users, 'Get Users successfully.');
        } else {
            return $this->sendError('Failed.', ['error' => 'failed query'], 500);
        }
    }

    public function update(Request $request)
    {
        if (!json_decode(Auth::user()->scopes)->users->update) {
            return $this->sendError('You do not have permissions to access this resource.', ['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->id);

        if ($user) {

            $input = $request->all();



            $user->update($input);

            return $this->sendResponse($user, 'User register successfully.');
        } else {
            return $this->sendError('User dont not exist.', ['error' => 'Dont not exist'], 409);
        }
    }

    public function resetPassword(Request $request)
    {
        if (!json_decode(Auth::user()->scopes)->users->update) {
            return $this->sendError('You do not have permissions to access this resource.', ['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($request->id);

        if ($user) {

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user->update($input);

            return $this->sendResponse($user, 'User register successfully.');
        } else {
            return $this->sendError('User dont not exist.', ['error' => 'Dont not exist'], 409);
        }
    }

    public function delete(Request $request)
    {

        $user = User::find($request->id);

        if (!$user) {
            return $this->sendError('User not found.', ['reason' => 'User not found on database']);
        }

        $user->delete();

        return $this->sendResponse($user, 'Deleted User successfully.');
    }
}
