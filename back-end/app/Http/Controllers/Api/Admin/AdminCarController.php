<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\CarDeviceAssignment;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class AdminCarController extends Controller
{
    /**
     * Create a new Car and assign a GPS Device to it.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'marque' => 'required|string',
            'model' => 'required|string',
            'matricule' => 'required|string|unique:cars,matricule',
            'color' => 'nullable|string',
            'fuel_type' => 'required|in:diesel,gasoline,hybrid,electric',
            'mileage' => 'nullable|numeric',
            'device_id' => 'required|exists:devices,id',
        ]);

        return DB::transaction(function () use ($request) {

            // 1. Create the Car record
            $car = Car::create([
                'account_id' => $request->account_id,
                'marque' => $request->marque,
                'model' => $request->model,
                'matricule' => $request->matricule,
                'color' => $request->color,
                'fuel_type' => $request->fuel_type,
                'mileage' => $request->mileage ?? 0,
                'status' => 'active',
            ]);

            // 2. Unassign device from any previous car if active
            CarDeviceAssignment::where('device_id', $request->device_id)
                ->whereNull('removed_at')
                ->update(['removed_at' => now()]);

            // 3. Assign the GPS device to the new car
            $assignment = CarDeviceAssignment::create([
                'car_id' => $car->id,
                'device_id' => $request->device_id,
                'assigned_by' => auth()->id(), // Admin ID
                'installed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Car created and GPS device assigned successfully',
                'car' => $car->load('activeDeviceAssignment.device'),
            ], 201);
        });
    }

    /**
     * Change/Reassign GPS Device for an existing Car.
     */
    public function reassignDevice(Request $request, $carId)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
        ]);

        $car = Car::findOrFail($carId);

        return DB::transaction(function () use ($car, $request) {

            // Close active assignment for this car
            CarDeviceAssignment::where('car_id', $car->id)
                ->whereNull('removed_at')
                ->update(['removed_at' => now()]);

            // Close active assignment for the new device if used elsewhere
            CarDeviceAssignment::where('device_id', $request->device_id)
                ->whereNull('removed_at')
                ->update(['removed_at' => now()]);

            // Create new assignment history record
            CarDeviceAssignment::create([
                'car_id' => $car->id,
                'device_id' => $request->device_id,
                'assigned_by' => auth()->id(),
                'installed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Device reassigned to car successfully',
            ]);
        });
    }
}
