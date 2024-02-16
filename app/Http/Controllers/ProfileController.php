<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\PasswordReset;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function addUser(Request $request) {
        $user = new User();
        $user->name=$request->name; 
        $user->email=$request->email; 
        $user->password=$request->password;

        $user->save();
        
        return response()->json('Successfully Added');
}

public function editUser(Request $request) {
    $user = User::findorfail($request->id);

    $user->name=$request->name; 
    $user->email=$request->email; 
    $user->password=$request->password;
    
    $user->update();

    return response()->json('Updated Successfully');
}

public function deleteUser(Request $request) {
    $user = user::findorfail($request->id)->delete();

    return response()->json('User Deleted Successfully');
}

public function getUser() {
    $users = User::all();

    return response()->json($users);
    }

    public function registration(Request $request){
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|same:password'
        ]);
        if ($validator->fails()){
            return response() -> json([
                'message'=>'Registration failed',
                'errors' => $validator->errors()
            ],422);
        }
        
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> Hash::make($request->password),

        ]);

        return response() -> json([
            'message'=>'Registration successful',
            'data' => $user
        ],200);

    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()){
            return response() -> json([
                'message'=>'Login failed',
                'errors' => $validator->errors()
            ],422);
        }

        $user = User::where('email', $request->email)->first();

        if($user){
            if(Hash::check($request->password, $user->password)){
                $token=$user->createToken("auth-token")->plainTextToken;
                return response() -> json([
                    'message'=>'Login successful',
                    'token'=>$token,
                    'data'=>$user
                ],200);
            }else{
                return response() -> json([
                    'message'=>'Invalid Credential'
                ],400);
            }
        }else{
            return response() -> json([
                'message'=>'Invalid Credential'
            ],400);
        }
    }

    public function userFetch(Request $request) {
        return response()->json([
            'message' => 'User Successfully Fetched',
            'data' => $request->user()
        ], 200);
    }

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }

    
    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|same:password',
            'token' => 'sometimes',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Password reset failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = $request->filled('token') ? $request->token : Str::random(60);

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        event(new PasswordReset($user));

        return response()->json(['message' => 'Password reset successful'], 200);
    }
}

