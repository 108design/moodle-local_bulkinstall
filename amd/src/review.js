/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/review.js.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
/** Client-side visibility only; server-side validation remains authoritative. */
export const init = () => {
    const root = document.querySelector('.local-bulkinstall-review');
    if (!root || root.dataset.biReady) {
        return;
    }
    root.dataset.biReady = '1';
    const filters = root.querySelector('.bi-filters');
    if (filters) {
        filters.hidden = false;
        filters.addEventListener('click', event => {
            const button = event.target.closest('[data-bi-filter]');
            if (!button) {
                return;
            }
            filters.querySelectorAll('button').forEach(control => {
                control.setAttribute('aria-pressed', String(control === button));
                control.classList.toggle('active', control === button);
            });
            root.querySelectorAll('[data-bi-state]').forEach(row => {
                row.hidden = button.dataset.biFilter !== 'all' && row.dataset.biState !== button.dataset.biFilter;
            });
        });
    }
    root.querySelectorAll('[data-bi-open]').forEach(link => {
        const details = document.getElementById(link.dataset.biOpen);
        if (!details) {
            return;
        }
        link.setAttribute('role', 'button');
        link.setAttribute('aria-controls', details.id);
        link.setAttribute('aria-expanded', String(details.open));
        link.addEventListener('click', event => {
            event.preventDefault();
            details.open = !details.open;
        });
        link.addEventListener('keydown', event => {
            if (event.key === ' ') {
                event.preventDefault();
                link.click();
            }
        });
        details.addEventListener('toggle', () => link.setAttribute('aria-expanded', String(details.open)));
    });
};
