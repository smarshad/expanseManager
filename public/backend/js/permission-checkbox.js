$(document).ready(function() {

    // Select/Deselect all permissions in a module
    $('.module-checkbox').on('change', function() {
        const module = $(this).data('module');
        const checked = $(this).prop('checked');

        $(`.permission-checkbox[data-module="${module}"]`).prop('checked', checked);
        updateModuleCheckboxState(module);
    });

    // Update module checkbox when individual permission changes
    $('.permission-checkbox').on('change', function() {
        const module = $(this).data('module');
        updateModuleCheckboxState(module);
    });

    // Function to update module checkbox state
    function updateModuleCheckboxState(module) {
        const moduleCheckbox = $(`.module-checkbox[data-module="${module}"]`);
        const allPermissions = $(`.permission-checkbox[data-module="${module}"]`);
        const checkedPermissions = $(`.permission-checkbox[data-module="${module}"]:checked`);

        if (checkedPermissions.length === allPermissions.length && allPermissions.length > 0) {
            // All checked
            moduleCheckbox.prop('checked', true);
            moduleCheckbox.prop('indeterminate', false);
        } else if (checkedPermissions.length > 0) {
            // Some checked (indeterminate state)
            moduleCheckbox.prop('checked', false);
            moduleCheckbox.prop('indeterminate', true);
        } else {
            // None checked
            moduleCheckbox.prop('checked', false);
            moduleCheckbox.prop('indeterminate', false);
        }
    }

    // Initialize all module checkboxes on page load
    $('.module-checkbox').each(function() {
        const module = $(this).data('module');
        updateModuleCheckboxState(module);
    });

});