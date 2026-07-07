<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Firebase Cloud Messaging (FCM) HTTP v1 API üzerinden
 * push bildirim gönderen servis.
 *
 * Gereksinimler:
 *  - Firebase Service Account JSON dosyası (.env'de yolu belirtilmeli)
 *  - PHP'nin openssl eklentisi (JWT oluşturmak için)
 */
class FirebasePushService
{
    private string $projectId;
    private array  $serviceAccount;

    public function __construct()
    {
        $credentialsPath = base_path(config('services.firebase.credentials', 'firebase-service-account.json'));

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException(
                "Firebase Service Account dosyası bulunamadı: {$credentialsPath}"
            );
        }

        $this->serviceAccount = json_decode(file_get_contents($credentialsPath), true);
        $this->projectId = $this->serviceAccount['project_id'] ?? 'bvbarber-ae2cd';
    }

    // ───────────────────────────────────────────────
    //  Public API
    // ───────────────────────────────────────────────

    /**
     * Tek kullanıcıya push bildirim gönderir.
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $tokens = Device::where('user_id', $userId)
            ->whereNotNull('push_token')
            ->pluck('push_token')
            ->toArray();

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Birden fazla kullanıcıya push bildirim gönderir.
     */
    public function sendToMultipleUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = Device::whereIn('user_id', $userIds)
            ->whereNotNull('push_token')
            ->pluck('push_token')
            ->toArray();

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    // ───────────────────────────────────────────────
    //  Token bazlı gönderim
    // ───────────────────────────────────────────────

    /**
     * Verilen FCM token listesine push bildirim gönderir.
     *
     * @return int Başarılı gönderim sayısı
     */
    private function sendToTokens(array $tokens, string $title, string $body, array $data = []): int
    {
        if (empty($tokens)) {
            return 0;
        }

        $accessToken = $this->getAccessToken();
        $successCount = 0;

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)
                    ->post(
                        "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send",
                        [
                            'message' => [
                                'token' => $token,
                                'notification' => [
                                    'title' => $title,
                                    'body'  => $body,
                                ],
                                'data' => array_map('strval', $data),
                                'apns' => [
                                    'payload' => [
                                        'aps' => [
                                            'sound' => 'default',
                                            'badge' => 1,
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    );

                if ($response->successful()) {
                    $successCount++;
                } else {
                    $responseBody = $response->json();
                    $errorCode = $responseBody['error']['details'][0]['errorCode'] ?? '';

                    // Geçersiz veya süresi dolmuş token ise sil
                    if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                        Device::where('push_token', $token)->delete();
                        Log::info("[FCM] Geçersiz token silindi: " . substr($token, 0, 20) . '…');
                    }

                    Log::warning("[FCM] Push gönderilemedi", [
                        'token'    => substr($token, 0, 20) . '…',
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("[FCM] Push gönderim hatası: {$e->getMessage()}");
            }
        }

        return $successCount;
    }

    // ───────────────────────────────────────────────
    //  Google OAuth2 Access Token (JWT ile)
    // ───────────────────────────────────────────────

    /**
     * Firebase Service Account JSON ile Google OAuth2 access token alır.
     * Token 50 dakika boyunca cache'lenir.
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            $now = time();

            // JWT Header
            $header = $this->base64UrlEncode(json_encode([
                'alg' => 'RS256',
                'typ' => 'JWT',
            ]));

            // JWT Claim Set
            $claimSet = $this->base64UrlEncode(json_encode([
                'iss'   => $this->serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            // Sign
            $signatureInput = "{$header}.{$claimSet}";
            $privateKey = openssl_pkey_get_private($this->serviceAccount['private_key']);

            if (!$privateKey) {
                throw new \RuntimeException('Firebase private key okunamadı.');
            }

            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = "{$signatureInput}." . $this->base64UrlEncode($signature);

            // Token al
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Google OAuth2 token alınamadı: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
