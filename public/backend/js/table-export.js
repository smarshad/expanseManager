(function ($) {
    'use strict';

    if (!$ || !$.fn.DataTable) return;

    const cleanText = value => {
        if (value === null || value === undefined) return '';
        const holder = document.createElement('div');
        holder.innerHTML = String(value);
        return (holder.textContent || holder.innerText || '').replace(/\s+/g, ' ').trim();
    };

    const nestedValue = (object, path) => {
        if (!path) return '';
        return String(path).split('.').reduce((value, key) => value == null ? '' : value[key], object);
    };

    function exportName(table) {
        const heading = table.closest('.card-box, .card, .content').find('.header-title, .page-title, h4').first().text().trim();
        return (heading || document.title || 'table-export').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
    }

    async function fetchRows(api) {
        const settings = api.settings()[0];
        if (!settings.oFeatures.bServerSide) {
            return api.rows({ search: 'applied' }).data().toArray();
        }

        const ajax = settings.ajax || settings.sAjaxSource;
        const url = typeof ajax === 'string' ? ajax : ajax && ajax.url;
        if (!url) return api.rows({ page: 'current' }).data().toArray();

        const target = new URL(url, window.location.origin);
        const lastRequest = settings.oAjaxData || {};
        Object.entries(lastRequest).forEach(([key, value]) => {
            if (value !== null && value !== undefined && typeof value !== 'object') {
                target.searchParams.set(key, value);
            }
        });
        target.searchParams.set('page', '1');
        target.searchParams.set('per_page', '100000');
        target.searchParams.set('search', api.search() || '');
        target.searchParams.set('draw', '1');

        const response = await fetch(target.toString(), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!response.ok) throw new Error('Export data could not be loaded.');
        const payload = await response.json();
        return payload.data || [];
    }

    function displayRows(api, rows) {
        const settings = api.settings()[0];
        const columns = settings.aoColumns.map((column, index) => ({
            column,
            index,
            title: cleanText($(column.nTh).text()) || `Column ${index + 1}`
        })).filter(item => !/^(action|actions)$/i.test(item.title));

        return rows.map((row, rowIndex) => {
            const output = {};
            columns.forEach(item => {
                let value;
                if (/^(sr\.?\s*no\.?|#)$/i.test(item.title)) {
                    value = rowIndex + 1;
                } else if (typeof item.column.fnGetData === 'function') {
                    try {
                        value = item.column.fnGetData(row, 'display', {
                            settings,
                            row: rowIndex,
                            col: item.index
                        });
                    } catch (error) {
                        value = typeof item.column.mData === 'string' ? nestedValue(row, item.column.mData) : '';
                    }
                } else {
                    value = typeof item.column.mData === 'string' ? nestedValue(row, item.column.mData) : '';
                }
                output[item.title] = cleanText(value);
            });
            return output;
        });
    }

    function download(content, mime, filename) {
        const blob = new Blob([content], { type: mime });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(link.href);
    }

    function csvValue(value) {
        return `"${String(value ?? '').replace(/"/g, '""')}"`;
    }

    function printRows(title, rows) {
        const headers = rows[0] ? Object.keys(rows[0]) : [];
        const popup = window.open('', '_blank', 'width=1100,height=750');
        if (!popup) throw new Error('Allow popups to print this table.');
        popup.document.write(`<!doctype html><html><head><title>${cleanText(title)}</title><style>body{font-family:Arial,sans-serif;padding:24px;color:#172033}h2{margin:0 0 18px}table{width:100%;border-collapse:collapse;font-size:12px}th,td{border:1px solid #d9e0ea;padding:7px;text-align:left}th{background:#f1f5f9}@media print{button{display:none}}</style></head><body><h2>${cleanText(title)}</h2><table><thead><tr>${headers.map(h => `<th>${cleanText(h)}</th>`).join('')}</tr></thead><tbody>${rows.map(row => `<tr>${headers.map(h => `<td>${cleanText(row[h])}</td>`).join('')}</tr>`).join('')}</tbody></table><script>window.onload=function(){window.print()}<\/script></body></html>`);
        popup.document.close();
    }

    async function runExport(button, api, type) {
        const original = button.html();
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Exporting');
        try {
            const rawRows = await fetchRows(api);
            const rows = displayRows(api, rawRows);
            const name = exportName($(api.table().node()));
            if (type === 'json') {
                download(JSON.stringify(rows, null, 2), 'application/json;charset=utf-8', `${name}.json`);
            } else if (type === 'csv') {
                const headers = rows[0] ? Object.keys(rows[0]) : [];
                const csv = [headers.map(csvValue).join(','), ...rows.map(row => headers.map(h => csvValue(row[h])).join(','))].join('\r\n');
                download('\ufeff' + csv, 'text/csv;charset=utf-8', `${name}.csv`);
            } else {
                printRows(name.replace(/-/g, ' '), rows);
            }
        } catch (error) {
            window.alert(error.message || 'Export failed.');
        } finally {
            button.prop('disabled', false).html(original);
        }
    }

    function enhanceTable(tableNode) {
        const $table = $(tableNode);
        if (!$table.hasClass('dataTable') || $table.data('universal-export')) return;
        $table.data('universal-export', true);
        const api = $table.DataTable();
        const toolbar = $('<div class="universal-table-export btn-group btn-group-sm mb-2" role="group" aria-label="Table export"></div>');
        [['json', 'JSON', 'fa-file-code'], ['csv', 'CSV / Excel', 'fa-file-csv'], ['print', 'Print / PDF', 'fa-print']].forEach(item => {
            const button = $(`<button type="button" class="btn btn-outline-primary"><i class="fas ${item[2]} mr-1"></i>${item[1]}</button>`);
            button.on('click', () => runExport(button, api, item[0]));
            toolbar.append(button);
        });
        const wrapper = $table.closest('.dataTables_wrapper');
        const filter = wrapper.find('.dataTables_filter').first();
        if (filter.length) {
            const filterColumn = filter.parent();
            const lengthColumn = wrapper.find('.dataTables_length').parent();
            filterColumn.removeClass('col-md-6').addClass('col-md-9 d-flex justify-content-md-end align-items-center flex-wrap');
            lengthColumn.removeClass('col-md-6').addClass('col-md-3');
            toolbar.addClass('mr-2').prependTo(filterColumn);
            filter.addClass('mb-2');
        } else {
            wrapper.prepend(toolbar);
        }
    }

    function enhanceAll() {
        $('table.dataTable').each(function () { enhanceTable(this); });
    }

    $(document).on('init.dt', function (event, settings) { setTimeout(() => enhanceTable(settings.nTable), 0); });
    $(function () { setTimeout(enhanceAll, 0); });
})(window.jQuery);
