<?php
/** Native Joomla 5/6 prediction templates list rows. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$listOrder = (string) $this->state->get('list.ordering', 'tmpl.title');
$listDirn = (string) $this->state->get('list.direction', 'ASC');
?>

<?php if ($this->prediction_id <= 0) : ?>
    <div class="alert alert-info">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_DESCR'); ?>
    </div>
    <?php return; ?>
<?php endif; ?>

<?php if ($this->predictiongame) : ?>
    <div class="alert alert-info py-2">
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE2',
            '<strong>' . $this->escape((string) $this->predictiongame->name) . '</strong>',
            ' ' . (int) $this->predictiongame->id . ' '
        ); ?>
    </div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle" id="predictiontemplatesList">
        <thead>
            <tr>
                <th class="w-1 text-center">#</th>
                <th class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                <th>
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE', 'tmpl.template', $listDirn, $listOrder); ?>
                </th>
                <th>
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE3', 'tmpl.title', $listDirn, $listOrder); ?>
                </th>
                <th class="text-center">
                    <?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'tmpl.id', $listDirn, $listOrder); ?>
                </th>
                <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
                <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr>
                <td colspan="7" class="text-center py-4">
                    <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                </td>
            </tr>
        <?php endif; ?>

        <?php foreach ($this->items as $i => $item) : ?>
            <?php
            $id = (int) $item->id;
            $checkedOut = (int) ($item->checked_out ?? 0);
            $checkedOutByOther = $checkedOut > 0 && $checkedOut !== (int) $this->user->id;
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement') && !$checkedOutByOther;
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || $checkedOut === (int) $this->user->id
                || $checkedOut === 0;
            $editLink = Route::_(
                'index.php?option=com_sportsmanagement&task=predictiontemplate.edit&id=' . $id
                . '&predid=' . (int) $this->prediction_id
            );
            ?>
            <tr>
                <td class="text-center">
                    <?php echo $this->pagination ? $this->pagination->getRowOffset($i) : $i + 1; ?>
                </td>
                <td class="text-center">
                    <?php echo HTMLHelper::_('grid.id', $i, $id, !$canCheckin); ?>
                </td>
                <td>
                    <?php if ($checkedOutByOther) : ?>
                        <span class="icon-lock me-1" aria-hidden="true"></span>
                    <?php endif; ?>

                    <?php if ($canEdit) : ?>
                        <a href="<?php echo $editLink; ?>" class="fw-semibold">
                            <?php echo $this->escape((string) $item->template); ?>
                        </a>
                    <?php else : ?>
                        <span class="fw-semibold"><?php echo $this->escape((string) $item->template); ?></span>
                    <?php endif; ?>
                </td>
                <td><?php echo $this->escape(Text::_((string) $item->title)); ?></td>
                <td class="text-center"><?php echo $id; ?></td>
                <td><?php echo $this->escape((string) ($item->modified ?? '')); ?></td>
                <td><?php echo $this->escape((string) ($item->username ?? '')); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($this->pagination) : ?>
    <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
<?php endif; ?>
