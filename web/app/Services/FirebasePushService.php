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

        if (file_exists($credentialsPath)) {
            $this->serviceAccount = json_decode(file_get_contents($credentialsPath), true) ?: [];
            $this->projectId = $this->serviceAccount['project_id'] ?? 'bvbarber-ae2cd';
        } else {
            $this->serviceAccount = [];
            $this->projectId = 'bvbarber-ae2cd';
        }
    }

    /**
     * Firebase Service Account kimlik dosyasının varlığını ve geçerliliğini kontrol eder.
     */
    public function isConfigured(): bool
    {
        return !empty($this->serviceAccount) && 
               !empty($this->serviceAccount['private_key']) && 
               !empty($this->serviceAccount['client_email']);
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

        if (!$this->isConfigured()) {
            Log::warning('[FCM] firebase-service-account.json bulunamadığı için push iletimi atlandı.');
            return 0;
        }

        $accessToken = $this->getAccessToken();
        $successCount = 0;

        foreach ($tokens as $token) {
            try {
                $response = $this->httpClient()
                    ->withToken($accessToken)
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
                                'android' => [
                                    'priority' => 'high',
                                    'notification' => [
                                        'channel_id' => 'bvbarber_channel',
                                        'sound' => 'default',
                                        'notification_priority' => 'PRIORITY_MAX',
                                        'default_sound' => true,
                                        'default_vibrate_timings' => true,
                                        'visibility' => 'PUBLIC',
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    ],
                                ],
                                'apns' => [
                                    'headers' => [
                                        'apns-priority' => '10',
                                    ],
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
            $response = $this->httpClient()->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('Google OAuth2 token alınamadı: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    /**
     * Yerel geliştirme ortamında (özellikle Windows'ta CA sertifikası eksikse)
     * cURL 60 SSL hatası almamak için güvenli fallback ile HTTP istemcisi döner.
     * Canlı ortamda (production) her zaman katı SSL doğrulaması yapar.
     */
    private function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(15);
        if (!app()->isProduction() && empty(ini_get('curl.cainfo')) && empty(ini_get('openssl.cafile'))) {
            return $client->withoutVerifying();
        }
        return $client;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
