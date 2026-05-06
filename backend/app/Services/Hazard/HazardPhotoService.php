<?php

namespace App\Services\Hazard;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

/**
 * Strips ALL EXIF metadata from an uploaded hazard photo and returns the
 * cleaned binary. Anonymous reporters could leak PII (GPS from camera, device
 * model, original timestamp) via EXIF; we re-encode the image so nothing
 * survives.
 *
 * Intervention Image v4 re-encodes via the configured driver (GD by default).
 * Re-encoding strips EXIF as a side effect; we verify post-strip that no
 * Exif marker remains.
 */
class HazardPhotoService
{
    public const MAX_DIMENSION = 2400; // long-edge cap

    private readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver());
    }

    /**
     * Re-encode the uploaded photo to JPEG with EXIF stripped. Returns raw
     * bytes ready for storage.
     */
    public function stripExifAndReencode(UploadedFile $upload): string
    {
        $image = $this->manager->decodePath($upload->getRealPath());

        // Cap long edge so we don't store giant photos
        $width = $image->width();
        $height = $image->height();
        $longEdge = max($width, $height);
        if ($longEdge > self::MAX_DIMENSION) {
            $scale = self::MAX_DIMENSION / $longEdge;
            $image->scale(width: (int) round($width * $scale));
        }

        // Re-encode strips EXIF
        return (string) $image->encode(new JpegEncoder(quality: 85));
    }

    /**
     * Cheap post-strip sanity check: scan for the JPEG APP1 EXIF marker.
     * Returns true iff no Exif marker is present.
     */
    public function verifyNoExif(string $jpegBytes): bool
    {
        // EXIF marker: 0xFFE1 followed by length, then 'Exif\0\0'
        return strpos($jpegBytes, "\xFF\xE1") === false
            || strpos($jpegBytes, 'Exif') === false;
    }
}
