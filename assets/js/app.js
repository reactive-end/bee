/**
 * Proyecto Bee - JavaScript Global
 * Utilidades compartidas: sidebar, modales, confirmaciones, tablas
 */

(function () {
    'use strict';

    // ─── Sidebar Toggle (Mobile) ────────────────

    function initSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        const toggleBtn = document.querySelector('.menu-toggle');

        if (!sidebar || !toggleBtn) return;

        function openSidebar() {
            sidebar.classList.add('open');
            if (backdrop) backdrop.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            if (backdrop) backdrop.classList.remove('active');
            document.body.style.overflow = '';
        }

        toggleBtn.addEventListener('click', function () {
            sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
        });

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                closeSidebar();
            }
        });
    }

    // ─── Dropdown Menus ─────────────────────────

    function initDropdowns() {
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.dropdown-trigger');
            if (!trigger) {
                document.querySelectorAll('.dropdown-menu.active').forEach(function (menu) {
                    menu.classList.remove('active');
                });
                return;
            }

            e.stopPropagation();
            var menu = trigger.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                var isActive = menu.classList.contains('active');
                document.querySelectorAll('.dropdown-menu.active').forEach(function (m) {
                    m.classList.remove('active');
                });
                if (!isActive) {
                    menu.classList.add('active');
                }
            }
        });
    }

    // ─── Modal ──────────────────────────────────

    window.openModal = function (modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus first input
        var firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
        if (firstInput) {
            setTimeout(function () { firstInput.focus(); }, 100);
        }
    };

    window.closeModal = function (modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    function initModals() {
        document.addEventListener('click', function (e) {
            // Close on overlay click
            if (e.target.classList.contains('modal-overlay') && e.target.classList.contains('active')) {
                closeModal(e.target.id);
            }
        });

        // Close buttons
        document.querySelectorAll('.modal-close, [data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var modal = this.closest('.modal-overlay');
                if (modal) closeModal(modal.id);
            });
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var activeModal = document.querySelector('.modal-overlay.active');
                if (activeModal) closeModal(activeModal.id);
            }
        });
    }

    // ─── Confirmaciones ─────────────────────────

    function initConfirmations() {
        document.querySelectorAll('[data-confirm]').forEach(function (el) {
            el.addEventListener('click', function (e) {
                var message = this.getAttribute('data-confirm') || '¿Esta seguro de realizar esta accion?';
                if (!confirm(message)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
    }

    // ─── Tabla: Seleccionar todos ───────────────

    function initSelectAll() {
        document.querySelectorAll('.select-all-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                var table = this.closest('table');
                if (!table) return;
                var checkboxes = table.querySelectorAll('tbody input[type="checkbox"].row-checkbox');
                checkboxes.forEach(function (cb) {
                    cb.checked = checkbox.checked;
                });
            });
        });
    }

    // ─── Busqueda en tiempo real ────────────────

    function initTableSearch() {
        document.querySelectorAll('.search-input input[data-table]').forEach(function (input) {
            var tableId = input.getAttribute('data-table');
            var table = document.getElementById(tableId);
            if (!table) return;

            input.addEventListener('input', function () {
                var query = this.value.toLowerCase().trim();
                var rows = table.querySelectorAll('tbody tr');
                var visibleCount = 0;

                rows.forEach(function (row) {
                    var text = row.textContent.toLowerCase();
                    if (text.indexOf(query) > -1) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update or create no-results message
                var noResults = table.querySelector('.no-results-row');
                if (visibleCount === 0) {
                    if (!noResults) {
                        var tbody = table.querySelector('tbody');
                        var colSpan = table.querySelectorAll('thead th').length || 1;
                        var tr = document.createElement('tr');
                        tr.className = 'no-results-row';
                        tr.innerHTML = '<td colspan="' + colSpan + '" style="text-align:center;padding:2rem;color:var(--color-muted)">No se encontraron resultados</td>';
                        tbody.appendChild(tr);
                    }
                } else if (noResults) {
                    noResults.remove();
                }
            });
        });
    }

    // ─── Alertas auto-cerrables ─────────────────

    function initAutoDismiss() {
        document.querySelectorAll('.alert.auto-dismiss').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function () {
                    if (alert.parentNode) alert.remove();
                }, 300);
            }, 5000);
        });
    }

    // ─── Reloj en header ────────────────────────

    function initClock() {
        var clock = document.querySelector('.header-time');
        if (!clock) return;

        function update() {
            var now = new Date();
            var options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            clock.textContent = now.toLocaleDateString('es-PE', {
                weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
            }) + ' - ' + now.toLocaleTimeString('es-PE', options);
        }

        update();
        setInterval(update, 30000);
    }

    // ─── Numeric Inputs ─────────────────────────

    function formatCurrency(value) {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        }).format(value);
    }

    window.formatCurrency = formatCurrency;

    // ─── Init ───────────────────────────────────

    document.addEventListener('DOMContentLoaded', function () {
        initSidebar();
        initDropdowns();
        initModals();
        initConfirmations();
        initSelectAll();
        initTableSearch();
        initAutoDismiss();
        initClock();
    });

})();
