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
        if (typeof document !== 'undefined') {
            const boot = () => {
                this.ensureProgressBar();
                this.bindNavigationEvents();
                this.revealPage();
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', boot);
            } else {
                boot();
            }

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
        if (this.eventsBound) return;
        this.eventsBound = true;

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

        // Support clickable rows with data-href, data-url, clickable-row, and legacy onclick (CSP-safe)
        document.addEventListener('click', (e) => {
            // Ignore clicks originating inside interactive elements, buttons, forms, dropdowns, or modals
            if (e.target.closest('button, a, input, select, textarea, label, form, .btn, .btn-group, .dropdown, .dropdown-toggle, .dropdown-menu, .modal, .action-icon, .form-check, .form-check-input, .popover, [data-bs-toggle], [data-no-row-click]')) {
                return;
            }

            const clickableRow = e.target.closest('tr[data-href], tr[data-url], tr.clickable-row, tr.is-clickable, tr[data-campaign-id], tr[data-coupon-id], tr[onclick]');
            if (!clickableRow) return;

            // Support campaign & coupon modal triggers without inline onclick
            if (clickableRow.dataset.campaignId && typeof window.showCampaignDetails === 'function') {
                window.showCampaignDetails(clickableRow.dataset.campaignId);
                return;
            }
            if (clickableRow.dataset.couponId && typeof window.showCouponDetails === 'function') {
                window.showCouponDetails(clickableRow.dataset.couponId);
                return;
            }

            let href = clickableRow.getAttribute('data-href') || clickableRow.getAttribute('data-url');

            // Fallback for legacy inline onclick blocked by CSP
            if (!href && clickableRow.hasAttribute('onclick')) {
                const onclickAttr = clickableRow.getAttribute('onclick') || '';
                const match = onclickAttr.match(/window\.location(?:\.href)?\s*=\s*['"]([^'"]+)['"]/);
                if (match && match[1]) {
                    href = match[1];
                }
            }

            if (href && href !== '#' && !href.startsWith('javascript:')) {
                this.startProgress();
                window.location.href = href;
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
