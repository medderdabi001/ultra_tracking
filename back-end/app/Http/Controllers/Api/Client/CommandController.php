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
        $car = Car::with('activeDeviceAssignment.device')->findOrFail($request->car_id);

        // 1. Permission Check
        if ($user->role === 'assistant') {
            $permission = $user->permittedCars()->where('car_id', $car->id)->first();
            if (!$permission || !$permission->pivot->can_cut_engine) {
                return response()->json([
                    'message' => 'Unauthorized! You do not have permission to control engine for this car.'
                ], 403);
            }
        } else {
            // Check if car belongs to Gerant's Account
            if ($car->account_id !== $user->account_id) {
                return response()->json(['message' => 'Unauthorized car access.'], 403);
            }
        }

        $activeAssignment = $car->activeDeviceAssignment;
        if (!$activeAssignment || !$activeAssignment->device) {
            return response()->json(['message' => 'No active GPS device found on this car.'], 400);
        }

        $device = $activeAssignment->device;

        // 2. Record Command Request in Database (Pending status)
        $commandLog = DeviceCommand::create([
            'car_id' => $car->id,
            'device_id' => $device->id,
            'requested_by' => $user->id,
            'type' => $request->command_type,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // 3. Dispatch Command via Traccar API
        $result = $this->traccarService->sendCommand($device->traccar_device_id, $request->command_type);

        if ($result['success']) {
            $commandLog->update([
                'status' => 'completed',
                'sent_at' => now(),
                'completed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Engine command sent successfully!',
                'command_id' => $commandLog->id,
            ]);
        } else {
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
}
