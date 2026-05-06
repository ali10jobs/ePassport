<?php

namespace App\Services\QrCode;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Generates QR PNGs from raw token strings. The platform's gate-scan flow
 * scans helmet/coverall/equipment tokens; image generation happens here so
 * the same code path serves the worker QR endpoint, the equipment QR endpoint,
 * and any future printable cert / passport flows.
 *
 * Tokens must be opaque — they are random per-entity strings, not human-
 * meaningful IDs. Scanning resolves the token to an entity server-side.
 */
class QrCodeService
{
    public const DEFAULT_SIZE_PX = 600;
    public const DEFAULT_MARGIN_PX = 24;

    /**
     * Generate a PNG of a QR encoding $token. Returns raw binary bytes.
     *
     * High error correction (H = ~30%) is used so partial damage to a printed
     * helmet sticker (scratches, sun bleach) still resolves cleanly.
     */
    public function pngFromToken(string $token, int $size = self::DEFAULT_SIZE_PX): string
    {
        $qr = new QrCode(
            data: $token,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: self::DEFAULT_MARGIN_PX,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );

        return (new PngWriter())->write($qr)->getString();
    }

    /**
     * Generate a fresh random token suitable for use as a helmet, coverall, or
     * equipment QR. URL-safe, 22 chars (~128 bits of entropy).
     */
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(16)), '+/', '-_'), '=');
    }
}
