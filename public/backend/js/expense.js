jQuery(function ($) {
    const options = window.expensePaymentOptions || { accounts: [], platforms: [] };
    const $method = $('#payment_method_id');
    const $platform = $('#payment_platform_id');
    const $account = $('#account_id');
    const $frequency = $('#frequency');
    const $isRecurring = $('#is_recurring');
    let selectedAccount = Number(options.selectedAccount || 0);
    let selectedPlatform = Number(options.selectedPlatform || 0);

    $('[data-toggle="select2"]').select2();

    function setOptions($select, rows, placeholder, selected) {
        $select.empty().append(new Option(placeholder, ''));
        rows.forEach(row => $select.append(new Option(row.name, row.id, false, Number(row.id) === Number(selected))));
        $select.trigger('change.select2');
    }

    function refreshForMethod() {
        const methodId = Number($method.val() || 0);
        const platforms = methodId ? options.platforms.filter(item => Number(item.method_id) === methodId) : [];
        const accounts = methodId ? options.accounts.filter(item => item.method_ids.map(Number).includes(methodId)) : options.accounts;
        const platformStillValid = platforms.some(item => Number(item.id) === selectedPlatform);
        const accountStillValid = accounts.some(item => Number(item.id) === selectedAccount);
        selectedPlatform = platformStillValid ? selectedPlatform : 0;
        selectedAccount = accountStillValid ? selectedAccount : 0;
        setOptions($platform, platforms, 'Select Platform', selectedPlatform);
        setOptions($account, accounts, 'Select Account', selectedAccount);
        $('#payment_platform_group, #payment_platform_label').toggle(platforms.length > 0);
        if (!platforms.length) $platform.val('').trigger('change.select2');
    }

    $method.on('change', function () {
        selectedPlatform = 0;
        selectedAccount = 0;
        refreshForMethod();
    });

    $platform.on('change', function () {
        selectedPlatform = Number(this.value || 0);
        const methodId = Number($method.val() || 0);
        let accounts = methodId ? options.accounts.filter(item => item.method_ids.map(Number).includes(methodId)) : options.accounts;
        if (selectedPlatform) accounts = accounts.filter(item => item.platform_ids.map(Number).includes(selectedPlatform));
        if (!accounts.some(item => Number(item.id) === selectedAccount)) selectedAccount = 0;
        setOptions($account, accounts, 'Select Account', selectedAccount);
    });

    $account.on('change', function () { selectedAccount = Number(this.value || 0); });

    $frequency.on('change', function () {
        const recurringFrequencies = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        $isRecurring.prop('checked', recurringFrequencies.includes(this.value));
    });
    refreshForMethod();
});
