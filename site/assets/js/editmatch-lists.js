(function () {
    'use strict';

    function getSelect(id) {
        const element = document.getElementById(id);
        return element instanceof HTMLSelectElement ? element : null;
    }

    function markChanged() {
        const field = document.getElementById('changes_check');

        if (field) {
            field.value = '1';
        }
    }

    function moveListItems(sourceId, destinationId) {
        const source = getSelect(sourceId);
        const destination = getSelect(destinationId);

        if (!source || !destination) {
            return false;
        }

        const selected = Array.from(source.options).filter((option) => option.selected);

        selected.forEach((option) => destination.appendChild(option));

        if (selected.length > 0) {
            markChanged();
        }

        return false;
    }

    function moveUp(selectId) {
        const select = getSelect(selectId);

        if (!select) {
            return false;
        }

        let changed = false;

        for (let index = 1; index < select.options.length; index += 1) {
            const option = select.options[index];
            const previous = select.options[index - 1];

            if (option.selected && !previous.selected) {
                select.insertBefore(option, previous);
                changed = true;
            }
        }

        if (changed) {
            markChanged();
        }

        return false;
    }

    function moveDown(selectId) {
        const select = getSelect(selectId);

        if (!select) {
            return false;
        }

        let changed = false;

        for (let index = select.options.length - 2; index >= 0; index -= 1) {
            const option = select.options[index];
            const next = select.options[index + 1];

            if (option.selected && !next.selected) {
                select.insertBefore(next, option);
                changed = true;
            }
        }

        if (changed) {
            markChanged();
        }

        return false;
    }

    function submitListForm(button) {
        const form = button.form;
        const task = button.dataset.submitTask || '';
        const selectAll = button.dataset.selectAllBeforeSubmit || '';

        if (!form || !task || !window.Joomla || typeof window.Joomla.submitform !== 'function') {
            return false;
        }

        if (selectAll) {
            form.querySelectorAll(selectAll).forEach((option) => {
                option.selected = true;
            });
        }

        window.Joomla.submitform(task, form);
        return false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-list-source][data-list-destination]').forEach((button) => {
            button.addEventListener('click', () => {
                moveListItems(button.dataset.listSource, button.dataset.listDestination);
            });
        });

        document.querySelectorAll('[data-list-move-up]').forEach((button) => {
            button.addEventListener('click', () => moveUp(button.dataset.listMoveUp));
        });

        document.querySelectorAll('[data-list-move-down]').forEach((button) => {
            button.addEventListener('click', () => moveDown(button.dataset.listMoveDown));
        });

        document.querySelectorAll('[data-submit-task]').forEach((button) => {
            button.addEventListener('click', () => submitListForm(button));
        });
    });

    window.move_list_items = moveListItems;
    window.move_up = moveUp;
    window.move_down = moveDown;
}());
