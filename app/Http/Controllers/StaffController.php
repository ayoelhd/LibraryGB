<?php

namespace App\Http\Controllers;

use App\Http\Requests\StaffStoreRequest;
use App\Http\Requests\StaffUpdateRequest;
use App\Http\Resources\StaffResource;
use App\Models\Staff;

class StaffController extends Controller
{
    public function index()
    {
        return StaffResource::collection(Staff::all());
    }

    public function store(StaffStoreRequest $request)
    {
        $staff = Staff::create($request->validated());

        return StaffResource::make($staff)->response()->setStatusCode(201);
    }

    public function show($id)
    {
        return StaffResource::make(Staff::findOrFail($id));
    }

    public function update(StaffUpdateRequest $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $staff->update($request->validated());

        return StaffResource::make($staff);
    }

    public function destroy($id)
    {
        Staff::findOrFail($id)->delete();

        return response()->json('Staff member deleted successfully.');
    }
}
