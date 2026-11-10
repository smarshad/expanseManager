const CRUDManager = {
    /**
     * Configuration
     */
    config: {
        csrfToken: $('meta[name="csrf-token"]').attr('content'),
        notificationTimeout: 5000
    },

    /**
     * Initialize DataTable with AJAX
     */
    initDataTable(selector, endpoint, columns, options = {}) {
        const defaultOptions = {
            processing: true,
            serverSide: true,
            ajax: {
                url: endpoint,
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken
                }
            },
            columns: columns.map(col => ({ data: col })),
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    render: (data, type, row) => this.renderActionButtons(row)
                }
            ],
            responsive: true,
            ...options
        };

        return $(selector).DataTable(defaultOptions);
    },

    /**
     * Render action buttons
     */
    renderActionButtons(row, showRestore = false) {
        let html = '<div class="btn-group btn-group-sm">';
        html += `<button class="btn btn-info" onclick="CRUDManager.editRecord(${row.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                 </button>`;
        html += `<button class="btn btn-danger" onclick="CRUDManager.deleteRecord(${row.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                 </button>`;
        if (showRestore) {
            html += `<button class="btn btn-warning" onclick="CRUDManager.restoreRecord(${row.id})" title="Restore">
                        <i class="fas fa-undo"></i>
                     </button>`;
        }
        html += '</div>';
        return html;
    },

    /**
     * Create new record
     */
    createRecord(endpoint, data) {
        return $.ajax({
            url: endpoint,
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            }
        });
    },

    /**
     * Read record
     */
    readRecord(endpoint) {
        return $.ajax({
            url: endpoint,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            }
        });
    },

    /**
     * Update record
     */
    updateRecord(endpoint, data) {
        return $.ajax({
            url: endpoint,
            type: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            }
        });
    },

    /**
     * Delete record
     */
    deleteRecord(id, endpoint, confirmMessage = 'Are you sure you want to delete this record?') {
        if (!confirm(confirmMessage)) return;

        $.ajax({
            url: `${endpoint}/${id}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', response.message);
                    $.fn.dataTable.tables().api().ajax.reload();
                }
            },
            error: () => {
                this.showNotification('error', 'Failed to delete record');
            }
        });
    },

    /**
     * Restore soft-deleted record
     */
    restoreRecord(id, endpoint) {
        $.ajax({
            url: `${endpoint}/${id}/restore`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', response.message);
                    $.fn.dataTable.tables().api().ajax.reload();
                }
            },
            error: () => {
                this.showNotification('error', 'Failed to restore record');
            }
        });
    },

    /**
     * Edit record - populate form
     */
    editRecord(id, endpoint) {
        $.get(`${endpoint}/${id}`, (response) => {
            if (response.success) {
                const data = response.data;
                Object.keys(data).forEach(key => {
                    const field = $(`[name="${key}"]`);
                    if (field.length) {
                        field.val(data[key]);
                    }
                });
                // Show modal
                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('recordModal'));
                modal.show();
            }
        });
    },

    /**
     * Submit form via AJAX
     */
    submitForm(formSelector, endpoint, method = 'POST', onSuccess = null) {
        const form = $(formSelector);
        const formData = new FormData(form[0]);

        $.ajax({
            url: endpoint,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', response.message);
                    form[0].reset();
                    
                    if (onSuccess) {
                        onSuccess(response);
                    } else {
                        // Close modal if exists
                        const modal = document.querySelector('.modal.show');
                        if (modal) {
                            bootstrap.Modal.getInstance(modal).hide();
                        }
                        // Reload table
                        $.fn.dataTable.tables().api().ajax.reload();
                    }
                }
            },
            error: (xhr) => {
                const errors = xhr.responseJSON?.errors || {};
                Object.keys(errors).forEach(field => {
                    $(`#error-${field}`).text(errors[field][0]);
                });
                this.showNotification('error', 'Failed to save record');
            }
        });
    },

    /**
     * Validate form
     */
    validateForm(formSelector, rules) {
        const form = $(formSelector);
        let isValid = true;

        Object.keys(rules).forEach(field => {
            const value = form.find(`[name="${field}"]`).val();
            const rule = rules[field];

            if (rule.required && !value) {
                $(`#error-${field}`).text('This field is required');
                isValid = false;
            }
        });

        return isValid;
    },

    /**
     * Show notification
     */
    showNotification(type, message, duration = null) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${icon}"></i> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        $('body').prepend(alertHtml);

        const timeout = duration || this.config.notificationTimeout;
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() { $(this).remove(); });
        }, timeout);
    },

    /**
     * Export data to CSV
     */
    exportToCSV(endpoint, filename = 'export.csv', filters = {}) {
        const params = new URLSearchParams(filters);
        window.location.href = `${endpoint}?${params}&format=csv`;
    },

    /**
     * Export data to PDF
     */
    exportToPDF(endpoint, filename = 'export.pdf', filters = {}) {
        const params = new URLSearchParams(filters);
        window.location.href = `${endpoint}?${params}&format=pdf`;
    },

    /**
     * Bulk delete records
     */
    bulkDelete(ids, endpoint, confirmMessage = 'Delete selected records?') {
        if (!confirm(confirmMessage)) return;

        $.ajax({
            url: `${endpoint}/bulk-delete`,
            type: 'POST',
            data: JSON.stringify({ ids }),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: (response) => {
                if (response.success) {
                    this.showNotification('success', response.message);
                    $.fn.dataTable.tables().api().ajax.reload();
                }
            },
            error: () => {
                this.showNotification('error', 'Failed to delete records');
            }
        });
    },

    /**
     * Get selected rows from DataTable
     */
    getSelectedRows(tableSelector) {
        const table = $(tableSelector).DataTable();
        return table.rows({ selected: true }).data().toArray();
    }
};