<?php

namespace App\Support\Exceptions;

class WebErrorMetadata
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $statusName,
        public readonly string $title,
        public readonly string $description,
        public readonly string $primaryActionLabel,
        public readonly string $primaryActionUrl,
        public readonly string $primaryActionType = 'back', // 'back', 'reload', 'link'
        public readonly ?string $secondaryActionLabel = null,
        public readonly ?string $secondaryActionUrl = null,
        public readonly ?string $referenceId = null
    ) {}

    /**
     * Resolve metadata for a given HTTP status code.
     */
    public static function resolve(int $statusCode, ?string $customMessage = null, ?string $referenceId = null): self
    {
        // Sanitize raw framework or leak messages
        if ($customMessage !== null) {
            $isFrameworkLeak = str_starts_with($customMessage, 'The route ')
                || str_contains($customMessage, 'could not be found')
                || str_contains($customMessage, 'No query results')
                || str_contains($customMessage, 'SQLSTATE')
                || str_contains($customMessage, 'SELECT ')
                || str_contains($customMessage, 'INSERT ');
            if ($isFrameworkLeak) {
                $customMessage = null;
            }
        }

        $dashboardUrl = route('dashboard', [], false) ?: '/dashboard';
        $loginUrl = route('login', [], false) ?: '/login';

        return match ($statusCode) {
            400 => new self(
                statusCode: 400,
                statusName: 'Bad Request',
                title: 'Geçersiz İstek',
                description: $customMessage ?: 'Sunucuya iletilen istek anlaşılamadı.',
                primaryActionLabel: 'Geri Dön',
                primaryActionUrl: 'javascript:smartGoBack()',
                primaryActionType: 'back',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl
            ),

            401 => new self(
                statusCode: 401,
                statusName: 'Unauthorized',
                title: 'Oturum Açmanız Gerekiyor',
                description: $customMessage ?: 'Bu sayfayı görüntülemek için lütfen kullanıcı hesabınızla giriş yapın.',
                primaryActionLabel: 'Giriş Yap',
                primaryActionUrl: $loginUrl,
                primaryActionType: 'link',
                secondaryActionLabel: 'Ana Sayfa',
                secondaryActionUrl: '/'
            ),

            402 => new self(
                statusCode: 402,
                statusName: 'Payment Required',
                title: 'Lisans / Ödeme Gerekli',
                description: $customMessage ?: 'Bu özelliğe erişebilmek için aktif bir abonelik veya ödeme gerekmektedir.',
                primaryActionLabel: 'Yönetim Paneline Dön',
                primaryActionUrl: $dashboardUrl,
                primaryActionType: 'link',
                secondaryActionLabel: 'Geri Dön',
                secondaryActionUrl: 'javascript:smartGoBack()'
            ),

            403 => new self(
                statusCode: 403,
                statusName: 'Forbidden',
                title: 'Erişim Engellendi',
                description: $customMessage ?: 'Bu sayfayı görüntülemek için yetkiniz bulunmuyor.',
                primaryActionLabel: 'Yönetim Paneline Dön',
                primaryActionUrl: $dashboardUrl,
                primaryActionType: 'link',
                secondaryActionLabel: 'Geri Dön',
                secondaryActionUrl: 'javascript:smartGoBack()'
            ),

            404 => new self(
                statusCode: 404,
                statusName: 'Not Found',
                title: 'Sayfa Bulunamadı',
                description: $customMessage ?: 'Aradığınız sayfa silinmiş, taşınmış veya bağlantı adresi hatalı olabilir.',
                primaryActionLabel: 'Yönetim Paneline Dön',
                primaryActionUrl: $dashboardUrl,
                primaryActionType: 'link',
                secondaryActionLabel: 'Geri Dön',
                secondaryActionUrl: 'javascript:smartGoBack()'
            ),

            408 => new self(
                statusCode: 408,
                statusName: 'Request Timeout',
                title: 'İstek Zaman Aşımı',
                description: $customMessage ?: 'İstek belirlenen süre içinde tamamlanamadı.',
                primaryActionLabel: 'Sayfayı Yenile',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Geri Dön',
                secondaryActionUrl: 'javascript:smartGoBack()'
            ),

            419 => new self(
                statusCode: 419,
                statusName: 'Page Expired',
                title: 'Oturum Süresi Doldu',
                description: $customMessage ?: 'Sayfa uzun süre açık kaldığı için oturum zaman aşımına uğradı.',
                primaryActionLabel: 'Yeniden Dene',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Giriş Yap',
                secondaryActionUrl: $loginUrl
            ),

            429 => new self(
                statusCode: 429,
                statusName: 'Too Many Requests',
                title: 'Çok Fazla İstek',
                description: $customMessage ?: 'Kısa sürede çok fazla istek yapıldı. Lütfen biraz bekleyip tekrar deneyin.',
                primaryActionLabel: 'Yeniden Dene',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl
            ),

            500 => new self(
                statusCode: 500,
                statusName: 'Internal Server Error',
                title: 'Sunucu Hatası',
                description: $customMessage ?: 'Beklenmeyen bir hata oluştu. Mühendislerimiz konuyla ilgileniyor.',
                primaryActionLabel: 'Sayfayı Yenile',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Yönetim Paneline Dön',
                secondaryActionUrl: $dashboardUrl,
                referenceId: $referenceId
            ),

            501 => new self(
                statusCode: 501,
                statusName: 'Not Implemented',
                title: 'Özellik Aktif Değil',
                description: $customMessage ?: 'Bu özellik henüz kullanıma sunulmamıştır.',
                primaryActionLabel: 'Geri Dön',
                primaryActionUrl: 'javascript:smartGoBack()',
                primaryActionType: 'back',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl
            ),

            502 => new self(
                statusCode: 502,
                statusName: 'Bad Gateway',
                title: 'Ağ Geçidi Hatası',
                description: $customMessage ?: 'Sunucu bağlantısında geçici bir aksaklık yaşandı.',
                primaryActionLabel: 'Yeniden Dene',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl
            ),

            503 => new self(
                statusCode: 503,
                statusName: 'Service Unavailable',
                title: 'Bakım Yapılıyor',
                description: $customMessage ?: 'Sistemde planlı bir bakım çalışması yürütülmektedir. En kısa sürede açılacaktır.',
                primaryActionLabel: 'Sayfayı Yenile',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: null,
                secondaryActionUrl: null
            ),

            504 => new self(
                statusCode: 504,
                statusName: 'Gateway Timeout',
                title: 'Ağ Zaman Aşımı',
                description: $customMessage ?: 'Sunucudan zamanında yanıt alınamadı.',
                primaryActionLabel: 'Yeniden Dene',
                primaryActionUrl: 'javascript:window.location.reload()',
                primaryActionType: 'reload',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl
            ),

            default => new self(
                statusCode: $statusCode,
                statusName: 'Error ' . $statusCode,
                title: 'Bir Hata Oluştu',
                description: $customMessage ?: 'İşlem sırasında beklenmeyen bir hata meydana geldi.',
                primaryActionLabel: 'Geri Dön',
                primaryActionUrl: 'javascript:smartGoBack()',
                primaryActionType: 'back',
                secondaryActionLabel: 'Yönetim Paneli',
                secondaryActionUrl: $dashboardUrl,
                referenceId: $referenceId
            )
        };
    }
}
