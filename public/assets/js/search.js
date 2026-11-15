'use strict';

(() => {
    const input = document.getElementById('nav-search');
    const dropdown = document.getElementById('nav-search-results');
    if (!input || !dropdown) return;

    let timer = 0;

    const hide = () => dropdown.classList.remove('show');

    const highlightHtml = (snippet) => snippet || '';

    const render = (items) => {
        dropdown.replaceChildren();
        for (const item of items) {
            const link = document.createElement('a');
            link.className = 'dropdown-item';
            link.href = `/w/${encodeURIComponent(item.workspace_slug)}/documents/${encodeURIComponent(item.id)}`;

            const title = document.createElement('div');
            title.className = 'fw-semibold';
            title.textContent = item.title || '';

            const workspace = document.createElement('small');
            workspace.textContent = item.workspace_name || '';

            const snippet = document.createElement('div');
            snippet.className = 'small text-muted';
            // The backend escapes snippet text and only re-adds literal <mark> tags, so innerHTML is safe here.
            snippet.innerHTML = highlightHtml(item.snippet);

            link.append(title, workspace, snippet);
            dropdown.append(link);
        }
        dropdown.classList.toggle('show', items.length > 0);
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        const query = input.value.trim();
        if (query.length < 2) {
            hide();
            return;
        }
        timer = window.setTimeout(async () => {
            try {
                const response = await fetch(`${input.dataset.api}?q=${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' }
                });
                if (!response.ok) {
                    hide();
                    return;
                }
                const data = await response.json();
                render(data.results || []);
            } catch (error) {
                console.error('Search request failed', error);
                hide();
            }
        }, 300);
    });

    document.addEventListener('click', (event) => {
        if (!input.parentElement?.parentElement?.contains(event.target)) hide();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') hide();
    });
})();
