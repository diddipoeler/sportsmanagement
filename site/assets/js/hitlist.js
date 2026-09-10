(() => {
    'use strict';

    const getForm = () => document.querySelector('[data-jsm-hitlist-form]') || document.adminForm;

    const submitForm = (task = '') => {
        const form = getForm();

        if (!form) {
            return;
        }

        if (window.Joomla && typeof Joomla.submitform === 'function') {
            Joomla.submitform(task, form);
            return;
        }

        if (task && form.elements.task) {
            form.elements.task.value = task;
        }

        form.submit();
    };

    const tableOrdering = (order, direction, task) => {
        const form = getForm();

        if (!form) {
            return;
        }

        if (form.elements.filter_order) {
            form.elements.filter_order.value = order;
        }

        if (form.elements.filter_order_Dir) {
            form.elements.filter_order_Dir.value = direction;
        }

        submitForm(task || '');
    };

    const searchPerson = (value) => {
        const form = getForm();

        if (!form) {
            return;
        }

        const search = form.querySelector('#filter_search');

        if (search) {
            search.value = value;
        }

        submitForm('');
    };

    window.tableOrdering = tableOrdering;
    window.searchPerson = searchPerson;

    if (window.Joomla && typeof Joomla.tableOrdering !== 'function') {
        Joomla.tableOrdering = tableOrdering;
    }
})();
