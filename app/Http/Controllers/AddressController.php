<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = Address::where('user_id', auth()->id())->get();

        return response()->json($addresses, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'longitude'    => 'required|string|max:255',
            'latitude'     => 'required|string|max:255',
            'line1'        => 'required|string|max:255',
            'city'         => 'required|string|max:255',
            'country'      => 'required|string|max:255',
            'postal_code'  => 'required|string|max:20',
            'line2'        => 'nullable|string|max:255',
            'state'        => 'nullable|string|max:255',
        ]);

        $address = Address::create([
            'user_id'      => auth()->id(),
            'longitude'    => $request->longitude,
            'latitude'     => $request->latitude,
            'line1'        => $request->line1,
            'line2'        => $request->line2,
            'city'         => $request->city,
            'state'        => $request->state,
            'country'      => $request->country,
            'postal_code'  => $request->postal_code,
        ]);

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($address, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'longitude'    => 'required|string|max:255',
            'latitude'     => 'required|string|max:255',
            'line1'        => 'required|string|max:255',
            'city'         => 'required|string|max:255',
            'country'      => 'required|string|max:255',
            'postal_code'  => 'required|string|max:20',
            'line2'        => 'nullable|string|max:255',
            'state'        => 'nullable|string|max:255',
        ]);

        $address->update([
            'longitude'    => $request->longitude,
            'latitude'     => $request->latitude,
            'line1'        => $request->line1,
            'line2'        => $request->line2,
            'city'         => $request->city,
            'state'        => $request->state,
            'country'      => $request->country,
            'postal_code'  => $request->postal_code,
        ]);

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
        ], 200);
    }
}
