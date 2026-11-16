'use strict';

(() => {
    const textarea = document.getElementById('document-content');
    const preview = document.getElementById('markdown-preview');
    if (!textarea || !preview || !window.marked) return;

    // Client-side preview uses marked without sanitizer because the user is previewing their own unsaved input.
    // The saved version is rendered server-side via CommonMark with html_input=escape, which is what other users will see.
    let timer = 0;
    const render = () => {
        preview.innerHTML = window.marked.parse(textarea.value);
    };
    textarea.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(render, 200);
    });
    render();
})();
