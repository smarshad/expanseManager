let vendorTable;

$(function () {
    vendorTable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: Number($('meta[name="perPage"]').attr('content')) || 10,
        ajax: {
            url: $('#listRoute').val(),
            data: function (data) {
                data.page = (data.start / data.length) + 1;
                data.per_page = data.length;
                data.search = data.search?.value || '';
                delete data.start;
                delete data.length;
            },
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name' },
            { data: 'category', defaultContent: '-' },
            { data: 'is_active', render: value => value ? 'Yes' : 'No' },
            { data: 'created_at', defaultContent: '-' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    let buttons = '';
                    if (row.can?.update) {
                        const editUrl = window.routes.vendorEdit.replace(':id', row.id);
                        buttons += `<button class="btn btn-sm btn-primary openModel" data-url="${editUrl}" data-size="lg" data-title="Update Vendor" data-footer='<button type="submit" class="btn btn-primary js-submit-btn">Update</button> <button type="button" class="btn btn-info" data-dismiss="modal">Close</button>'>Edit</button> `;
                    }
                    if (row.can?.delete) {
                        const deleteUrl = window.routes.vendorDelete.replace(':id', row.id);
                        buttons += `<button class="btn btn-sm btn-danger btn-delete" data-action="${deleteUrl}">Delete</button>`;
                    }
                    return buttons || '-';
                }
            }
        ]
    });
});
