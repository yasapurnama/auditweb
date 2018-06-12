<?php

namespace App\Http\Controllers;

use PDF;
use App\User;
use App\Download;
use App\AuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['download']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, $result)
    {
        if(Auth::check())
        {
            $audit_results = Auth::user()->audit_result->find($result);            
        }
        else if($request->has('token')){
            $download = Download::where('token',request('token'))->first();
            if(isset($download)){
                $audit_results = $download->audit_result()->first();
            }
            else{
                return abort(404);
            }
        }
        else{
            return abort(404);
        }
        return view('download', compact('audit_results')); //for debuging perpose
        $pdf = PDF::loadView('download', compact('audit_results'));
        $created_at = $audit_results->created_at->format('dmY_Hi');
        return $pdf->download('audit_result_'.$created_at.'.pdf');
    }

    public function generate_token(AuditResult $auditresult)
    {
        $random = str_random(10);
        $created_at = $auditresult->created_at;
        $token = sha1(Auth::user()->id.$auditresult->id.$created_at.$random);
        $download = Download::create([
            'user_id' => Auth::user()->id,
            'audit_result_id' => $auditresult->id,
            'token' => $token,
            'created_at' => $created_at
        ]);
        return $download->exists;
    }
}
