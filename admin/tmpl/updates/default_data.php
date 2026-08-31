<?php
/** Native Joomla 5/6 administrator update data layout. */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$token = Session::getFormToken();
$tabsOptions = ['active' => 'tab1_id1'];

echo HTMLHelper::_('bootstrap.startTabSet', 'ID-Tabs-Group', $tabsOptions);
echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-Group', 'tab1_id1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_LIST'));
?>
<div class="table-responsive">
    <table class="table align-middle">
        <thead>
        <tr>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FILE', 'name', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DESCR'); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSION', 'version', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DATE', 'date', $this->sortDirection, $this->sortColumn); ?></th>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_EXECUTED'); ?></th>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_COUNT'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->updateFiles as $index => $row) :
            $fileName = (string) ($row['file_name'] ?? '');
            $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save'
                . '&file_name=' . rawurlencode($fileName)
                . '&' . $token . '=1';
            $modalId = 'jsm-update-' . (int) $index;
            $modalTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_MAKE_UPDATE');
        ?>
            <tr>
                <td class="text-center"><?php echo $index + 1; ?></td>
                <td class="text-center text-nowrap">
                    <button type="button"
                            class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal"
                            data-bs-target="#<?php echo $this->escape($modalId); ?>"
                            title="<?php echo $this->escape($modalTitle); ?>">
                        <span class="icon-refresh" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo $this->escape($modalTitle); ?></span>
                    </button>
                    <?php
                    echo HTMLHelper::_('bootstrap.renderModal', $modalId, [
                        'title' => $modalTitle,
                        'url' => Uri::base() . $link,
                        'height' => '400px',
                        'width' => '800px',
                        'bodyHeight' => 70,
                        'modalWidth' => 80,
                    ]);
                    ?>
                    <?php echo $this->escape($fileName); ?>
                </td>
                <td>
                    <?php
                    $description = (string) ($row['updateDescription'] ?? '');
                    echo $description !== ''
                        ? $this->escape($description)
                        : Text::sprintf(
                            'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_UPDATE',
                            (string) ($row['last_version'] ?? ''),
                            (string) ($row['version'] ?? '')
                        );
                    ?>
                </td>
                <td class="text-center"><?php echo $this->escape((string) ($row['version'] ?? '')); ?></td>
                <td class="text-center">
                    <?php echo $this->escape(trim(
                        (string) ($row['updateFileDate'] ?? '') . ' ' . (string) ($row['updateFileTime'] ?? '')
                    )); ?>
                </td>
                <td class="text-center"><?php echo $this->escape((string) ($row['date'] ?? '')); ?></td>
                <td class="text-center"><?php echo (int) ($row['count'] ?? 0); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.addTab', 'ID-Tabs-Group', 'tab1_id2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_HISTORY'));

foreach ($this->versionhistory as $history) :
?>
    <fieldset class="mb-3">
        <legend class="h5">
            <?php echo Text::sprintf(
                'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSIONEN',
                $this->escape((string) $history->version),
                HTMLHelper::date($history->date, Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DAYDATE'))
            ); ?>
        </legend>
        <?php echo Text::_((string) $history->text); ?>
    </fieldset>
<?php endforeach;

echo HTMLHelper::_('bootstrap.endTab');
echo HTMLHelper::_('bootstrap.endTabSet');
