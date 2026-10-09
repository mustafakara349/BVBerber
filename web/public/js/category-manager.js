/**
 * Category Manager — Reusable AJAX CRUD modal handler.
 *
 * Usage: Include this file, then call:
 *   CategoryManager.init({
 *     modalId:   'categoriesModal',
 *     listId:    'categoriesList',
 *     formId:    'addCategoryForm',
 *     inputId:   'newCategoryName',
 *     storeUrl:  '/product-categories',          // from data-url on form
 *     updateUrl: (id) => `/product-categories/${id}`,
 *     destroyUrl:(id) => `/product-categories/${id}`,
 *   });
 *
 * Or simply include the file — it auto-discovers any element with
 * data-cm-modal="true" on the modal and reads config from data attributes.
 */
(function (global) {
    'use strict';

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Build a single <li> row for the category list.
     */
    function buildListItem(id, name) {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
        li.dataset.id = id;
        li.innerHTML = `
            <span class="cat-text fw-medium">${escapeHtml(name)}</span>
            <input type="text" class="form-control form-control-sm cat-input d-none me-2" value="${escapeHtml(name)}" style="max-width:60%;" aria-label="Kategori Adı">
            <div class="d-inline-flex gap-1 align-items-center ms-2 flex-shrink-0">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0 cm-edit-btn" data-id="${id}" title="Düzenle" aria-label="Düzenle">
                    <i class="ti ti-pencil fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-success btn-sm rounded-circle p-2 border-0 cm-save-btn d-none" data-id="${id}" title="Kaydet" aria-label="Kaydet">
                    <i class="ti ti-check fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2 border-0 cm-cancel-btn d-none" data-id="${id}" title="İptal" aria-label="İptal">
                    <i class="ti ti-x fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 cm-delete-btn" data-id="${id}" title="Sil" aria-label="Sil">
                    <i class="ti ti-trash fs-5"></i>
                </button>
            </div>
        `;
        return li;
    }

    /**
     * Show or hide edit controls for a list item.
     */
    function setEditMode(li, active) {
        li.querySelector('.cat-text').classList.toggle('d-none', active);
        li.querySelector('.cat-input').classList.toggle('d-none', !active);
        li.querySelector('.cm-edit-btn').classList.toggle('d-none', active);
        li.querySelector('.cm-delete-btn').classList.toggle('d-none', active);
        li.querySelector('.cm-save-btn').classList.toggle('d-none', !active);
        li.querySelector('.cm-cancel-btn').classList.toggle('d-none', !active);
        if (active) {
            const input = li.querySelector('.cat-input');
            input.focus();
            input.select();
        }
    }

    /**
     * Core initializer.
     * @param {object} opts
     * @param {string} opts.modalId
     * @param {string} opts.listId
     * @param {string} opts.formId
     * @param {string} opts.inputId
     * @param {string} opts.storeUrl
     * @param {function} opts.updateUrl  — receives id, returns URL string
     * @param {function} opts.destroyUrl — receives id, returns URL string
     */
    function init(opts) {
        const csrf       = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const modalEl    = document.getElementById(opts.modalId);
        const list       = document.getElementById(opts.listId);
        const form       = document.getElementById(opts.formId);
        const nameInput  = document.getElementById(opts.inputId);

        if (!modalEl || !list || !form || !nameInput) return;

        let hasChanges = false;

        // ── Reload page on manual close if changes happened ───────────────────
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (hasChanges) window.location.reload();
        });

        // ── Helper: empty state ───────────────────────────────────────────────
        function showEmpty() {
            if (!list.querySelector('.cm-empty')) {
                const li = document.createElement('li');
                li.className = 'list-group-item text-center text-muted cm-empty';
                li.textContent = 'Kayıtlı kategori bulunmuyor. Yukarıdan ekleyebilirsiniz.';
                list.appendChild(li);
            }
        }

        function removeEmpty() {
            list.querySelector('.cm-empty')?.remove();
        }

        // ── ADD ───────────────────────────────────────────────────────────────
        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const name = nameInput.value.trim();
            if (!name) return;

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = '...';

            try {
                const res = await fetch(opts.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name }),
                });

                if (res.ok) {
                    const data = await res.json();
                    removeEmpty();
                    list.appendChild(buildListItem(data.id, data.name));
                    nameInput.value = '';
                    nameInput.focus();
                    hasChanges = true;
                } else {
                    const body = await res.json().catch(() => ({}));
                    alert('Hata: ' + (body.message ?? 'Eklenemedi.'));
                }
            } catch {
                alert('Sunucuya bağlanılamadı.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Ekle';
            }
        });

        // ── EDIT / SAVE / CANCEL / DELETE (event delegation) ─────────────────
        list.addEventListener('click', async function (e) {
            const btn = e.target.closest('button');
            if (!btn) return;
            const li = btn.closest('li[data-id]');
            if (!li) return;
            const id = li.dataset.id;

            if (btn.classList.contains('cm-edit-btn')) {
                setEditMode(li, true);
                return;
            }

            if (btn.classList.contains('cm-cancel-btn')) {
                // Restore original text from the visible span
                li.querySelector('.cat-input').value = li.querySelector('.cat-text').textContent.trim();
                setEditMode(li, false);
                return;
            }

            if (btn.classList.contains('cm-save-btn')) {
                const newName = li.querySelector('.cat-input').value.trim();
                if (!newName) return;
                btn.disabled = true;

                try {
                    const res = await fetch(opts.updateUrl(id), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ name: newName }),
                    });

                    if (res.ok) {
                        const data = await res.json();
                        const saved = data.name || newName;
                        li.querySelector('.cat-text').textContent = saved;
                        li.querySelector('.cat-input').value = saved;
                        setEditMode(li, false);
                        hasChanges = true;
                    } else {
                        alert('Güncellenemedi.');
                    }
                } catch {
                    alert('Sunucuya bağlanılamadı.');
                } finally {
                    btn.disabled = false;
                }
                return;
            }

            if (btn.classList.contains('cm-delete-btn')) {
                if (!confirm('Bu kategoriyi silmek istediğinize emin misiniz?\nBağlı ürünler kategorisiz kalabilir.')) return;
                btn.disabled = true;

                try {
                    const res = await fetch(opts.destroyUrl(id), {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                        },
                    });

                    if (res.ok) {
                        li.remove();
                        hasChanges = true;
                        if (!list.querySelector('li[data-id]')) showEmpty();
                    } else {
                        alert('Silinemedi.');
                        btn.disabled = false;
                    }
                } catch {
                    alert('Sunucuya bağlanılamadı.');
                    btn.disabled = false;
                }
            }
        });

        // ── Keyboard shortcuts (Enter = save, Esc = cancel) ───────────────────
        list.addEventListener('keydown', function (e) {
            if (!e.target.classList.contains('cat-input')) return;
            const li = e.target.closest('li[data-id]');
            if (!li) return;
            if (e.key === 'Enter') { e.preventDefault(); li.querySelector('.cm-save-btn')?.click(); }
            if (e.key === 'Escape') { e.preventDefault(); li.querySelector('.cm-cancel-btn')?.click(); }
        });
    }

    // Expose globally
    global.CategoryManager = { init };

}(window));
