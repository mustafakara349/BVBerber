import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Laravel Echo (Reverb) istemcisi.
 *
 * - Host belirtilmemişse/"localhost" ise sayfanın açıldığı host kullanılır; böylece panel
 *   aynı ağdaki başka bir cihazdan (ör. 192.168.x.x:8000) açıldığında da bağlantı kurulur.
 * - Vite modülleri ertelenmiş (deferred) yüklendiğinden, hazır olunduğunda `echo:ready`
 *   olayı yayınlanır; sayfa içi betikler bu olayı dinleyerek abone olur.
 * - Yapılandırma eksikse veya bağlantı kurulamazsa uygulama çalışmaya devam eder
 *   (bildirimler yedek yoklama ile gelir).
 */
const key = import.meta.env.VITE_REVERB_APP_KEY;

if (key) {
    try {
        const configuredHost = import.meta.env.VITE_REVERB_HOST;
        const host = !configuredHost || configuredHost === 'localhost'
            ? window.location.hostname
            : configuredHost;
        const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
        const port = Number(import.meta.env.VITE_REVERB_PORT) || (scheme === 'https' ? 443 : 80);

        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: host,
            wsPort: port,
            wssPort: port,
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        document.dispatchEvent(new CustomEvent('echo:ready'));
    } catch (e) {
        console.warn('[Echo] Başlatılamadı, yedek yoklama kullanılacak.', e);
    }
}
