import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from '@ably/laravel-echo';
import * as Ably from 'ably';

window.Ably = Ably;

if (import.meta.env.VITE_ABLY_ENABLED === 'true') {
    window.Echo = new Echo({
        broadcaster: 'ably',
        authEndpoint: '/broadcasting/auth',
    });
}
