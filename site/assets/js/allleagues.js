(function () {
    'use strict';

    function getFilterForm() {
        return document.querySelector('form[data-jsm-leagues-filter-form]');
    }

    window.tableOrdering = function (order, dir) {
        var form = document.adminForm || getFilterForm();

        if (!form) {
            return;
        }

        if (form.elements.filter_order) {
            form.elements.filter_order.value = order;
        }

        if (form.elements.filter_order_Dir) {
            form.elements.filter_order_Dir.value = dir;
        }

        form.submit();
    };

    document.addEventListener('DOMContentLoaded', function () {
        var form = getFilterForm();

        if (!form) {
            return;
        }

        form.querySelectorAll('[data-jsm-auto-submit]').forEach(function (element) {
            element.addEventListener('change', function () {
                form.submit();
            });
        });

        form.querySelectorAll('[data-character-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                var search = form.querySelector('#filter_search');

                if (search) {
                    search.value = button.getAttribute('data-character-filter') || '';
                }

                form.submit();
            });
        });

        var clearButton = form.querySelector('[data-clear-league-search]');

        if (clearButton) {
            clearButton.addEventListener('click', function () {
                var search = form.querySelector('#filter_search');

                if (search) {
                    search.value = '';
                }

                form.submit();
            });
        }
    });
}());
