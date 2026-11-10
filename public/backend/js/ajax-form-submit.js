$(document).ready(function () {

    // Global AJAX setup for CSRF + JSON
    // ensure CSRF header for Laravel
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('submit', '.data-ajax-submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let action = form.attr('action');
        let method = form.attr('method') || 'POST';
        let formData = new FormData(form[0]);

        if (action == undefined || action == '') {
            alert('Action Missing');
            return false;
        }
        if (formData == undefined || formData == '') {
            alert('FormData Missing');
            return false;
        }
        handleAjaxFormSubmit(action, method, formData);
    });



    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const id = $btn.data('id');
        const action = $btn.data('action');

        console.log('Delete clicked for id:', id, 'action:', action);

        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.value) {
                console.log('User cancelled delete');
                return;
            }

            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            // Send AJAX - using method option (works with jQuery)
            $.ajax({
                url: action,
                type: 'POST',                 // use POST and send _method for best compatibility
                data: { _method: 'DELETE', id: id },  // Laravel friendly method spoofing
                dataType: 'json',
                success: function (res) {

                    // optional: remove table row or element (find closest row)
                    const $row = $btn.closest('tr');
                    if ($row.length) {
                        // if using DataTable, use the DataTable API to remove; otherwise remove DOM row
                        $row.fadeOut(300, function () { $(this).remove(); });
                    } else {
                        // if not inside table, remove parent card / item
                        $btn.closest('.item, .list-group-item, .row').remove();
                    }

                    Swal.fire({
                        type: 'success',
                        title: (res && res.message) ? res.message : 'Deleted!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                },
                error: function (xhr, status, err) {
                    // restore button UI
                    $btn.prop('disabled', false).html(originalHtml);

                    // Validation (422) or JSON message handling
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const firstErr = Object.values(xhr.responseJSON.errors)[0][0];
                        Swal.fire('Error', firstErr, 'error');
                        return;
                    }

                    const message = (xhr.responseJSON && xhr.responseJSON.message)
                        ? xhr.responseJSON.message
                        : 'An error occurred while deleting.';

                    Swal.fire('Error', message, 'error');
                },
                complete: function () {
                    // safety restore of button if not removed
                    if ($btn && $btn.length) $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });


});

function handleAjaxFormSubmit(action, method, formData) {
    $('.alert').hide();
    $.ajax({
        url: action,
        method: method,
        data: formData,
        contentType: false,
        processData: false,
        cache: false,
        success(response) {
            console.log('response:', response, 'formData:', formData);
            alert(response.message)
            if (response.redirect != undefined) {
                window.location.href = response.redirect;
            }

            if (response.callback != undefined) {
                // initList(response.callback);

                // If callback is a URL and you want to change the list URL:
                // table.ajax.url(response.callback).load();
                // If you just want to reload current data:
                if ($.fn.DataTable.isDataTable('#datatable')) {
                    $('#datatable').DataTable().ajax.reload(null, false);
                }

                $('#ajaxModal').modal('hide');
            }
        },
        error(xhr) {
            const data = xhr.responseJSON || {};

            // 1. Laravel validation errors (422)
            if (xhr.status === 422 && data.errors) {
                showValidationErrors(data.errors);
                return;
            }

            // 2. Backend error with `success = false`
            if (data.success === false) {
                const msg = data.message ?? 'An error occurred.';
                const err = data.error ?? '';
                alert(`${msg}${err ? `\n${err}` : ''}`);
                return;
            }

            // 3. QueryException or unexpected errors with only message field
            if (data.message) {
                alert(data.message);
                return;
            }

            // 4. Last fallback for unknown error structures
            alert('An unexpected error occurred. Please try again.');
        }

    });
}

function showValidationErrors(errors) {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    $('.alert-danger, .alert-success').html('').hide();
    $.each(errors, function (field, messages) {
        console.log('Field:', field, 'Messages:', messages);
        let input;
        // Handle different field types
        if (field === 'permissions') {
            $('.alert-danger').html(messages[0]).show();
        } else {
            // Regular fields
            input = $('[name="' + field + '"]');
            if (input.length > 0) {
                input.addClass('is-invalid');
                input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
            }
        }
    });
}

$(document).on('click', '.openModel', function (e) {
    e.preventDefault();

    var url = $(this).data('url');
    var title = $(this).data('title') || '';
    var size = $(this).data('size') || 'md'; // default = medium
    var footerHtml = $(this).data('footer') || '';

    var $modal = $('#ajaxModal');
    var $dialog = $('#ajaxModalDialog');
    var $modalBody = $modal.find('.js-modal-body');
    var $modalTitle = $modal.find('.js-modal-title');
    var $loader = $modal.find('.js-modal-loader');
    var $footer = $modal.find('.js-modal-footer');

    // Clear previous size classes
    $dialog.removeClass('modal-sm modal-lg modal-xl');

    // Add new size class
    if (size === 'sm') $dialog.addClass('modal-sm');
    if (size === 'lg') $dialog.addClass('modal-lg');
    if (size === 'xl') $dialog.addClass('modal-xl');

    // Set title + show loader
    $modalTitle.text(title);
    $loader.show();
    $modalBody.html('');

    // Open modal
    $modal.modal('show');
    // Load content
    $.get(url)
        .done(function (html) {
            $loader.hide();
            $modalBody.html(html);
            $footer.html(footerHtml);
        })
        .fail(function (xhr) {
            $loader.hide();

            // Authorization error
            if (xhr.status === 403) {
                let message = 'You are not authorized to perform this action.';

                // Try to read JSON message from policy
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                $modalBody.html(`
                    <div class="alert alert-danger text-center">
                        ${message}
                    </div>
                `);

                // Optional: auto-close modal
                setTimeout(() => {
                    $('#ajaxModal').modal('hide');
                }, 5000);

                return;
            }

            // Generic error
            $modalBody.html(
                '<div class="alert alert-danger">Failed to load content.</div>'
            );
        });

});

$(document).on('click', '.js-submit-btn', function (e) {
    e.preventDefault();

    // find form inside the modal and trigger submit
    let $modal = $('#ajaxModal'); // or your modal id
    let $form = $modal.find('form.data-ajax-submit').first();

    if ($form.length === 0) {
        alert('Form not found in modal');
        return;
    }

    $form.trigger('submit');  // this will hit your existing .data-ajax-submit handler
});

function showAlert(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            ${message}
        </div>
    `;
    $('#datatable_wrapper').prepend(alertHtml);
    hideTableLoader()
    setTimeout(() => {
        $('.alert').modal('hide');
    }, 5000);

}

function hideTableLoader() {
    // DataTables will repopulate rows automatically, so nothing needed here
    $('#datatable .table-loading-row').remove();
}

function showTableLoader() {
    const colspan = $('#datatable thead th').length;

    $('#datatable tbody').html(`
        <tr class="table-loading-row">
            <td colspan="${colspan}">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="ms-2">Loading...</span>
            </td>
        </tr>
    `);
}