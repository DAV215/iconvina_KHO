(function () {
    const parseStorage = (key) => {
        try {
            return JSON.parse(window.localStorage.getItem(key) || '{}');
        } catch (error) {
            return {};
        }
    };

    const initColumnToggles = () => {
        document.querySelectorAll('[data-column-menu]').forEach((menu) => {
            const tableId = menu.getAttribute('data-column-menu');
            if (!tableId) {
                return;
            }

            const table = document.getElementById(tableId);
            if (!table) {
                return;
            }

            const storageKey = 'iconvina.columns.' + tableId;
            const saved = parseStorage(storageKey);
            const apply = (column, visible) => {
                table.querySelectorAll('[data-col="' + column + '"]').forEach((cell) => {
                    cell.style.display = visible ? '' : 'none';
                });
            };

            menu.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
                const column = checkbox.value;
                const visible = saved[column] !== false;
                checkbox.checked = visible;
                apply(column, visible);
                checkbox.addEventListener('change', () => {
                    saved[column] = checkbox.checked;
                    window.localStorage.setItem(storageKey, JSON.stringify(saved));
                    apply(column, checkbox.checked);
                });
            });
        });
    };

    const initFilterCollapseState = () => {
        document.querySelectorAll('[data-filter-collapse]').forEach((collapse) => {
            const storageKey = 'iconvina.filters.' + collapse.getAttribute('data-filter-collapse');
            if (!storageKey) {
                return;
            }

            const applyState = (expanded) => {
                collapse.classList.toggle('show', expanded);
                document.querySelectorAll('[data-bs-target="#' + collapse.id + '"], [href="#' + collapse.id + '"]').forEach((trigger) => {
                    trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    trigger.classList.toggle('is-expanded', expanded);
                });
            };

            const saved = window.localStorage.getItem(storageKey);
            if (saved === '1' || saved === '0') {
                applyState(saved === '1');
            }

            collapse.addEventListener('shown.bs.collapse', () => {
                window.localStorage.setItem(storageKey, '1');
                applyState(true);
            });

            collapse.addEventListener('hidden.bs.collapse', () => {
                window.localStorage.setItem(storageKey, '0');
                applyState(false);
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initColumnToggles();
            initFilterCollapseState();
        });
    } else {
        initColumnToggles();
        initFilterCollapseState();
    }
})();
