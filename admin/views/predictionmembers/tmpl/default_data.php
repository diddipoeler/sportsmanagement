<?php
/** Prediction members list rows for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$stateIcon = static function (bool $enabled, string $yesText, string $noText): string {
    $icon = $enabled ? 'icon-check text-success' : 'icon-times text-danger';
    $title = Text::_($enabled ? $yesText : $noText);

    return '<span class="' . $icon . '" title="' . htmlspecialchars($title) . '" aria-label="'
        . htmlspecialchars($title) . '"></span>';
};
?>
<div class="table-responsive" id="editcell_predictiongames">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th scope="col" class="w-1 text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th scope="col" class="w-1 text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_USERNAME'), 'u.username', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REAL_NAME'), 'u.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PRED_NAME'), 'p.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_DATE_LAST_TIP'), 'tmb.last_tipp', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col" class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'), 'tmb.reminder', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col" class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_RECEIPT'), 'tmb.receipt', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col" class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PROFILE'), 'tmb.show_profile', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col" class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ADMIN_TIP'), 'tmb.admintipp', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col" class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED'), 'tmb.approved', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
            <th scope="col"><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ((array) $this->items as $i => $item) :
            $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
            $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                || (int) $item->checked_out === (int) $this->user->id
                || (int) $item->checked_out === 0;
            $editUrl = Route::_('index.php?option=com_sportsmanagement&task=predictionmember.edit&id=' . (int) $item->id);
        ?>
            <tr>
                <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id, !$canCheckin); ?></td>
                <td>
                    <?php if ((int) $item->checked_out > 0 && !$canCheckin) : ?>
                        <span class="icon-lock" aria-hidden="true"></span>
                    <?php endif; ?>
                    <?php if ($canEdit && $canCheckin) : ?>
                        <a href="<?php echo $editUrl; ?>" title="<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_EDIT_USER'); ?>">
                            <?php echo htmlspecialchars((string) $item->username); ?>
                        </a>
                    <?php else : ?>
                        <?php echo htmlspecialchars((string) $item->username); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars((string) $item->realname); ?></td>
                <td><?php echo htmlspecialchars((string) $item->predictionname); ?></td>
                <td class="text-center">
                    <?php if (!empty($item->last_tipp)) :
                        [$date, $time] = array_pad(explode(' ', (string) $item->last_tipp, 2), 2, '');
                        echo sportsmanagementHelper::convertDate($date);
                        echo $time !== '' ? ' / ' . date('H:i', strtotime($time)) : '';
                    else :
                        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NEVER_TIPPED');
                    endif; ?>
                </td>
                <td class="text-center"><?php echo $stateIcon((bool) $item->reminder, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                <td class="text-center"><?php echo $stateIcon((bool) $item->receipt, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                <td class="text-center"><?php echo $stateIcon((bool) $item->show_profile, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ALLOWED', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_ALLOWED'); ?></td>
                <td class="text-center"><?php echo $stateIcon((bool) $item->admintipp, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                <td class="text-center"><?php echo $stateIcon((bool) $item->approved, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_APPROVED'); ?></td>
                <td><?php echo htmlspecialchars((string) $item->modified); ?></td>
                <td><?php echo htmlspecialchars((string) $item->modusername); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="13">
                <div class="d-flex justify-content-between align-items-center">
                    <span><?php echo $this->pagination->getResultsCounter(); ?></span>
                    <?php echo $this->pagination->getListFooter(); ?>
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
