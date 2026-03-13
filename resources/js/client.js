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
