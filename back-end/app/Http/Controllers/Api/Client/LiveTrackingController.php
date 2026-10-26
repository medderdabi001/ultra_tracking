<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
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

        // 1. Fetch cars belonging to the user's account
        if ($user->role === 'gerant') {
            $cars = Car::where('account_id', $user->account_id)
                ->with('activeDeviceAssignment.device')
                ->get();
        } elseif ($user->role === 'assistant') {
            $cars = $user->permittedCars()
                ->where('cars.account_id', $user->account_id)
                ->wherePivot('can_view', true)
                ->with('activeDeviceAssignment.device')
                ->get();
        } else {
            return response()->json([
                'message' => 'Unauthorized user role.',
            ], 403);
        }

        // 2. Extract valid Traccar Device IDs
        $traccarDeviceIds = [];

        foreach ($cars as $car) {
            $device = $car->activeDeviceAssignment?->device;

            if ($device && $device->traccar_device_id) {
                $traccarDeviceIds[] = $device->traccar_device_id;
            }
        }

        // Remove duplicate IDs
        $traccarDeviceIds = array_values(array_unique($traccarDeviceIds));

        // 3. Fetch positions from Traccar only for accessible devices
        $positions = empty($traccarDeviceIds)
            ? []
            : $this->traccarService->getPositions($traccarDeviceIds);

        // 4. Index positions by Traccar device ID
        $positionsByDeviceId = [];

        foreach ($positions as $pos) {
            if (isset($pos['deviceId'])) {
                $positionsByDeviceId[$pos['deviceId']] = $pos;
            }
        }

        // 5. Merge car data with live position data
        $formattedCars = $cars->map(function ($car) use ($positionsByDeviceId) {
            $device = $car->activeDeviceAssignment?->device;
            $traccarId = $device?->traccar_device_id;
            $liveData = $traccarId !== null
                ? ($positionsByDeviceId[$traccarId] ?? null)
                : null;

            return [
                'id' => $car->id,
                'marque' => $car->marque,
                'model' => $car->model,
                'matricule' => $car->matricule,
                'status' => $car->status,
                'device' => [
                    'imei' => $device?->imei,
                ],
                'telemetry' => $liveData ? [
                    'latitude' => $liveData['latitude'] ?? null,
                    'longitude' => $liveData['longitude'] ?? null,
                    'speed' => $liveData['speed'] ?? null,
                    'course' => $liveData['course'] ?? null,
                    'altitude' => $liveData['altitude'] ?? null,
                    'ignition' => $liveData['attributes']['ignition'] ?? false,
                    'device_time' => $liveData['deviceTime'] ?? null,
                ] : null,
            ];
        });

        return response()->json([
            'cars' => $formattedCars,
        ]);
    }
}
