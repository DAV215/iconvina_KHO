(() => {
    const initProcessTracking = () => {
        document.querySelectorAll('[data-process-tracking]').forEach((tracking) => {
            const links = Array.from(tracking.querySelectorAll('[data-process-anchor]'));
            if (links.length === 0) {
                return;
            }

            const sectionMap = new Map();
            links.forEach((link) => {
                const anchor = link.getAttribute('data-process-anchor') || '';
                if (anchor === '') {
                    return;
                }

                const target = document.getElementById(anchor);
                if (!target) {
                    return;
                }

                sectionMap.set(anchor, { link, target });
            });

            links.forEach((link) => {
                link.addEventListener('click', (event) => {
                    const anchor = link.getAttribute('data-process-anchor') || '';
                    const pair = sectionMap.get(anchor);
                    if (!pair) {
                        return;
                    }

                    event.preventDefault();
                    pair.target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            const observer = new IntersectionObserver((entries) => {
                const visibleEntry = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((left, right) => right.intersectionRatio - left.intersectionRatio)[0];

                if (!visibleEntry) {
                    return;
                }

                links.forEach((link) => link.classList.remove('is-current-view'));
                const activeLink = tracking.querySelector('[data-process-anchor="' + visibleEntry.target.id + '"]');
                if (activeLink) {
                    activeLink.classList.add('is-current-view');
                }
            }, {
                rootMargin: '-20% 0px -55% 0px',
                threshold: [0.2, 0.35, 0.55],
            });

            sectionMap.forEach(({ target }) => observer.observe(target));
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProcessTracking, { once: true });
    } else {
        initProcessTracking();
    }
})();
