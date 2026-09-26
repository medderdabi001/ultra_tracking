<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TraccarApiService;

class LiveTrackingController extends Controller
{
    protected TraccarApiService $traccarService;

    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    /**
     * Get accessible cars with their live Traccar positions.
     */
    public function getLiveVehicles(Request $request)
    {
        $user = $request->user();

        // 1. Fetch cars based on User Role
        if ($user->role === 'gerant') {
            $cars = $user->account->cars()->with('activeDeviceAssignment.device')->get();
        } else {
            // Assistant Role
            $cars = $user->permittedCars()
                ->wherePivot('can_view', true)
                ->with('activeDeviceAssignment.device')
                ->get();
        }

        // 2. Extract Traccar Device IDs
        $traccarDeviceIds = [];
        foreach ($cars as $car) {
            $activeAssignment = $car->activeDeviceAssignment;
            if ($activeAssignment && $activeAssignment->device) {
                $traccarDeviceIds[] = $activeAssignment->device->traccar_device_id;
            }
        }

        // 3. Fetch Positions from Traccar API
        $positions = $this->traccarService->getPositions($traccarDeviceIds);

        // Index positions by deviceId for quick lookup
        $positionsByDeviceId = [];
        foreach ($positions as $pos) {
            $positionsByDeviceId[$pos['deviceId']] = $pos;
        }

        // 4. Merge Car Data with Live Traccar Position Data
        $formattedCars = $cars->map(function ($car) use ($positionsByDeviceId) {
            $traccarId = $car->activeDeviceAssignment->device->traccar_device_id ?? null;
            $liveData = $positionsByDeviceId[$traccarId] ?? null;

            return [
                'id' => $car->id,
                'marque' => $car->marque,
                'model' => $car->model,
                'matricule' => $car->matricule,
                'status' => $car->status,
                'device' => [
                    'imei' => $car->activeDeviceAssignment->device->imei ?? null,
                ],
                'telemetry' => $liveData ? [
                    'latitude' => $liveData['latitude'],
                    'longitude' => $liveData['longitude'],
                    'speed' => $liveData['speed'], // in knots/kmh depending on Traccar config
                    'course' => $liveData['course'],
                    'altitude' => $liveData['altitude'],
                    'ignition' => $liveData['attributes']['ignition'] ?? false,
                    'device_time' => $liveData['deviceTime'],
                ] : null,
            ];
        });

        return response()->json([
            'cars' => $formattedCars
        ]);
    }
}
