<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treetomatchs&layout=editlist&nid=' . $this->node_id . '&tid=' . $this->tree_id . '&pid=' . $this->project_id); ?>" method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="nid" value="<?php echo $this->node_id; ?>">
    <input type="hidden" name="tid" value="<?php echo $this->tree_id; ?>">
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('adminForm');
    if (!form) return;

    const move = (sourceId, targetId) => {
        const source = document.getElementById(sourceId);
        const target = document.getElementById(targetId);
        if (!source || !target) return;
        Array.from(source.selectedOptions).forEach((option) => target.append(option));
    };

    form.querySelectorAll('[data-jsm-move]').forEach((button) => {
        button.addEventListener('click', () => move(button.dataset.source, button.dataset.target));
    });

    const selectAssigned = () => {
        const assigned = document.getElementById('node_matcheslist');
        if (assigned) Array.from(assigned.options).forEach((option) => { option.selected = true; });
    };

    form.addEventListener('submit', selectAssigned);

    if (window.Joomla && typeof window.Joomla.submitbutton === 'function') {
        const submitbutton = window.Joomla.submitbutton;
        window.Joomla.submitbutton = function (task) {
            selectAssigned();
            return submitbutton.call(this, task);
        };
    }
});
</script>
