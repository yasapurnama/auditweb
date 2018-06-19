<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('profile');
    }

    /**
     * Edit the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return view('profile_edit');
    }

    /**
     * Edit the application profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if($request->filled('password')){
            $validatedData = $request->validate([
                'name' => 'required|regex:/^[\pL\s\-]+$/u|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.Auth::user()->id,
                'password' => 'required|string|min:8|confirmed'
            ]);
            $user->update([
                'name' => request('name'),
                'email' => request('email'),
                'password' => bcrypt(request('password')),
            ]);
        }
        else{
            $validatedData = $request->validate([
                'name' => 'required|regex:/^[\pL\s\-]+$/u|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,'.Auth::user()->id,
            ]);
            $user->update([
                'name' => request('name'),
                'email' => request('email'),
            ]);
        }

        return redirect(route('profile'))->with('message', 'Update profile success!');
    }
}
