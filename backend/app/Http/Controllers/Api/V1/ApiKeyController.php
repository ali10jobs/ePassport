<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CreateApiKeyRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @group API Keys
 *
 * Long-lived API keys for ERP / machine-to-machine integrations. Built on
 * Sanctum personal access tokens with scoped abilities. The full token is
 * returned ONCE at creation; subsequent reads only return the prefix and
 * metadata.
 */
class ApiKeyController extends Controller
{
    /**
     * @authenticated
     */
    public function index(Request $request): JsonResource
    {
        $user = $request->user();
        $tokens = $user->tokens()->orderByDesc('created_at')->get();

        return JsonResource::collection($tokens->map(fn (PersonalAccessToken $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'abilities' => $t->abilities,
            'last_used_at' => $t->last_used_at?->toIso8601String(),
            'expires_at' => $t->expires_at?->toIso8601String(),
            'created_at' => $t->created_at?->toIso8601String(),
        ]));
    }

    /**
     * Create a new API key. The plaintext token is returned ONCE; store it now.
     *
     * @authenticated
     */
    public function store(CreateApiKeyRequest $request): JsonResponse
    {
        $abilities = $request->input('abilities', ['*']);
        $expiresAt = $request->input('expires_at');

        $newToken = $request->user()->createToken(
            name: $request->validated('name'),
            abilities: $abilities,
            expiresAt: $expiresAt ? Carbon::parse($expiresAt) : null,
        );

        return response()->json([
            'data' => [
                'id' => $newToken->accessToken->id,
                'name' => $newToken->accessToken->name,
                'abilities' => $newToken->accessToken->abilities,
                'expires_at' => $newToken->accessToken->expires_at?->toIso8601String(),
                'created_at' => $newToken->accessToken->created_at?->toIso8601String(),
                'token' => $newToken->plainTextToken,
                'token_warning' => 'Store this token now. It will not be shown again. Send as Authorization: Bearer <token> on requests.',
            ],
        ], 201);
    }

    /**
     * Revoke an API key.
     *
     * @authenticated
     */
    public function destroy(Request $request, int $tokenId): Response
    {
        $token = $request->user()->tokens()->whereKey($tokenId)->firstOrFail();
        $token->delete();

        return response()->noContent();
    }

    /**
     * Available abilities catalog. UI for issuing keys uses this to render
     * the ability checklist.
     *
     * @authenticated
     */
    public function abilities(): JsonResponse
    {
        return response()->json([
            'data' => CreateApiKeyRequest::abilitiesCatalog(),
        ]);
    }
}
