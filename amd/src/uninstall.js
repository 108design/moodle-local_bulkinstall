/**
 * This file is part of a 108design source-available software product.
 *
 * Use and modification are permitted only under the Software License included
 * with this distribution. See LICENSE.md for the full terms.
 *
 * File documentation for amd/src/uninstall.js.
 *
 * @package   local_bulkinstall
 * @copyright 2026 Andreas Giesen
 * @license   See LICENSE.md for the full terms.
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
/** Display-only filters. Hidden checked components remain selected and are always included in review. */
export const init = () => {
    const page = document.querySelector('.local-bulkinstall-uninstall');
    if (!page || !page.querySelector('[data-uninstall-row]')) {
        return;
    }
    const rows = [...page.querySelectorAll('[data-uninstall-row]')];
    const boxes = [...page.querySelectorAll('[data-uninstall-select]:not(:disabled)')];
    const search = page.querySelector('[data-uninstall-search]');
    const type = page.querySelector('[data-uninstall-type]');
    const state = page.querySelector('[data-uninstall-state]');
    const count = page.querySelector('[data-uninstall-count]');
    const update = () => {
        const query = search.value.trim().toLocaleLowerCase();
        rows.forEach(row => {
            row.hidden = !row.dataset.search.includes(query)
                || (type.value !== '' && type.value !== row.dataset.type)
                || (state.value !== '' && state.value !== row.dataset.status);
        });
        const selected = boxes.filter(box => box.checked);
        const hidden = selected.filter(box => box.closest('[data-uninstall-row]').hidden);
        count.textContent = count.dataset.template.replace('{selected}', selected.length).replace('{hidden}', hidden.length);
        page.querySelector('[data-uninstall-empty]').hidden = rows.some(row => !row.hidden);
    };
    page.querySelector('[data-uninstall-filters]').hidden = false;
    page.querySelector('[data-uninstall-tools]').hidden = false;
    search.addEventListener('input', update);
    type.addEventListener('change', update);
    state.addEventListener('change', update);
    boxes.forEach(box => box.addEventListener('change', update));
    page.querySelector('[data-uninstall-all]').addEventListener('click', () => {
        boxes.filter(box => !box.closest('[data-uninstall-row]').hidden).forEach(box => { box.checked = true; });
        update();
    });
    page.querySelector('[data-uninstall-clear]').addEventListener('click', () => {
        boxes.forEach(box => { box.checked = false; });
        update();
    });
    update();
};
