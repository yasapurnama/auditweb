<?php

namespace App\Http\Controllers;

use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = Auth::user()->notification()->latest()->paginate(10);
        return view('notification', compact('notifications'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $notifications = Auth::user()->notification()->latest()->get();
        return $notifications;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function update()
    {
        Notification::where('user_id', Auth::user()->id)->update(['readed' => true]);
        $notifications = Notification::where('user_id', Auth::user()->id)->get();
        return $notifications->map(function($notif){
            return [
                'id' => $notif->id,
                'notif_message' => $notif->notif_message,
                'readed' => $notif->readed,
                'owner_report' => $notif->owner_report,
                'created_at' => $notif->created_at->diffForHumans()
            ];
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Notification  $notification
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'data_id' => 'required|numeric'
        ]);

        $data_id = request('data_id');
        $result = Auth::user()->notification()->find($data_id);
        if($result){
            $result->delete();
            return redirect()->route('notification')->with('status', 'Notification deleted!');
        }
        else{
            return redirect()->route('notification')->with('error', 'Delete notification failed!');
        }
    }
}
