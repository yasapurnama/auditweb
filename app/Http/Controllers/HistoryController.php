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
        $auditResults = AuditResult::latest()->paginate(10);
        return view('manage.history', compact('auditResults'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function show(AuditResult $auditResult)
    {
        $audit_results = $auditResult->get()->first();
        $download = Download::where('audit_result_id',$audit_results->id)->first();
        $token = $download->token;
        return view('manage.history_show', compact('audit_results','token'));
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
        $result = AuditResult::findOrFail($data_id);
        if($result){
            $result->delete();
            return redirect()->route('manage.history')->with('status', 'Audit result deleted!');
        }
        else{
            return redirect()->route('manage.history')->with('error', 'Delete audit result failed!');
        }
    }
}
