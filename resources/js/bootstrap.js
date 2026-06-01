import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const getAblySocketId = () => {
    const echo = window.Echo;

    if (!echo || echo.options?.broadcaster !== 'ably') {
        return null;
    }

    const connection = echo.connector?.ably?.connection;
    const connectionKey = connection?.key;

    if (!connectionKey || connection?.state !== 'connected') {
        return null;
    }

    return echo.socketId();
};

window.getAblySocketId = getAblySocketId;

window.axios.interceptors.request.use((config) => {
    const socketId = getAblySocketId();

    if (socketId) {
        config.headers = config.headers || {};
        config.headers['X-Socket-ID'] = socketId;
    }

    return config;
});

const shouldInitializeEcho = () =>
    import.meta.env.VITE_ABLY_ENABLED === 'true'
    && window.__broadcasting?.enabled === true;

/**
 * Ably + Echo 體積大：僅在佇署且後端啟用廣播時動態載入，避免塞進主 chunk。
 */
const initializeEcho = async () => {
    if (!shouldInitializeEcho()) {
        return;
    }

    const [{ default: Echo }, ablyModule] = await Promise.all([
        import('@ably/laravel-echo'),
        import('ably'),
    ]);

    window.Ably = ablyModule;
    window.Echo = new Echo({
        broadcaster: 'ably',
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
        withoutInterceptors: true,
    });
};

if (import.meta.env.VITE_ABLY_ENABLED === 'true') {
    void initializeEcho();
}
