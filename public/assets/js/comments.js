'use strict';

document.querySelectorAll('[data-reply-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const form = button.closest('.comment-card')?.querySelector('.reply-form');
        if (form) form.hidden = !form.hidden;
    });
});