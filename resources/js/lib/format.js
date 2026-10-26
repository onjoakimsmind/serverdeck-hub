export function timeAgo(value) {
    if (!value) return '—';

    const date = new Date(value);
    const seconds = Math.floor((Date.now() - date.getTime()) / 1000);

    if (seconds < 45) return 'just now';
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
    if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;

    return new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short' }).format(date);
}

export function formatDateTime(value) {
    if (!value) return '—';

    return new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(value));
}

export function formatUptime(seconds) {
    if (!seconds && seconds !== 0) return '—';

    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);

    if (days > 0) return `${days}d ${hours}h`;

    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) return `${hours}h ${minutes}m`;

    return `${minutes}m`;
}

export function percentTone(value) {
    if (value === null || value === undefined) return 'text-dim';
    if (value >= 90) return 'text-rose';
    if (value >= 75) return 'text-amber';

    return 'text-accent';
}
