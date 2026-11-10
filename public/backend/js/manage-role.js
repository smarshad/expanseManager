let table;

$(function () {
    const url = $('#listRoute').val();
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
                    d.search = d.search?.value ?? null;
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
                },
                error: function (xhr) {
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
                { data: "name" },
                {
                    render: function (data, type, row) {
                        // 'row' now contains the full object for the current row,
                        // including the 'can' permissions from the backend.
                        return `[${row.permissions_count}] - ${row.permissions}` ;
                    }
                },
                { data: "description" },
                { data: "guard_name" },
                { data: "created_at" },
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
    }

    function renderActionButtons(rowData) {
        // Start with an empty string for the buttons
        let buttonsHtml = '';

        // --- PERMISSION-BASED RENDERING ---

        // 1. Check if the user can UPDATE this permission
        if (rowData.can && rowData.can.update) {
            const editUrl = window.routes.edit.replace(':id', rowData.id);
            const titleText = `${window.lang.edit} ${window.lang.title}`;

            buttonsHtml += `
            <a
                class="btn btn-sm btn-primary"
                href='${editUrl}'>
                ${window.lang.edit}
            </a>
        `;
        }

        // 2. Check if the user can DELETE this category
        if (rowData.can && rowData.can.delete) {
            const deleteUrl = window.routes.delete.replace(':id', rowData.id);

            buttonsHtml += `
            <button class="btn btn-sm btn-danger btn-delete" data-action="${deleteUrl}" data-id="${rowData.id}">
            ${window.lang.delete}
            </button>
        `;
        }

        // Return the generated HTML (will be empty if user has no permissions)
        return buttonsHtml.trim();
    }
});
