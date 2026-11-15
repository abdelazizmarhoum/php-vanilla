'use strict';

(() => {
    const feed = document.getElementById('activity-feed');
    if (!feed) return;

    const slug = feed.dataset.workspaceSlug;
    let lastId = Number.parseInt(feed.dataset.lastId || '0', 10) || 0;
    const interval = 15000;

    const humanize = (action, meta) => {
        const title = typeof meta.title === 'string' ? meta.title : '';
        const documentTitle = typeof meta.document_title === 'string' ? meta.document_title : title;
        switch (action) {
            case 'document.created': return title ? `created '${title}'` : 'created a document';
            case 'document.updated': return title ? `updated '${title}'` : 'updated a document';
            case 'document.deleted': return title ? `deleted '${title}'` : 'deleted a document';
            case 'document.restored': return title ? `restored '${title}'` : 'restored a document';
            case 'comment.added': return documentTitle ? `commented on '${documentTitle}'` : 'commented';
            case 'comment.deleted': return documentTitle ? `deleted a comment on '${documentTitle}'` : 'deleted a comment';
            case 'workspace.created': return typeof meta.name === 'string' ? `created workspace '${meta.name}'` : 'created a workspace';
            case 'workspace.updated': return typeof meta.name === 'string' ? `renamed workspace to '${meta.name}'` : 'updated the workspace';
            default: return action;
        }
    };

    const appendItem = (item) => {
        const row = document.createElement('div');
        row.className = 'activity-item py-2 border-bottom';
        row.dataset.id = String(item.id);

        const actor = document.createTextNode(item.actor_name || 'Someone');
        const action = document.createTextNode(` ${humanize(item.action || '', item.meta || {})} `);
        const timestamp = document.createElement('small');
        timestamp.className = 'text-body-secondary';
        timestamp.textContent = item.created_at || '';
        row.append(actor, action, timestamp);
        feed.querySelector('.activity-empty')?.remove();
        feed.prepend(row);
    };

    const poll = async () => {
        if (document.visibilityState !== 'visible') return;
        try {
            const response = await fetch(`/w/${encodeURIComponent(slug)}/activity/feed?after=${lastId}`, {
                headers: { Accept: 'application/json' }
            });
            if (!response.ok) return;
            const data = await response.json();
            for (const item of data.items || []) {
                appendItem(item);
                lastId = Math.max(lastId, Number(item.id) || 0);
            }
            while (feed.querySelectorAll('.activity-item').length > 50) {
                feed.querySelector('.activity-item:last-child')?.remove();
            }
            feed.dataset.lastId = String(lastId);
        } catch (error) {
            console.error('Activity polling failed', error);
        }
    };

    const timer = window.setInterval(poll, interval);
    window.addEventListener('beforeunload', () => window.clearInterval(timer), { once: true });
})();
