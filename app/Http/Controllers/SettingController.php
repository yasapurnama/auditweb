<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application setting.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('setting');
    }

    /**
     * Edit the application setting.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'sendmail' => 'required|numeric|between:0,1',
            'notify' => 'required|numeric|between:0,1',
        ]);

        $user = Auth::user();
        $user->setting()->update([
            'sendmail' => request('sendmail'),
            'notify' => request('notify'),
        ]);

        return back()->with('message', 'Update setting success!');
    }
}
