<?php

namespace App\Services;

use App\Models\OnlineMeeting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class JitsiTokenService
{
    public function generate(User $user, OnlineMeeting $meeting, bool $isModerator): ?string
    {
        if ($this->shouldUseJaas($meeting)) {
            return $this->generateJaasToken($user, $meeting, $isModerator);
        }

        return $this->generateLegacyToken($user, $meeting, $isModerator);
    }

    private function generateLegacyToken(User $user, OnlineMeeting $meeting, bool $isModerator): ?string
    {
        $appId = config('services.jitsi.jwt_app_id');
        $secret = config('services.jitsi.jwt_app_secret');

        if (blank($appId) || blank($secret)) {
            return null;
        }

        $now = time();
        $domain = $meeting->jitsi_domain ?: config('services.jitsi.domain');
        $iss = config('services.jitsi.jwt_iss') ?: $appId;
        $sub = config('services.jitsi.jwt_sub') ?: $domain;
        $aud = config('services.jitsi.jwt_aud', 'jitsi');

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = [
            'aud' => $aud,
            'iss' => $iss,
            'sub' => $sub,
            'room' => $meeting->jitsi_room,
            'exp' => $now + 7200,
            'nbf' => $now - 10,
            'iat' => $now,
            'context' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'moderator' => $isModerator ? 'true' : 'false',
                ],
            ],
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    private function generateJaasToken(User $user, OnlineMeeting $meeting, bool $isModerator): ?string
    {
        $appId = (string) config('services.jitsi.jaas_app_id');
        $kid = (string) config('services.jitsi.jaas_kid');
        $privateKey = $this->resolveJaasPrivateKey();

        if (blank($appId) || blank($kid) || blank($privateKey)) {
            return null;
        }

        $now = time();
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $kid,
        ];

        $payload = [
            'aud' => 'jitsi',
            'iss' => 'chat',
            'sub' => $appId,
            'room' => '*',
            'exp' => $now + 7200,
            'nbf' => $now - 10,
            'iat' => $now,
            'context' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'moderator' => $isModerator ? 'true' : 'false',
                ],
                'features' => [
                    'recording' => (bool) $meeting->recording_enabled,
                    'livestreaming' => false,
                    'transcription' => false,
                    'outbound-call' => false,
                ],
            ],
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        $signature = '';
        $signed = openssl_sign($headerEncoded . '.' . $payloadEncoded, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$signed) {
            Log::error('JaaS JWT signing failed', [
                'openssl_error' => openssl_error_string(),
                'kid' => $kid,
                'app_id' => $appId,
            ]);
            return null;
        }

        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    private function resolveJaasPrivateKey(): ?string
    {
        $inline = config('services.jitsi.jaas_private_key');
        if (filled($inline)) {
            $normalized = str_replace('\n', PHP_EOL, (string) $inline);
            return str_contains($normalized, 'BEGIN') ? $normalized : null;
        }

        $path = config('services.jitsi.jaas_private_key_path');
        if (blank($path)) {
            return null;
        }

        if (!is_string($path) || !is_file($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        return $contents !== false ? $contents : null;
    }

    private function shouldUseJaas(OnlineMeeting $meeting): bool
    {
        $domain = strtolower((string) ($meeting->jitsi_domain ?: config('services.jitsi.domain')));
        return str_contains($domain, '8x8.vc') || filled(config('services.jitsi.jaas_app_id'));
    }

    private function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
