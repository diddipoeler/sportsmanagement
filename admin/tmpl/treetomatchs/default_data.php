<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<div class="table-responsive">
    <legend><?php echo Text::sprintf(
        'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2',
        '<i>' . htmlspecialchars((string) $this->nodews->node, ENT_QUOTES, 'UTF-8') . '</i>',
        '<i>' . htmlspecialchars((string) $this->projectws->name, ENT_QUOTES, 'UTF-8') . '</i>'
    ); ?></legend>

    <table class="table table-striped align-middle">
        <thead>
        <tr>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM'); ?></th>
            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM'); ?></th>
            <th class="text-center"><?php echo Text::_('JSTATUS'); ?></th>
            <th class="text-center"><?php echo Text::_('JGRID_HEADING_ID'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($this->match as $i => $row) : ?>
            <tr>
                <td class="text-center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="text-center"><?php echo $this->escape((string) $row->match_number); ?></td>
                <td class="text-center"><?php echo $this->escape((string) $row->roundcode); ?></td>
                <td><?php echo $this->escape((string) $row->projectteam1); ?></td>
                <td class="text-center"><?php echo $this->escape((string) $row->projectteam1result); ?> : <?php echo $this->escape((string) $row->projectteam2result); ?></td>
                <td><?php echo $this->escape((string) $row->projectteam2); ?></td>
                <td class="text-center">
                    <span class="<?php echo (int) $row->published === 1 ? 'icon-check text-success' : 'icon-times text-danger'; ?>"
                          title="<?php echo Text::_((int) $row->published === 1 ? 'JPUBLISHED' : 'JUNPUBLISHED'); ?>"></span>
                </td>
                <td class="text-center"><?php echo (int) $row->mid; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php echo $this->pagination->getListFooter(); ?>
