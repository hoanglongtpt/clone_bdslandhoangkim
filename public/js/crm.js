document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.querySelector('[data-filter-drawer]');
    const backdrop = document.querySelector('[data-filter-backdrop]');
    const gallery = document.querySelector('[data-gallery-modal]');
    const galleryItems = [...document.querySelectorAll('[data-gallery-item]')];
    const galleryImage = gallery?.querySelector('[data-gallery-image]');
    const galleryCounter = gallery?.querySelector('[data-gallery-counter]');
    const notesViewModal = document.querySelector('[data-notes-view-modal]');
    const noteAddModal = document.querySelector('[data-note-add-modal]');
    const noteAddForm = noteAddModal?.querySelector('[data-note-add-form]');
    const noteTextarea = noteAddModal?.querySelector('[data-note-textarea]');
    let currentImage = 0;
    let previousQuickReason = '';

    const syncBodyLock = () => {
        const overlayOpen = drawer?.classList.contains('open') || gallery?.classList.contains('open')
            || notesViewModal?.classList.contains('open') || noteAddModal?.classList.contains('open');
        document.body.classList.toggle('no-scroll', Boolean(overlayOpen));
    };

    const openDrawer = () => {
        drawer?.classList.add('open');
        backdrop?.classList.add('open');
        syncBodyLock();
    };
    const closeDrawer = () => {
        drawer?.classList.remove('open');
        backdrop?.classList.remove('open');
        syncBodyLock();
    };

    const renderImage = index => {
        if (!galleryItems.length || !galleryImage) return;
        currentImage = (index + galleryItems.length) % galleryItems.length;
        const item = galleryItems[currentImage];
        galleryImage.src = item.dataset.gallerySrc;
        if (galleryCounter) galleryCounter.textContent = `${currentImage + 1} / ${galleryItems.length}`;
    };
    const openGallery = index => {
        if (!gallery) return;
        renderImage(index);
        gallery.classList.add('open');
        gallery.setAttribute('aria-hidden', 'false');
        syncBodyLock();
        gallery.querySelector('[data-gallery-close]')?.focus();
    };
    const closeGallery = () => {
        if (!gallery) return;
        gallery.classList.remove('open');
        gallery.setAttribute('aria-hidden', 'true');
        if (galleryImage) galleryImage.src = '';
        syncBodyLock();
    };

    const openModal = modal => {
        modal?.classList.add('open');
        modal?.setAttribute('aria-hidden', 'false');
        syncBodyLock();
        modal?.querySelector('.app-modal-close')?.focus();
    };
    const closeModal = modal => {
        modal?.classList.remove('open');
        modal?.setAttribute('aria-hidden', 'true');
        syncBodyLock();
    };

    const makeNoteCard = note => {
        const article = document.createElement('article');
        article.className = 'notes-modal-card';
        const meta = document.createElement('div');
        meta.className = 'notes-modal-meta';
        const date = document.createElement('time');
        date.textContent = `◷ ${note.note_date || 'Không rõ thời gian'}`;
        const author = document.createElement('strong');
        author.textContent = note.author || 'Không rõ';
        const content = document.createElement('p');
        content.textContent = note.note || '—';
        meta.append(date, author);
        article.append(meta, content);
        return article;
    };

    document.querySelectorAll('[data-view-notes]').forEach(button => button.addEventListener('click', async () => {
        if (!notesViewModal) return;
        notesViewModal.querySelector('[data-notes-view-title]').textContent = button.dataset.noteTitle || 'Ghi chú';
        notesViewModal.querySelector('[data-notes-view-code]').textContent = button.dataset.propertyCode || '';
        const body = notesViewModal.querySelector('[data-notes-view-body]');
        body.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'muted';
        loading.textContent = 'Đang tải ghi chú...';
        body.append(loading);
        openModal(notesViewModal);
        try {
            const response = await fetch(button.dataset.notesUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            body.replaceChildren();
            if (!payload.notes.length) {
                const empty = document.createElement('p');
                empty.className = 'muted notes-modal-empty';
                empty.textContent = 'Chưa có ghi chú.';
                body.append(empty);
                return;
            }
            payload.notes.forEach(note => body.append(makeNoteCard(note)));
        } catch (error) {
            body.replaceChildren();
            const message = document.createElement('p');
            message.className = 'inline-error';
            message.textContent = 'Không tải được ghi chú. Vui lòng thử lại.';
            body.append(message);
        }
    }));

    document.querySelectorAll('[data-add-note]').forEach(button => button.addEventListener('click', () => {
        if (!noteAddModal || !noteAddForm) return;
        noteAddForm.action = button.dataset.noteAction;
        noteAddModal.querySelector('[data-note-group-input]').value = button.dataset.noteGroup;
        noteAddModal.querySelector('[data-note-add-title]').textContent = button.dataset.noteTitle || 'Thêm ghi chú';
        noteAddModal.querySelector('[data-note-add-code]').textContent = button.dataset.propertyCode || '';
        noteAddForm.reset();
        noteAddModal.querySelector('[data-note-group-input]').value = button.dataset.noteGroup;
        previousQuickReason = '';
        openModal(noteAddModal);
    }));

    noteAddModal?.querySelectorAll('[data-note-reason]').forEach(radio => radio.addEventListener('change', () => {
        if (!noteTextarea) return;
        const current = noteTextarea.value.trim();
        if (!current || current === previousQuickReason) noteTextarea.value = radio.value;
        previousQuickReason = radio.value;
        noteTextarea.focus();
    }));

    document.querySelectorAll('[data-open-filter]').forEach(button => button.addEventListener('click', openDrawer));
    document.querySelectorAll('[data-close-filter]').forEach(button => button.addEventListener('click', closeDrawer));
    backdrop?.addEventListener('click', closeDrawer);
    galleryItems.forEach((item, index) => item.addEventListener('click', () => openGallery(index)));
    gallery?.querySelector('[data-gallery-close]')?.addEventListener('click', closeGallery);
    gallery?.querySelector('[data-gallery-prev]')?.addEventListener('click', () => renderImage(currentImage - 1));
    gallery?.querySelector('[data-gallery-next]')?.addEventListener('click', () => renderImage(currentImage + 1));
    gallery?.addEventListener('click', event => {
        if (event.target === gallery) closeGallery();
    });
    document.querySelectorAll('[data-close-notes-view]').forEach(button => button.addEventListener('click', () => closeModal(notesViewModal)));
    document.querySelectorAll('[data-close-note-add]').forEach(button => button.addEventListener('click', () => closeModal(noteAddModal)));
    notesViewModal?.addEventListener('click', event => {
        if (event.target === notesViewModal) closeModal(notesViewModal);
    });
    noteAddModal?.addEventListener('click', event => {
        if (event.target === noteAddModal) closeModal(noteAddModal);
    });

    document.addEventListener('keydown', event => {
        if (gallery?.classList.contains('open')) {
            if (event.key === 'Escape') closeGallery();
            if (event.key === 'ArrowLeft') renderImage(currentImage - 1);
            if (event.key === 'ArrowRight') renderImage(currentImage + 1);
            return;
        }
        if (notesViewModal?.classList.contains('open')) {
            if (event.key === 'Escape') closeModal(notesViewModal);
            return;
        }
        if (noteAddModal?.classList.contains('open')) {
            if (event.key === 'Escape') closeModal(noteAddModal);
            return;
        }
        if (event.key === 'Escape') closeDrawer();
    });
});

