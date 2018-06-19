<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::where('id', '<>', Auth::user()->id)->latest()->paginate(10);
        return view('manage.users', compact('users'));
    }

    /**
     * Display the specified resource.
     *
     * @param  $user
     * @return \Illuminate\Http\Response
     */
    public function show($user)
    {
        $user = User::findOrFail($user);
        return view('manage.user_profile', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($user)
    {
        $user = User::findOrFail($user);
        return view('manage.user_profileedit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $user)
    {
        $user = User::findOrFail($user);
        if($request->filled('password')){
            $validatedData = $request->validate([
                'name' => 'required|regex:/^[\pL\s\-]+$/u|string|max:255',
                'email' => 'required|string|email|max:255',
                'role' => 'required|digits_between:1,2',
                'status' => 'required|digits_between:0,1',
                'password' => 'required|string|min:8|confirmed'
            ]);
            $user->update([
                'name' => request('name'),
                'email' => request('email'),
                'role' => request('role'),
                'status' => request('status'),
                'password' => bcrypt(request('password')),
            ]);
        }
        else{
            $validatedData = $request->validate([
                'name' => 'required|regex:/^[\pL\s\-]+$/u|string|max:255',
                'email' => 'required|string|email|max:255',
                'role' => 'required|digits_between:1,2',
                'status' => 'required|digits_between:0,1',
            ]);
            $user->update([
                'name' => request('name'),
                'email' => request('email'),
                'role' => request('role'),
                'status' => request('status'),
            ]);
        }
        return redirect(route('manage.userview', $user))->with('message', 'Update profile success!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $validatedData = $request->validate([
            'data_id' => 'required|numeric'
        ]);

        $data_id = request('data_id');
        $user = User::findOrFail($data_id);
        if($user){
            $user->update([
                'status' => 0,
            ]);
            return redirect()->route('manage.users')->with('status', 'User disabled!');
        }
        else{
            return redirect()->route('manage.users')->with('error', 'Disable user failed!');
        }   
    }
}
