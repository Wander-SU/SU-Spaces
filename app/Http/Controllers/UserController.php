<?php

namespace App\Http\Controllers;

// use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('userManagement.view');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // Won't be used
    // public function store(StoreuserRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(building $building)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(building $building)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // Won't be used
    // public function update(UpdateuserRequest $request, building $building)
    // {
    //     //
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(building $building)
    {
        //
    }
}