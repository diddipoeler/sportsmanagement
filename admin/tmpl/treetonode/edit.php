<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$id = (int) ($this->item->id ?? 0);
$renderFieldset = static function ($form, string $name): void {
    foreach ($form->getFieldset($name) as $field) {
        if (strtolower((string) $field->type) === 'hidden') {
            echo $field->input;
            continue;
        }
        ?>
        <div class="control-group mb-3">
            <div class="control-label"><?php echo $field->label; ?></div>
            <div class="controls"><?php echo $field->input; ?></div>
        </div>
        <?php
    }
};
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=treetonode&layout=edit&id=' . $id . '&tid=' . (int) $this->tree_id . '&pid=' . (int) $this->project_id); ?>"
      method="post" name="adminForm" id="treetonode-form" class="form-validate">
    <?php echo HTMLHelper::_('uitab.startTabSet', 'treetonodeTabs', ['active' => 'node-details', 'recall' => true]); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'treetonodeTabs', 'node-details', Text::_('COM_SPORTSMANAGEMENT_TABS_DETAILS')); ?>
    <div class="options-form mb-4"><?php $renderFieldset($this->form, 'details'); ?></div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php echo HTMLHelper::_('uitab.addTab', 'treetonodeTabs', 'node-description', Text::_('COM_SPORTSMANAGEMENT_TABS_description')); ?>
    <div class="options-form mb-4"><?php $renderFieldset($this->form, 'description'); ?></div>
    <?php echo HTMLHelper::_('uitab.endTab'); ?>

    <?php if ($this->match) : ?>
        <?php echo HTMLHelper::_('uitab.addTab', 'treetonodeTabs', 'node-matches', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE')); ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th><?php echo Text::_('JGRID_HEADING_ID'); ?></th><th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNUMBER'); ?></th><th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'); ?></th><th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'); ?></th><th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th></tr></thead>
                <tbody>
                <?php foreach ($this->match as $match) : ?>
                    <tr>
                        <td><?php echo (int) ($match->mid ?? 0); ?></td>
                        <td><?php echo $this->escape((string) ($match->match_number ?? '')); ?></td>
                        <td><?php echo $this->escape((string) ($match->projectteam1 ?? '')); ?></td>
                        <td><?php echo $this->escape((string) ($match->projectteam2 ?? '')); ?></td>
                        <td><?php echo $this->escape((string) ($match->projectteam1result ?? '')); ?> : <?php echo $this->escape((string) ($match->projectteam2result ?? '')); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
    <?php endif; ?>

    <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <input type="hidden" name="tid" value="<?php echo (int) $this->tree_id; ?>">
    <input type="hidden" name="pid" value="<?php echo (int) $this->project_id; ?>">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
