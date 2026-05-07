import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from '@ably/laravel-echo';
import * as Ably from 'ably';

window.Ably = Ably;

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

if (import.meta.env.VITE_ABLY_ENABLED === 'true') {
    window.Echo = new Echo({
        broadcaster: 'ably',
        authEndpoint: `${window.location.origin}/broadcasting/auth`,
        withoutInterceptors: true,
    });
}
