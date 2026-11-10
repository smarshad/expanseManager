let table;

$(function () {
    const url = $('#listRoute').val();
    console.log(url);
    initList(url);

    function initList(url) {
        $.fn.dataTable.ext.errMode = 'none';

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
                beforeSend: function () {
                    showTableLoader(); // ✅ show loader
                },
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

                    hideTableLoader(); // ✅ hide on success

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
                { data: 'name' },
                { data: "route" },
                { data: "url" },
                { data: "icon" },
                { data: "parent_name" },
                { data: "children.length" },
                { data: "order" },
                { data: "is_active" },
                { data: "created_at" },
                { data: "creator.name" },
                {
                    render: function (data, type, row) {
                        return renderActionButtons(row)
                    }
                }
            ],

            language: {
                processing: `<div class="spinner-border text-primary" role="status"></div> Loading...`
            }
        });

        // Add loader inside table rows
        // table.on('processing.dt', function (e, settings, processing) {
        //     if (processing) {
        //         showTableLoader();
        //     }
        // });
    }

    function renderActionButtons(rowData) {
        let buttonsHtml = '';
        // --- PERMISSION-BASED RENDERING ---

        // 1. Check if the user can UPDATE this category
        if (rowData.can && rowData.can.update) {
            const editUrl = window.routes.edit.replace(':id', rowData.id);
            const titleText = `${window.lang.edit} ${window.lang.title}`;
            buttonsHtml += `
           <a
                class="btn btn-sm btn-primary"
                href='${editUrl}'>
                ${window.lang.edit}
            </a>`;
        }
        // 2. Check if the user can DELETE this category
        if (rowData.can && rowData.can.delete) {
            const deleteUrl = window.routes.delete.replace(':id', rowData.id);

            buttonsHtml += `
            <button class="btn btn-sm btn-danger btn-delete" data-action="${deleteUrl}" data-id="${rowData.id}">
                Delete
            </button>`;
        }
        // Return the generated HTML (will be empty if user has no permissions)
        return buttonsHtml.trim();
    }
});
