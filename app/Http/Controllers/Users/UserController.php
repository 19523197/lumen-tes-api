<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        $allUser = User::all();
        if (!$allUser) {
            return response()->json(['message' => 'error'], 400);
        }

        return response()->json(['user' => $allUser], 200);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'required|alpha|regex:/^[\pL\s\-]+$/u'
        ]);
        if ($checkUser = User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email sudah terdaftar'], 400);
        }
        $newUser = User::create($request->all());
        if (!$newUser) {
            return response()->json(['message' => 'error'], 400);
        }

        return response()->json(['message' => 'berhasil membuat user', 'user' => $newUser], 200);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::where("email", $request->email)->first();
        if (!$user) {
            return response()->json(['message' => "login gagal"], 406);
        }
        if (Hash::check($request->password, $user->password)) {
            $token = Auth::attempt($request->only('email', 'password'));
            dd($token);
            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Auth::factory()->getTTL() * 60
            ], 400);
        }
        return  response()->json(['message' => "login gagal"], 404);
    }
    //
}
