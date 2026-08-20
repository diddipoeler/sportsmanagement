(() => {
    'use strict';

    const byId = (id) => document.getElementById(id);

    const submitMatrixMatch = (trigger) => {
        const form = document.getElementById('matrixForm');

        if (!form) {
            return;
        }

        const home = form.elements.teamplayer1_id;
        const away = form.elements.teamplayer2_id;

        if (!home || !away) {
            return;
        }

        home.value = trigger.dataset.homePlayer || '';
        away.value = trigger.dataset.awayPlayer || '';
        form.submit();
    };

    const prepareMassAdd = () => {
        const count = byId('addmatchescount');
        const temporaryCount = byId('tempaddmatchescount');
        const addType = byId('addtype');

        if (count && temporaryCount) {
            count.value = temporaryCount.value;
        }

        if (addType) {
            addType.value = '1';
        }
    };

    const prepareCopyMatches = () => {
        const addType = byId('addtype');

        if (addType) {
            addType.value = '2';
        }
    };

    const handleResultRowChange = (event) => {
        const control = event.target;

        if (!(control instanceof Element)) {
            return;
        }

        const row = control.closest('[data-jsm-result-row]');

        if (!row) {
            return;
        }

        const checkbox = row.querySelector('input[id^="cb"]');

        if (checkbox && control.id !== checkbox.id) {
            checkbox.checked = true;
        }

        if (control.matches('select[id^="team"]')) {
            const matchId = control.id.substring(10);
            const link = document.getElementsByClassName('openroster-' + control.id)[0];

            if (link) {
                link.href = 'index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=editlineup&cid[]='
                    + encodeURIComponent(matchId)
                    + '&team=' + encodeURIComponent(control.value);
            }
        }
    };

    const displayTypeView = () => {
        const type = byId('ct');
        const standard = byId('massadd_standard');
        const type2 = byId('massadd_type2');

        if (!type || !standard || !type2) {
            return;
        }

        standard.style.display = type.value === '1' ? 'block' : 'none';
        type2.style.display = type.value === '2' ? 'block' : 'none';
    };

    document.addEventListener('change', handleResultRowChange);

    document.addEventListener('DOMContentLoaded', () => {
        const closeEditor = document.querySelector('[data-jsm-close-editor="1"]');
        if (closeEditor) {
            window.closeIndividualSportEditor();
        }

        const createType = byId('ct');
        if (createType) {
            createType.addEventListener('change', displayTypeView);
            displayTypeView();
        }
    });

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-jsm-action]');

        if (!trigger) {
            return;
        }

        switch (trigger.dataset.jsmAction) {
            case 'submit-task':
                event.preventDefault();
                Joomla.submitform(trigger.dataset.task || '', trigger.form || byId('adminForm'));
                break;
            case 'save-close':
                event.preventDefault();
                window.saveAndCloseIndividualSport(trigger.form || byId('adminForm'));
                break;
            case 'save-match':
                event.preventDefault();
                submitMatrixMatch(trigger);
                break;
            case 'add-matches':
                prepareMassAdd();
                break;
            case 'copy-matches':
                prepareCopyMatches();
                break;
        }
    });

    window.switchMenu = (id) => {
        const element = byId(id);

        if (element) {
            element.style.display = element.style.display === 'none' ? 'block' : 'none';
        }
    };

    window.displayTypeView = displayTypeView;

    window.addmatches = () => {
        prepareMassAdd();
        return true;
    };

    window.copymatches = () => {
        prepareCopyMatches();
        return true;
    };

    window.SaveMatch = (homePlayerId, awayPlayerId) => {
        const form = document.getElementById('matrixForm');

        if (!form) {
            return;
        }

        submitMatrixMatch({dataset: {
            homePlayer: String(homePlayerId),
            awayPlayer: String(awayPlayerId),
        }});
    };

    window.closeIndividualSportEditor = () => {
        const cancel = byId('cancel');

        if (cancel) {
            cancel.click();
        }
    };

    window.saveAndCloseIndividualSport = (form) => {
        const close = byId('close');

        if (close) {
            close.value = '1';
        }

        Joomla.submitform('jlextindividualsportes.saveshort', form);
    };
})();
