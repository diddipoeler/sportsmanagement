<?php
/** SportsManagement JoomlaLeague position mapping. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<form action="<?php echo $this->escape($this->request_url); ?>" method="post" id="adminForm" name="adminForm">
    <table class="<?php echo $this->escape($this->table_data_class); ?>">
        <thead>
        <tr>
            <?php echo $this->lists['whichtable']; ?>
        </tr>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_JOOMLEAGUE_POSITIONS'); ?></th>
            <th style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_SPORTSMANAGEMENT_POSITIONS'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->joomleague as $i => $row) : ?>
            <?php $checked = HTMLHelper::_('grid.checkedout', $row, $i); ?>
            <tr class="row<?php echo $i % 2; ?>">
                <td class="center"><?php echo (int) $i; ?></td>
                <td class="center"><?php echo $checked; ?></td>
                <td><?php echo $this->escape($row->name ?? ''); ?></td>
                <td class="center">
                    <?php
                    $append = ' onchange="document.getElementById(\'cb' . (int) $i . '\').checked=true" ';
                    echo HTMLHelper::_(
                        'select.genericlist',
                        $this->lists['position'],
                        'position' . (int) ($row->id ?? 0),
                        'class="inputbox" size="1"' . $append,
                        'value',
                        'text',
                        0
                    );
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="search_mode" value="<?php echo $this->escape($this->lists['search_mode']); ?>" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>" />
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
<?php echo $this->loadTemplate('footer'); ?>
