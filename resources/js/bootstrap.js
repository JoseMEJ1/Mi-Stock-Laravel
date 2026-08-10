import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY ?? 'local',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
    wsPort: Number(import.meta.env.VITE_PUSHER_PORT ?? 6001),
    forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? undefined,
});

window.renderResourceChange = function (event) {
    const list = document.getElementById('realtime-events');

    if (!list) {
        return;
    }

    const item = document.createElement('div');
    item.className = 'rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-900 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100';
    item.innerHTML = `
        <div class="flex items-center justify-between gap-4">
            <span class="font-semibold">${event.resource} ${event.action}</span>
            <span class="text-xs text-slate-500 dark:text-slate-400">${new Date(event.timestamp).toLocaleString()}</span>
        </div>
        <div class="mt-2 text-[13px] text-slate-600 dark:text-slate-300"><pre class="whitespace-pre-wrap break-words">${JSON.stringify(event.data, null, 2)}</pre></div>
    `;

    list.prepend(item);
    while (list.children.length > 10) {
        list.removeChild(list.lastChild);
    }
};

window.onResourceChangedCallbacks = [];
window.onResourceChanged = function (callback) {
    if (typeof callback === 'function') {
        window.onResourceChangedCallbacks.push(callback);
    }
};

window.Echo.channel('mi-stock').listen('.resource.changed', (event) => {
    window.renderResourceChange(event);
    window.onResourceChangedCallbacks.forEach((callback) => callback(event));
});
