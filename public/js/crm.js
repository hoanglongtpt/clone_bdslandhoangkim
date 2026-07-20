document.addEventListener('DOMContentLoaded', () => {
    const drawer = document.querySelector('[data-filter-drawer]');
    const backdrop = document.querySelector('[data-filter-backdrop]');
    const gallery = document.querySelector('[data-gallery-modal]');
    const galleryItems = [...document.querySelectorAll('[data-gallery-item]')];
    const galleryImage = gallery?.querySelector('[data-gallery-image]');
    const galleryCounter = gallery?.querySelector('[data-gallery-counter]');
    let currentImage = 0;

    const syncBodyLock = () => {
        const overlayOpen = drawer?.classList.contains('open') || gallery?.classList.contains('open');
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

    document.addEventListener('keydown', event => {
        if (gallery?.classList.contains('open')) {
            if (event.key === 'Escape') closeGallery();
            if (event.key === 'ArrowLeft') renderImage(currentImage - 1);
            if (event.key === 'ArrowRight') renderImage(currentImage + 1);
            return;
        }
        if (event.key === 'Escape') closeDrawer();
    });
});

