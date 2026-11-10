let table;

$(function () {
    const url = $('#listRoute').val();
    console.log(url);
    initList(url);

    function initList(url) {


        if ($.fn.DataTable.isDataTable('#datatable')) {
            table.ajax.url(url).load();
            return;
        }

        table = $('#datatable').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            searching: true,
            paging: true,
            ordering: true,
            pageLength: Number($('meta[name="perPage"]').attr('content')) || 10,
            ajax: {
                url: url,
                type: "GET",

                // FIX: The 'data' function MUST be inside the 'ajax' object
                data: function (d) {
                    // Calculate the page number: (offset / limit) + 1
                    var page = (d.start / d.length) + 1;

                    // Map DataTables parameters to Laravel's expected parameters
                    d.page = page;
                    d.per_page = d.length;
                    // FIX: extract search string properly
                    d.search = d.search?.value ?? null;
                    ['date_from', 'date_to', 'category_id', 'expense_for_id',
                        'payment_method_id', 'payment_platform_id', 'account_id',
                        'vendor_id', 'transaction_kind', 'nature', 'frequency'
                    ].forEach(name => { d[name] = $('#filter_' + name).val() || ''; });
                    // Clean up DataTables' default parameters
                    delete d.start;
                    delete d.length;

                    // Note: DataTables will automatically handle the 'draw' parameter
                },

                // The dataSrc function is correctly placed here
                dataSrc: function (json) {
                    // Your backend is now sending the correct format, 
                    // so this function is correct for extracting the data array.
                    return json.data || [];
                },error: function (xhr) {
                    hideTableLoader();
                    let message = 'Something went wrong. Please try again.';

                    if (xhr.status === 401) {
                        message = 'You are not authenticated. Please log in again.';
                    }

                    if (xhr.status === 403) {
                        message = 'You are not authorized to view this data.';
                    }

                    if (xhr.status === 422) {
                        message = 'Validation error occurred.';
                    }

                    if (xhr.responseJSON?.message) {
                        console.log('here')

                        message = xhr.responseJSON.message;
                    }

                    showAlert(message);
                }
            },

            // The 'data' function has been removed from here

            columns: [
                { data: "id" },
                { data: 'amount', render: function (data) { return 'Rs. ' + parseFloat(data || 0).toFixed(2); } },
                { data: "category.name" },
                { data: "expenseFor.name" },
                { data: "paymentMethod.name" },
                { data: "paymentPlatform.name", defaultContent: "-" },
                { data: "account.name", defaultContent: "-" },
                { data: "vendor.name", defaultContent: "-" },
                { data: "cashback", render: function (data) { return 'Rs. ' + parseFloat(data || 0).toFixed(2); } },
                { data: "description" },
                { data: "notes" },
                {
                    data: "file_path",
                    render: data => filePath(data)

                },
                {
                    data: "expense_date",
                    render: function (data, type) {
                        if (type !== "display" || !data) return data;

                        const parts = data.split("/");
                        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                        const month = months[Number(parts[1]) - 1];

                        return parts.length === 3 && month
                            ? parts[0] + "/" + month + "/" + parts[2]
                            : data;
                    }
                },
                { data: "created_at" },
                // { data: "creator.name" },
                {
                    render: function (data, type, row) {
                        // 'row' now contains the full object for the current row,
                        // including the 'can' permissions from the backend.
                        return renderActionButtons(row);
                    }
                }
            ],

            language: {
                processing: `<div class="spinner-border text-primary" role="status"></div> Loading...`
            }
        });

        // Add loader inside table rows
        table.on('processing.dt', function (e, settings, processing) {
            if (processing) {
                showTableLoader();
            }
        });

        $('#applyExpenseFilters').on('click', function () {
            table.page('first').draw('page');
        });

        $('#resetExpenseFilters').on('click', function () {
            $('.expense-filter').val('').trigger('change');
            table.search('').page('first').draw('page');
        });

        $('#expenseFilters input').on('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                $('#applyExpenseFilters').trigger('click');
            }
        });
    }

    function renderActionButtons(rowData) {
        const editUrl = window.routes.edit.replace(':id', rowData.id);
        const deleteUrl = window.routes.delete.replace(':id', rowData.id);
        const titleText = `${window.lang.edit} ${window.lang.title}`;
        let buttonsHtml = '';
        if (rowData.can && rowData.can.show) {
            buttonsHtml += `<a
                class="btn btn-sm btn-primary"
                href='${editUrl}'>
                ${titleText}
            </a>`;
        }

        if (rowData.can && rowData.can.delete) {
            buttonsHtml += ` <button class="btn btn-sm btn-danger btn-delete" data-action="${deleteUrl}" data-id="${rowData.id}">
                Delete
            </button>`;
        }
        return buttonsHtml.trim();
    }

    function filePath(data) {
        const fullUrl = `${window.location.origin}/storage/${data}`;
        if (!data) return "-"; // no file
            return `<a href="${fullUrl}" target="_blank" class="btn btn-secondary">View File</a>`;
    }
});
