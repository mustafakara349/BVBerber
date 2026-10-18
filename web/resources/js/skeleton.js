/**
 * B&V Barber Skeleton Loader & Navigation Transition Engine
 */

class SkeletonLoaderManager {
    constructor() {
        this.progressBar = null;
        this.progressTimer = null;
        this.init();
    }

    init() {
        // Ensure progress element exists
        if (typeof document !== 'undefined') {
            document.addEventListener('DOMContentLoaded', () => {
                this.ensureProgressBar();
                this.bindNavigationEvents();
                this.revealPage();
            });

            window.addEventListener('pageshow', () => {
                this.finishProgress();
            });
        }
    }

    ensureProgressBar() {
        let bar = document.getElementById('skeletonGlobalProgress');
        if (!bar) {
            bar = document.createElement('div');
            bar.id = 'skeletonGlobalProgress';
            document.body.appendChild(bar);
        }
        this.progressBar = bar;
    }

    startProgress() {
        if (!this.progressBar) this.ensureProgressBar();
        if (!this.progressBar) return;

        clearInterval(this.progressTimer);
        this.progressBar.className = 'running';
        this.progressBar.style.width = '15%';

        let width = 15;
        this.progressTimer = setInterval(() => {
            if (width < 85) {
                width += (85 - width) * 0.15;
                if (this.progressBar) {
                    this.progressBar.style.width = `${width}%`;
                }
            }
        }, 150);
    }

    finishProgress() {
        if (!this.progressBar) return;
        clearInterval(this.progressTimer);
        this.progressBar.style.width = '100%';
        this.progressBar.className = 'done';
        setTimeout(() => {
            if (this.progressBar && this.progressBar.classList.contains('done')) {
                this.progressBar.style.width = '0%';
                this.progressBar.className = '';
            }
        }, 400);
    }

    bindNavigationEvents() {
        document.addEventListener('click', (e) => {
            const anchor = e.target.closest('a');
            if (!anchor) return;

            // Check if link should be bypassed
            const href = anchor.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('tel:') || href.startsWith('mailto:')) {
                return;
            }

            // Exclude modal triggers, download links, new tab links, or custom no-skeleton targets
            if (
                anchor.target === '_blank' ||
                anchor.hasAttribute('download') ||
                anchor.hasAttribute('data-bs-toggle') ||
                anchor.hasAttribute('data-no-skeleton') ||
                anchor.closest('.modal') ||
                anchor.classList.contains('btn-reject-trigger')
            ) {
                return;
            }

            // Check if same origin
            try {
                const targetUrl = new URL(anchor.href, window.location.origin);
                if (targetUrl.origin === window.location.origin && (targetUrl.pathname !== window.location.pathname || targetUrl.search !== window.location.search)) {
                    this.startProgress();
                }
            } catch (err) {
                // Ignore invalid URLs
            }
        });

        // Support clickable rows with data-href
        document.addEventListener('click', (e) => {
            const clickableRow = e.target.closest('[data-href]');
            if (clickableRow && !e.target.closest('button, a, input, select, .action-icon, .form-check-input')) {
                const href = clickableRow.getAttribute('data-href');
                if (href) {
                    this.startProgress();
                    window.location.href = href;
                }
            }
        });

        // Also track form submissions for visual feedback
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form && !form.hasAttribute('data-no-skeleton') && !form.classList.contains('ajax-form')) {
                this.startProgress();
            }
        });
    }

    revealPage() {
        const mainContent = document.getElementById('content');
        if (mainContent) {
            mainContent.classList.add('skeleton-content-fade');
            requestAnimationFrame(() => {
                mainContent.style.opacity = '1';
            });
        }
        this.finishProgress();
    }

    /**
     * Helper to show skeleton in a specific container
     */
    show(target, skeletonHtml) {
        const el = typeof target === 'string' ? document.querySelector(target) : target;
        if (!el) return;

        el.dataset.previousContent = el.innerHTML;
        el.classList.add('is-skeleton-loading');
        el.setAttribute('aria-busy', 'true');
        el.innerHTML = skeletonHtml;
    }

    /**
     * Helper to restore content in a container
     */
    hide(target, newContentHtml = null) {
        const el = typeof target === 'string' ? document.querySelector(target) : target;
        if (!el) return;

        el.classList.remove('is-skeleton-loading');
        el.removeAttribute('aria-busy');

        if (newContentHtml !== null) {
            el.innerHTML = newContentHtml;
        } else if (el.dataset.previousContent) {
            el.innerHTML = el.dataset.previousContent;
            delete el.dataset.previousContent;
        }
    }
}

const SkeletonLoader = new SkeletonLoaderManager();
window.SkeletonLoader = SkeletonLoader;
export default SkeletonLoader;
