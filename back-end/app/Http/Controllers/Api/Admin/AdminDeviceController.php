<?php


namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Services\TraccarApiService;

class AdminDeviceController extends Controller
{
    protected TraccarApiService $traccarService;

    public function __construct(TraccarApiService $traccarService)
    {
        $this->traccarService = $traccarService;
    }

    /**
     * Register a new GPS Device in Laravel & Sync with Traccar.
     */
    public function store(Request $request)
    {
        $request->validate([
            'imei' => 'required|string|unique:devices,imei',
            'model' => 'nullable|string',
            'phone_number' => 'nullable|string',
        ]);

        $modelName = $request->model ?? 'Teltonika FMC920';

        // 1. Create Device inside Traccar Server via API
        $traccarDevice = $this->traccarService->createDevice($modelName . ' (' . $request->imei . ')', $request->imei);

        if (!$traccarDevice || !isset($traccarDevice['id'])) {
            return response()->json([
                'message' => 'Failed to create device on Traccar Server'
            ], 500);
        }

        // 2. Save Device into Laravel Database
        $device = Device::create([
            'imei' => $request->imei,
            'traccar_device_id' => $traccarDevice['id'],
            'model' => $modelName,
            'phone_number' => $request->phone_number,
            'protocol' => 'teltonika',
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'Device registered and synced with Traccar successfully',
            'device' => $device,
        ], 201);
    }
}