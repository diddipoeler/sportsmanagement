<?php
/** Native Joomla 5/6 prediction members list rows. */
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$stateIcon = static function (bool $enabled, string $yesText, string $noText): string {
    $title = Text::_($enabled ? $yesText : $noText);
    $class = $enabled ? 'icon-check text-success' : 'icon-times text-danger';

    return '<span class="' . $class . '" title="'
        . htmlspecialchars($title, ENT_QUOTES, 'UTF-8')
        . '" aria-label="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"></span>';
};
?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead>
        <tr>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_USERNAME'), 'u.username', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_REAL_NAME'), 'u.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PRED_NAME'), 'p.name', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_DATE_LAST_TIP'), 'tmb.last_tipp', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_SEND_REMINDER'), 'tmb.reminder', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_RECEIPT'), 'tmb.receipt', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_PROFILE'), 'tmb.show_profile', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ADMIN_TIP'), 'tmb.admintipp', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="text-center"><?php echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED'), 'tmb.approved', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?></th>
            <th><?php echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$this->items) : ?>
            <tr><td colspan="13" class="text-center py-4"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></td></tr>
        <?php else : ?>
            <?php foreach ($this->items as $i => $item) : ?>
                <?php
                $checkedOut = (int) ($item->checked_out ?? 0);
                $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
                $canCheckin = $this->user->authorise('core.manage', 'com_checkin')
                    || $checkedOut === (int) $this->user->id
                    || $checkedOut === 0;
                $editUrl = Route::_('index.php?option=com_sportsmanagement&task=predictionmember.edit&id=' . (int) $item->id);
                $lastTip = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NEVER_TIPPED');

                if (!empty($item->last_tipp)) {
                    try {
                        $lastTip = Factory::getDate((string) $item->last_tipp)->format('d.m.Y / H:i');
                    } catch (\Throwable) {
                        $lastTip = htmlspecialchars((string) $item->last_tipp, ENT_QUOTES, 'UTF-8');
                    }
                }
                ?>
                <tr>
                    <td class="text-center"><?php echo $this->pagination ? $this->pagination->getRowOffset($i) : $i + 1; ?></td>
                    <td class="text-center"><?php echo HTMLHelper::_('grid.id', $i, (int) $item->id, !$canCheckin); ?></td>
                    <td>
                        <?php if ($checkedOut > 0 && !$canCheckin) : ?><span class="icon-lock" aria-hidden="true"></span><?php endif; ?>
                        <?php if ($canEdit && $canCheckin) : ?>
                            <a href="<?php echo $editUrl; ?>"><?php echo htmlspecialchars((string) $item->username, ENT_QUOTES, 'UTF-8'); ?></a>
                        <?php else : ?>
                            <?php echo htmlspecialchars((string) $item->username, ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars((string) $item->realname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string) $item->predictionname, ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $lastTip; ?></td>
                    <td class="text-center"><?php echo $stateIcon((bool) $item->reminder, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                    <td class="text-center"><?php echo $stateIcon((bool) $item->receipt, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                    <td class="text-center"><?php echo $stateIcon((bool) $item->show_profile, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ALLOWED', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_ALLOWED'); ?></td>
                    <td class="text-center"><?php echo $stateIcon((bool) $item->admintipp, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_ACTIVE', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_INACTIVE'); ?></td>
                    <td class="text-center"><?php echo $stateIcon((bool) $item->approved, 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_APPROVED', 'COM_SPORTSMANAGEMENT_ADMIN_PMEMBERS_NOT_APPROVED'); ?></td>
                    <td><?php echo htmlspecialchars((string) ($item->modified ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string) ($item->modusername ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($this->pagination) : ?>
    <div class="mt-3"><?php echo $this->pagination->getListFooter(); ?></div>
<?php endif; ?>
