<?php
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$user = $this->getCurrentUser();
$listOrder = (string) $this->state->get('list.ordering', 'tmpl.template');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>
<div class="mb-3">
    <strong><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_LEGEND', $this->escape((string) $this->projectws->name)); ?></strong>
    <?php if ($this->master) : ?>
        <span class="text-muted ms-2"><?php echo $this->escape((string) $this->master); ?></span>
    <?php endif; ?>
</div>
<div class="table-responsive">
    <table class="table table-striped" id="templatesList">
        <thead>
            <tr>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE', 'tmpl.template', $listDirn, $listOrder); ?></th>
                <th><?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_DESCR', 'tmpl.title', $listDirn, $listOrder); ?></th>
                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TYPE'); ?></th>
                <th class="w-10"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
                <th class="w-10"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
                <th class="w-5 text-center"><?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'tmpl.id', $listDirn, $listOrder); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($this->templates as $i => $item) :
            $id = (int) $item->id;
            $isMaster = !empty($item->isMaster);
            $checkedOut = (int) ($item->checked_out ?? 0);
            $canCheckin = $user->authorise('core.manage', 'com_checkin') || $checkedOut === 0 || $checkedOut === (int) $user->id;
            $canEdit = $user->authorise('core.edit', 'com_sportsmanagement') && $canCheckin;
        ?>
            <tr>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, $id); ?></td>
                <td>
                    <?php if ($checkedOut !== 0) : ?>
                        <?php echo HTMLHelper::_('jgrid.checkedout', $i, (string) ($item->editor ?? ''), (string) ($item->checked_out_time ?? ''), 'templates.', $canCheckin); ?>
                    <?php endif; ?>
                    <?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=template.edit&id=' . $id . '&pid=' . (int) $this->project_id); ?>">
                            <?php echo $this->escape((string) $item->template); ?>
                        </a>
                    <?php else : ?>
                        <?php echo $this->escape((string) $item->template); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo Text::_((string) $item->title); ?></td>
                <td>
                    <span class="<?php echo $isMaster ? 'text-danger' : 'text-success'; ?> fw-bold">
                        <?php echo Text::_($isMaster ? 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_MASTER' : 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_INDEPENDENT'); ?>
                    </span>
                </td>
                <td><?php echo $this->escape((string) ($item->modified ?? '')); ?></td>
                <td><?php echo $this->escape((string) ($item->username ?? '')); ?></td>
                <td class="text-center">
                    <?php echo $id; ?>
                    <input type="hidden" name="isMaster[<?php echo $id; ?>]" value="<?php echo $isMaster ? 1 : 0; ?>">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo $this->pagination->getListFooter(); ?>
