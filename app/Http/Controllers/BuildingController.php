<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorebuildingRequest;
use App\Http\Requests\UpdatebuildingRequest;
use App\Models\Building;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('buildingNavigation.view');
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
    public function store(StorebuildingRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Building $building)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatebuildingRequest $request, Building $building)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Building $building)
    {
        //
    }
}
