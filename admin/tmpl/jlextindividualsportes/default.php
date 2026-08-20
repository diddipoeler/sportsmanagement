<?php
defined('_JEXEC') or die('Restricted access');
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('tr.row-result').forEach((row) => {
        const checkbox = row.querySelector('input[id^="cb"]');

        if (!checkbox) {
            return;
        }

        row.querySelectorAll('select, input').forEach((control) => {
            control.addEventListener('change', () => {
                if (control.id !== checkbox.id) {
                    checkbox.checked = true;
                }
            });
        });

        row.querySelectorAll('select[id^="team"]').forEach((select) => {
            select.addEventListener('change', () => {
                const matchId = select.id.substring(10);
                const link = document.getElementsByClassName('openroster-' + select.id)[0];

                if (link) {
                    link.href = 'index.php?option=com_sportsmanagement&tmpl=component&controller=match&task=editlineup&cid[]='
                        + encodeURIComponent(matchId)
                        + '&team=' + encodeURIComponent(select.value);
                }
            });
        });
    });
});

function switchMenu(id) {
    const element = document.getElementById(id);

    if (element) {
        element.style.display = element.style.display === 'none' ? 'block' : 'none';
    }
}

function copymatches() {
    const addType = document.getElementById('addtype');

    if (addType && document.copyform) {
        addType.value = '2';
        document.copyform.submit();
    }

    return true;
}

function addmatches() {
    const count = document.getElementById('addmatchescount');
    const temporaryCount = document.getElementById('tempaddmatchescount');
    const addType = document.getElementById('addtype');

    if (count && temporaryCount) {
        count.value = temporaryCount.value;
    }
    if (addType) {
        addType.value = '1';
    }

    return true;
}

function displayTypeView() {
    const type = document.getElementById('ct');
    const standard = document.getElementById('massadd_standard');
    const type2 = document.getElementById('massadd_type2');

    if (!type || !standard || !type2) {
        return;
    }

    standard.style.display = type.value === '1' ? 'block' : 'none';
    type2.style.display = type.value === '2' ? 'block' : 'none';
}

function SaveMatch(homePlayerId, awayPlayerId) {
    const form = document.matrixForm;

    if (form) {
        form.elements.teamplayer1_id.value = homePlayerId;
        form.elements.teamplayer2_id.value = awayPlayerId;
        form.submit();
    }
}

function closeIndividualSportEditor() {
    const cancel = document.getElementById('cancel');

    if (cancel) {
        cancel.click();
    }
}

function saveAndCloseIndividualSport(form) {
    const close = document.getElementById('close');

    if (close) {
        close.value = '1';
    }

    Joomla.submitform('jlextindividualsportes.saveshort', form);
}
</script>
<style>
    .subsequentdecision {
        background-color: #BBB;
    }
</style>
<div id="alt_decision_enter" style="display:<?php echo ($this->massadd == 0) ? 'none' : 'block'; ?>">
</div>

<?php
switch ($this->projectws->sports_type_name)
{
case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
echo $this->loadTemplate('matches_small_bore_rifle');
break;
default:
echo $this->loadTemplate('matches');
echo $this->loadTemplate('matrix');
break;
}
?>
