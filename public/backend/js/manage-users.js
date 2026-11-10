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

                //  FIX: The 'data' function MUST be inside the 'ajax' object
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
                {
                    data: null,
                    render: function (data, type, row, meta) {
                        if (type === 'display') {
                            return meta.row + 1;
                        }
                        return '';
                    },
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                { data: "name" },
                { data: "role" },
                { data: "permissions_count" },
                { data: "locked" },
                { data: "last_login" },
                { data: "created_at" },
                { data: "created_by" },
                {
                    data: null,
                    render: function (data, type, row) {
                        // Pass the entire row object
                        return renderActionButtons(row);
                    },
                    orderable: false,
                    searchable: false,
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
        const editUrl = window.routes.edit.replace(':id', rowData.id);
        const permissionUrl = window.routes.permission.replace(':id', rowData.id);
        const deleteUrl = window.routes.delete.replace(':id', rowData.id);

        let buttonsHtml = '';

        if (rowData.can && rowData.can.update) {
            buttonsHtml += `
                <a class="btn btn-sm btn-info" 
                href='${editUrl}'>
                    ${window.lang.edit}
                </a>
            `;
        }
        // PERMISSIONS button - show only if user can view permissions
        if (rowData.can && rowData.can.viewpermission) {
            buttonsHtml += `
                <a class="btn btn-sm btn-primary" href='${permissionUrl}'>
                    ${window.lang.view_permissions || 'Permissions'}
                </a>
            `;
        }

        // DELETE button - always show, but with confirmation
        buttonsHtml += `
            <button class="btn btn-sm btn-danger btn-delete" data-action="${deleteUrl}" data-id="${rowData.id}">
                Delete
            </button>
        `;

        return buttonsHtml.trim();
    }
});
