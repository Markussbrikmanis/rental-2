import DataTable from 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.css';

const lvLanguage = {
    emptyTable: 'Tabulā nav datu',
    info: 'Rāda _START_ līdz _END_ no _TOTAL_ ierakstiem',
    infoEmpty: 'Rāda 0 līdz 0 no 0 ierakstiem',
    infoFiltered: '(atlasīti no _MAX_ kopējiem ierakstiem)',
    lengthMenu: 'Rādīt _MENU_ ierakstus',
    loadingRecords: 'Notiek ielāde...',
    processing: 'Apstrādā...',
    search: 'Meklēt:',
    zeroRecords: 'Atbilstoši ieraksti netika atrasti',
    paginate: {
        first: 'Pirmā',
        last: 'Pēdējā',
        next: 'Nākamā',
        previous: 'Iepriekšējā',
    },
};

const initColumnFilters = (table, dataTable) => {
    const headerRows = table.tHead?.rows;

    if (!headerRows || headerRows.length < 2) {
        return;
    }

    Array.from(headerRows[1].cells).forEach((cell, index) => {
        const field = cell.querySelector('input, select');

        if (!field) {
            return;
        }

        const triggerSearch = () => {
            const value = field.value ?? '';
            dataTable.column(index).search(value).draw();
        };

        if (field.tagName === 'INPUT') {
            let debounceTimer;

            field.addEventListener('input', () => {
                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(triggerSearch, 250);
            });
        }

        field.addEventListener('change', triggerSearch);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-client-shell]');
    const menuToggle = document.querySelector('[data-client-menu-toggle]');
    const closeTriggers = document.querySelectorAll('[data-client-menu-close], [data-client-nav-link]');

    if (shell && menuToggle) {
        const setMenuState = (isOpen) => {
            shell.classList.toggle('is-menu-open', isOpen);
            menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            document.body.classList.toggle('client-menu-open', isOpen);
        };

        menuToggle.addEventListener('click', () => {
            setMenuState(!shell.classList.contains('is-menu-open'));
        });

        closeTriggers.forEach((element) => {
            element.addEventListener('click', () => setMenuState(false));
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setMenuState(false);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                setMenuState(false);
            }
        });
    }

    document.querySelectorAll('[data-datatable]').forEach((table) => {
        const dataTable = new DataTable(table, {
            language: lvLanguage,
            orderCellsTop: true,
            pageLength: Number(table.dataset.pageLength || 20),
            paging: true,
            searching: true,
            lengthChange: false,
            responsive: false,
        });

        initColumnFilters(table, dataTable);
    });
});
