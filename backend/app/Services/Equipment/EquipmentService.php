<?php

namespace App\Services\Equipment;

use App\Models\Equipment;
use App\Services\QrCode\QrCodeService;

class EquipmentService
{
    public function __construct(private readonly QrCodeService $qrCodes)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Equipment
    {
        $data['qr_token'] = $this->qrCodes->generateToken();

        return Equipment::create($data);
    }

    public function rotateToken(Equipment $equipment): Equipment
    {
        $equipment->update(['qr_token' => $this->qrCodes->generateToken()]);

        return $equipment->fresh();
    }
}
