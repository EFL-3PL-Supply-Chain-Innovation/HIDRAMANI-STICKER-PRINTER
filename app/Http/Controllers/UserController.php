<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\User;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    //register controller
   public function create()
    {
        // Return the registration view
        return view('create');
    }


    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create a new user instance
        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Return a response or redirect
        return redirect()->back()->with('success', 'User created successfully!');

    }
    //login controller
 public function login(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    if (auth()->attempt(['email' => $request->email, 'password' => $request->password])) {
        $user = auth()->user();

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    return response()->json(['message' => 'Invalid credentials'], 401);
}


    //logout controller
    public function logout(Request $request)
    {
        // Log out the user
        auth()->logout();

        // Return a response
        return response()->json(['message' => 'User logged out successfully'], 200);
    }


    public function index()
    {
        return view('login');
    }




   public function postLogin(Request $request)
{
    $request->validate([
        'email' => 'required',
        'password' => 'required',
    ]);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        if (!$user) {
            Auth::logout();
            return redirect("login")->withErrors(['Session expired. Please login again.']);
        }


        return redirect()->route('operate.excel')
                ->withSuccess('You have Successfully logged in');
    }

    return redirect("login")->withErrors(['Oops! You have entered invalid credentials.']);
}
}
