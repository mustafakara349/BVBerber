/**
 * Employee Titles Management
 * Handles AJAX CRUD operations for employee titles within the modal.
 */
document.addEventListener('DOMContentLoaded', function () {
    const titlesModalEl = document.getElementById('titlesModal');
    const titlesList = document.getElementById('titlesList');
    const addTitleForm = document.getElementById('addTitleForm');

    // Only run if the modal exists on the page
    if (!titlesModalEl || !titlesList || !addTitleForm) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const storeUrl = addTitleForm.dataset.url || '/employee-titles';
    let hasChanges = false;

    // Reload page when modal is manually closed if changes occurred
    titlesModalEl.addEventListener('hidden.bs.modal', function () {
        if (hasChanges) {
            window.location.reload();
        }
    });

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function createTitleListItem(id, name) {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center py-2 px-3';
        li.dataset.id = id;
        li.innerHTML = `
            <span class="title-text fw-medium">${escapeHtml(name)}</span>
            <input type="text" class="form-control form-control-sm title-input d-none me-2" value="${escapeHtml(name)}" style="max-width: 60%;">
            <div class="d-inline-flex gap-1 align-items-center ms-2 flex-shrink-0">
                <button type="button" class="btn btn-outline-primary btn-sm rounded-circle p-2 border-0 edit-btn" data-id="${id}" title="Düzenle">
                    <i class="ti ti-pencil fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-success btn-sm rounded-circle p-2 border-0 save-btn d-none" data-id="${id}" title="Kaydet">
                    <i class="ti ti-check fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2 border-0 cancel-btn d-none" data-id="${id}" title="İptal">
                    <i class="ti ti-x fs-5"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-2 border-0 delete-btn" data-id="${id}" title="Sil">
                    <i class="ti ti-trash fs-5"></i>
                </button>
            </div>
        `;
        return li;
    }

    // ── YENİ UNVAN EKLE ──────────────────────────────────────────────────────
    addTitleForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const input = document.getElementById('newTitleName');
        const name = input?.value.trim();
        if (!name) return;

        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.textContent = '...';
        }

        try {
            const res = await fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name })
            });

            if (res.ok) {
                const data = await res.json();
                const emptyState = document.getElementById('emptyState');
                if (emptyState) emptyState.remove();

                const li = createTitleListItem(data.id, data.name);
                titlesList.appendChild(li);

                input.value = '';
                input.focus();
                hasChanges = true;
            } else {
                const body = await res.json().catch(() => ({}));
                alert('Hata: ' + (body.message ?? 'Eklenemedi.'));
            }
        } catch (err) {
            alert('Sunucuya bağlanılamadı.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Ekle';
            }
        }
    });

    // ── DÜZENLE / KAYDET / İPTAL / SİL ──────────────────────────────────────
    titlesList.addEventListener('click', async function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        const li = btn.closest('li');
        if (!li) return;
        const id = btn.dataset.id || li.dataset.id;

        // Düzenleme modunu aç
        if (btn.classList.contains('edit-btn')) {
            const titleText = li.querySelector('.title-text');
            const titleInput = li.querySelector('.title-input');
            const editBtn = li.querySelector('.edit-btn');
            const deleteBtn = li.querySelector('.delete-btn');
            const saveBtn = li.querySelector('.save-btn');
            const cancelBtn = li.querySelector('.cancel-btn');

            titleText.classList.add('d-none');
            titleInput.classList.remove('d-none');
            editBtn.classList.add('d-none');
            deleteBtn.classList.add('d-none');
            saveBtn.classList.remove('d-none');
            cancelBtn.classList.remove('d-none');
            titleInput.focus();
            titleInput.select();
            return;
        }

        // Düzenlemeyi iptal et
        if (btn.classList.contains('cancel-btn')) {
            const titleText = li.querySelector('.title-text');
            const titleInput = li.querySelector('.title-input');
            const editBtn = li.querySelector('.edit-btn');
            const deleteBtn = li.querySelector('.delete-btn');
            const saveBtn = li.querySelector('.save-btn');
            const cancelBtn = li.querySelector('.cancel-btn');

            titleInput.value = titleText.textContent.trim();
            titleText.classList.remove('d-none');
            titleInput.classList.add('d-none');
            editBtn.classList.remove('d-none');
            deleteBtn.classList.remove('d-none');
            saveBtn.classList.add('d-none');
            cancelBtn.classList.add('d-none');
            return;
        }

        // Değişiklikleri kaydet
        if (btn.classList.contains('save-btn')) {
            const titleInput = li.querySelector('.title-input');
            const newName = titleInput.value.trim();
            if (!newName) return;
            btn.disabled = true;

            try {
                const res = await fetch(`/employee-titles/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: newName })
                });

                if (res.ok) {
                    const data = await res.json();
                    const titleText = li.querySelector('.title-text');
                    const editBtn = li.querySelector('.edit-btn');
                    const deleteBtn = li.querySelector('.delete-btn');
                    const cancelBtn = li.querySelector('.cancel-btn');

                    titleText.textContent = data.name || newName;
                    titleInput.value = data.name || newName;

                    titleText.classList.remove('d-none');
                    titleInput.classList.add('d-none');
                    editBtn.classList.remove('d-none');
                    deleteBtn.classList.remove('d-none');
                    btn.classList.add('d-none');
                    cancelBtn.classList.add('d-none');
                    hasChanges = true;
                } else {
                    alert('Güncellenemedi.');
                }
            } catch (err) {
                alert('Sunucuya bağlanılamadı.');
            } finally {
                btn.disabled = false;
            }
            return;
        }

        // Unvanı sil
        if (btn.classList.contains('delete-btn')) {
            if (!confirm('Bu unvanı silmek istediğinize emin misiniz?\nBu unvana sahip çalışanların unvanı boşalacaktır.')) return;
            btn.disabled = true;

            try {
                const res = await fetch(`/employee-titles/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    li.remove();
                    hasChanges = true;
                    if (titlesList.querySelectorAll('li[data-id]').length === 0) {
                        const emptyLi = document.createElement('li');
                        emptyLi.className = 'list-group-item text-center text-muted';
                        emptyLi.id = 'emptyState';
                        emptyLi.textContent = 'Kayıtlı unvan bulunmuyor. Yukarıdan ekleyebilirsiniz.';
                        titlesList.appendChild(emptyLi);
                    }
                } else {
                    alert('Silinemedi.');
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Sunucuya bağlanılamadı.');
                btn.disabled = false;
            }
        }
    });

    // Enter ile kaydet, Esc ile iptal et
    titlesList.addEventListener('keydown', function (e) {
        if (e.target.classList.contains('title-input')) {
            const li = e.target.closest('li');
            if (e.key === 'Enter') {
                e.preventDefault();
                const saveBtn = li?.querySelector('.save-btn');
                if (saveBtn) saveBtn.click();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                const cancelBtn = li?.querySelector('.cancel-btn');
                if (cancelBtn) cancelBtn.click();
            }
        }
    });
});
