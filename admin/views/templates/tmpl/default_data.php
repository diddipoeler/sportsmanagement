<?php
/** SportsManagement project template rows. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$templatesToLoad = ['footer', 'listheader'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$user = $this->app->getIdentity();
?>
<legend>
    <?php echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_LEGEND',
        '<i>' . $this->escape((string) ($this->projectws->name ?? '')) . '</i>'
    ); ?>
</legend>
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->escape($this->view); ?>list">
    <thead>
    <?php if (!empty($this->projectws->master_template)) : ?>
        <tr>
            <td class="text-end" colspan="9">
                <?php echo $this->lists['mastertemplates'] ?? ''; ?>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
        <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
        <th width="20">&nbsp;</th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE', 'tmpl.template', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_DESCR', 'tmpl.title', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TYPE'); ?></th>
        <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'tmpl.id', $this->sortDirection, $this->sortColumn); ?></th>
        <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
        <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->templates as $i => $item) :
        $link = Route::_(
            'index.php?option=com_sportsmanagement&task=template.edit&id=' . (int) $item->id
            . '&pid=' . (int) $this->project_id
        );
        $canCheckin = $user->authorise('core.manage', 'com_checkin')
            || (int) $item->checked_out === (int) $user->id
            || (int) $item->checked_out === 0;
        $isCheckedOut = (int) $item->checked_out !== 0 && (int) $item->checked_out !== (int) $user->id;
        $isMaster = !empty($item->isMaster);
        ?>
        <tr class="row<?php echo $i % 2; ?>">
            <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
            <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $item->id); ?></td>
            <td>
                <?php if ($isCheckedOut) : ?>
                    <?php echo HTMLHelper::_(
                        'jgrid.checkedout',
                        $i,
                        (int) $user->id,
                        $item->checked_out_time,
                        'templates.',
                        $canCheckin
                    ); ?>
                <?php else :
                    $image = HTMLHelper::_(
                        'image',
                        'administrator/components/com_sportsmanagement/assets/images/edit.png',
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS'),
                        ['title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS')]
                    );
                    echo HTMLHelper::link($link, $image);
                endif; ?>
            </td>
            <td><?php echo $this->escape((string) $item->template); ?></td>
            <td><?php echo Text::_((string) $item->title); ?></td>
            <td>
                <span class="<?php echo $isMaster ? 'text-danger' : 'text-success'; ?> fw-bold">
                    <?php echo Text::_($isMaster
                        ? 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_MASTER'
                        : 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_INDEPENDENT'); ?>
                </span>
            </td>
            <td class="text-center">
                <?php echo (int) $item->id; ?>
                <input type="hidden" name="isMaster[<?php echo (int) $item->id; ?>]" value="<?php echo $isMaster ? 1 : 0; ?>">
            </td>
            <td><?php echo $this->escape((string) ($item->modified ?? '')); ?></td>
            <td><?php echo $this->escape((string) ($item->username ?? '')); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="9">
            <?php echo $this->pagination->getListFooter(); ?>
            <?php echo $this->pagination->getResultsCounter(); ?>
        </td>
    </tr>
    </tfoot>
</table>
