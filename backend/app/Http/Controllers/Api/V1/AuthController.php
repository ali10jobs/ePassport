<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\ErrorCodes;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * Login flow supports two modes:
 *
 *   mode=cookie (default): web SPA path. Caller must hit /sanctum/csrf-cookie
 *     first, then POST /auth/login with the XSRF-TOKEN header. Sanctum
 *     creates a session cookie; subsequent requests authenticate via cookie.
 *
 *   mode=token: mobile + ERP path. Returns a fresh Sanctum personal access
 *     token. Caller stores the token (Keychain/Keystore for mobile) and
 *     sends it as Authorization: Bearer <token> on every request.
 */
class AuthController extends Controller
{
    /**
     * Log in.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);
        $mode = $request->input('mode', 'cookie');

        $user = User::where('email', $credentials['email'])->first();
        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw new ApiException(
                errorCode: ErrorCodes::UNAUTHENTICATED,
                message: 'Invalid email or password.',
                status: 401,
            );
        }

        if ($mode === 'token') {
            // Issue a fresh PAT for mobile / ERP integrations.
            $deviceName = $request->input('device_name') ?: 'unnamed-device';
            // Revoke previous tokens with the same device name to keep things tidy.
            $user->tokens()->where('name', $deviceName)->delete();
            $token = $user->createToken($deviceName);

            return response()->json([
                'data' => [
                    'token_type' => 'Bearer',
                    'access_token' => $token->plainTextToken,
                    'user' => $this->me($user),
                ],
            ]);
        }

        // Cookie mode: log in via the web guard so Sanctum issues a session cookie.
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'data' => [
                'token_type' => 'cookie',
                'user' => $this->me($user),
            ],
        ]);
    }

    /**
     * Log out the current user.
     *
     * For cookie sessions: invalidates and regenerates the session.
     * For token auth: revokes the bearer token used for this request.
     *
     * @authenticated
     */
    public function logout(Request $request): Response
    {
        $user = $request->user();
        if ($user === null) {
            return response()->noContent();
        }

        // Token auth: revoke the specific token that was used.
        if (method_exists($user->currentAccessToken(), 'delete')) {
            $user->currentAccessToken()?->delete();
        }

        // Cookie session (idempotent if no session is present).
        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }

    /** @return array<string, mixed> */
    private function me(User $user): array
    {
        $user->load('organizations');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'locale' => $user->locale,
            'organizations' => $user->organizations->map(fn ($org) => [
                'id' => $org->id,
                'name_en' => $org->name_en,
                'name_ar' => $org->name_ar,
                'role' => $org->pivot->role,
                'is_default' => (bool) $org->pivot->is_default,
            ]),
        ];
    }
}
