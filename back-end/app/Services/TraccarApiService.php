<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TraccarApiService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.traccar.url');
        $this->username = config('services.traccar.username');
        $this->password = config('services.traccar.password');
    }

    /**
     * Client HTTP de base configuré avec l'authentification HTTP Basic
     */
    protected function client()
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->contentType('application/json');
    }

    /**
     * 1. Créer un nouveau Device dans Traccar lors de l'enregistrement du GPS
     */
    public function createDevice(string $name, string $imei)
    {
        $response = $this->client()->post("{$this->baseUrl}/devices", [
            'name' => $name,
            'uniqueId' => $imei,
        ]);

        if ($response->successful()) {
            return $response->json(); // Retourne les données Traccar avec l'ID généré
        }

        Log::error('Traccar - Échec de création du device', ['body' => $response->body()]);
        return null;
    }

    /**
     * 2. Obtenir les positions actuelles (Live) pour une liste de devices
     */
    public function getPositions(array $traccarDeviceIds = [])
    {
        if (empty($traccarDeviceIds)) {
            return [];
        }

        $queryParams = implode('&', array_map(fn($id) => "deviceId={$id}", $traccarDeviceIds));
        $response = $this->client()->get("{$this->baseUrl}/positions?{$queryParams}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Traccar - Échec de récupération des positions', ['body' => $response->body()]);
        return [];
    }

    /**
     * 3. Envoyer une commande au GPS (Ex: Cut Engine / Restore Engine)
     */
    public function sendCommand(int $traccarDeviceId, string $type)
    {
        // Conversion des types de commande selon le protocole Teltonika dans Traccar
        $typeTraccar = match ($type) {
            'cut_engine' => 'engineStop',
            'restore_engine' => 'engineResume',
            default => $type,
        };

        $response = $this->client()->post("{$this->baseUrl}/commands/send", [
            'deviceId' => $traccarDeviceId,
            'type' => $typeTraccar,
        ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $response->json(),
            ];
        }

        return [
            'success' => false,
            'error' => $response->body(),
        ];
    }

    /**
     * 4. Récupérer l'historique des trajets (Route History)
     */
    public function getRouteHistory(int $traccarDeviceId, string $fromISO, string $toISO)
    {
        $response = $this->client()->get("{$this->baseUrl}/reports/route", [
            'deviceId' => $traccarDeviceId,
            'from' => $fromISO, // Format ISO 8601 (ex: 2026-09-21T00:00:00Z)
            'to' => $toISO,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}
