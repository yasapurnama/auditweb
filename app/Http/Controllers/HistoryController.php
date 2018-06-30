<?php

namespace App\Http\Controllers;

use App\Download;
use App\AuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::user()->role == 1){
            $auditResults = Auth::user()->audit_result()->latest()->paginate(6);
            return view('history', compact('auditResults'));
        }
        else{
            $auditResults = AuditResult::latest()->paginate(10);
            return view('manage.history', compact('auditResults'));  
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function show($result)
    {
        if(Auth::user()->role == 1){
            $audit_results = Auth::user()->audit_result()->findOrFail($result);
            return view('result', compact('audit_results'));
        }
        else{
            $audit_results = AuditResult::findOrFail($result);
            $download = Download::where('audit_result_id',$audit_results->id)->first();
            $token = $download->token;
            return view('manage.history_show', compact('audit_results','token'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'data_id' => 'required|numeric'
        ]);

        $data_id = request('data_id');
        
        if(Auth::user()->role == 1){
            $result = Auth::user()->audit_result()->find($data_id);
            if($result){
                $result->delete();
                return redirect()->route('history')->with('status', 'Audit result deleted!');
            }
            else{
                return redirect()->route('history')->with('error', 'Delete audit result failed!');
            }
        }
        else{
            $result = AuditResult::find($data_id);
            if($result){
                $result->delete();
                return redirect()->route('manage.history')->with('status', 'Audit result deleted!');
            }
            else{
                return redirect()->route('manage.history')->with('error', 'Delete audit result failed!');
            }
        }
    }
}
