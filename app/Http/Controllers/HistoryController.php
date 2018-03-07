<?php

namespace App\Http\Controllers;

use App\AuditResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
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
        $auditResults = AuditResult::->latest()->paginate(10);
        return view('history', compact('auditResults'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function show(AuditResult $auditResult)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function edit(AuditResult $auditResult)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AuditResult $auditResult)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\AuditResult  $auditResult
     * @return \Illuminate\Http\Response
     */
    public function destroy(AuditResult $auditResult)
    {
        //
    }
}
