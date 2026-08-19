<?php
/** Tournament-tree assigned match rows for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<div class="table-responsive">
    <legend>
        <?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2',
            '<i>' . htmlspecialchars((string) $this->nodews->node) . '</i>',
            '<i>' . htmlspecialchars((string) $this->projectws->name) . '</i>'
        ); ?>
    </legend>

    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR'); ?></th>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('JSTATUS'); ?></th>
            <th scope="col" class="text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ((array) $this->match as $i => $row) : ?>
            <tr>
                <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="text-center"><?php echo htmlspecialchars((string) $row->match_number); ?></td>
                <td class="text-center"><?php echo htmlspecialchars((string) $row->roundcode); ?></td>
                <td><?php echo htmlspecialchars((string) $row->projectteam1); ?></td>
                <td class="text-center">
                    <?php echo htmlspecialchars((string) $row->projectteam1result); ?> :
                    <?php echo htmlspecialchars((string) $row->projectteam2result); ?>
                </td>
                <td><?php echo htmlspecialchars((string) $row->projectteam2); ?></td>
                <td class="text-center">
                    <span class="<?php echo (int) $row->published === 1 ? 'icon-check text-success' : 'icon-times text-danger'; ?>"
                          aria-label="<?php echo Text::_((int) $row->published === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?>"
                          title="<?php echo Text::_((int) $row->published === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?>"></span>
                </td>
                <td class="text-center"><?php echo (int) $row->mid; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
        </tr>
        </tfoot>
    </table>
</div>
