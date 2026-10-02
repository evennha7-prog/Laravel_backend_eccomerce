<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index(): JsonResponse
    {
        $addresses = Address::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses,
        ], 200);
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
        ]);

        $address = Address::create([
            'user_id' => auth()->id(),
            'longitude' => $validated['longitude'],
            'latitude' => $validated['latitude'],
            'line1' => $validated['line1'],
            'line2' => $validated['line2'] ?? '',
            'city' => $validated['city'],
            'state' => $validated['state'] ?? '',
            'country' => $validated['country'],
            'postal_code' => $validated['postal_code'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'address' => $address,
        ], 201);
    }

    /**
     * Display the specified address.
     */
    public function show(Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'address' => $address,
        ], 200);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'longitude' => 'sometimes|numeric',
            'latitude' => 'sometimes|numeric',
            'line1' => 'sometimes|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'sometimes|string|max:255',
            'postal_code' => 'sometimes|string|max:20',
        ]);

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'address' => $address,
        ], 200);
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy(Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ], 200);
    }
}
