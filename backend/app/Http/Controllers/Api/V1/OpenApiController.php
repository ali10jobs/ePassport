<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\Yaml\Yaml;

/**
 * Serves the Scribe-generated OpenAPI 3.1 specification at /api/v1/openapi.json
 * (canonical URL per platform contract). The same spec is also served as YAML
 * at /api/v1/docs.openapi by Scribe.
 */
class OpenApiController extends Controller
{
    /**
     * @hideFromAPIDocumentation
     */
    public function spec(): JsonResponse|Response
    {
        $path = storage_path('app/private/scribe/openapi.yaml');

        if (! file_exists($path)) {
            return response()->json([
                'error' => [
                    'code' => 'OPENAPI_SPEC_NOT_GENERATED',
                    'message' => 'OpenAPI spec has not been generated yet. Run `php artisan scribe:generate`.',
                ],
            ], 503);
        }

        $yaml = file_get_contents($path);
        $parsed = Yaml::parse($yaml);

        return response()->json($parsed, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
