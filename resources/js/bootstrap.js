import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const getBroadcastSocketId = () => {
    const echo = window.Echo;

    if (!echo || echo.options?.broadcaster !== 'reverb') {
        return null;
    }

    const connection = echo.connector?.pusher?.connection;

    if (connection?.state !== 'connected') {
        return null;
    }

    return echo.socketId();
};

window.getBroadcastSocketId = getBroadcastSocketId;

window.axios.interceptors.request.use((config) => {
    const socketId = getBroadcastSocketId();

    if (socketId) {
        config.headers = config.headers || {};
        config.headers['X-Socket-ID'] = socketId;
    }

    return config;
});

const shouldInitializeEcho = () =>
    import.meta.env.VITE_REVERB_ENABLED === 'true'
    && window.__broadcasting?.enabled === true;

/**
 * Echo + pusher-js 體積大：僅在佇署且後端啟用廣播時動態載入，避免塞進主 chunk。
 */
const initializeEcho = async () => {
    if (!shouldInitializeEcho()) {
        return;
    }

    const [{ default: Echo }, { default: Pusher }] = await Promise.all([
        import('laravel-echo'),
        import('pusher-js'),
    ]);

    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
        withoutInterceptors: true,
    });
};

if (import.meta.env.VITE_REVERB_ENABLED === 'true') {
    void initializeEcho();
}
