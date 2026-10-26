<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\DeviceCommand;
use App\Services\TraccarApiService;

class CommandController extends Controller
{
    protected TraccarApiService $traccarService;

    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    /**
     * Send Engine Cut or Restore Command to GPS device.
     */
    public function sendEngineCommand(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:cars,id',
            'command_type' => 'required|in:cut_engine,restore_engine',
        ]);

        $user = $request->user();

        $car = Car::with('activeDeviceAssignment.device')
            ->findOrFail($request->car_id);

        // 1. Check that the car belongs to the user's account
        if ($car->account_id !== $user->account_id) {
            return response()->json([
                'message' => 'Unauthorized car access.',
            ], 403);
        }

        // 2. Check assistant's permission for this car
        if ($user->role === 'assistant') {
            $permission = $user->permittedCars()
                ->where('car_id', $car->id)
                ->first();

            if (!$permission || !$permission->pivot->can_cut_engine) {
                return response()->json([
                    'message' => 'Unauthorized! You do not have permission to control engine for this car.',
                ], 403);
            }
        }

        // 3. Check active GPS device assignment
        $activeAssignment = $car->activeDeviceAssignment;

        if (!$activeAssignment || !$activeAssignment->device) {
            return response()->json([
                'message' => 'No active GPS device found on this car.',
            ], 400);
        }

        $device = $activeAssignment->device;

        // 4. Record command request in database
        $commandLog = DeviceCommand::create([
            'car_id' => $car->id,
            'device_id' => $device->id,
            'requested_by' => $user->id,
            'type' => $request->command_type,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // 5. Dispatch command via Traccar API
        $result = $this->traccarService->sendCommand(
            $device->traccar_device_id,
            $request->command_type
        );

        if ($result['success']) {
            $commandLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            return response()->json([
                'message' => 'Engine command accepted by Traccar. Device execution is not yet confirmed.',
                'command_id' => $commandLog->id,
                'status' => 'sent',
            ]);
        }

        // 6. Record command failure
        $commandLog->update([
            'status' => 'failed',
            'error_message' => json_encode($result['error']),
        ]);

        return response()->json([
            'message' => 'Failed to send command to device.',
            'error' => $result['error'],
        ], 500);
    }
}
