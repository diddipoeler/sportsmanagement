<?php
/** Joomla 5/6 individual-sport result editor. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$smallBore = (string) ($this->projectws->sports_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION';
$canChange = true;
?>
<div class="table-responsive" id="editcell" data-jsm-close-editor="<?php echo $this->close ? '1' : '0'; ?>">
    <fieldset class="adminform">
        <legend><?php echo Text::sprintf(
            'COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE2',
            '<i>' . $this->roundws->name . '</i>',
            '<i>' . $this->projectws->name . '</i>'
        ); ?></legend>

        <form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <button type="button" class="btn btn-secondary" data-jsm-action="submit-task" data-task="jlextindividualsportes.applyshort">
                    <?php echo Text::_('JAPPLY'); ?>
                </button>
                <button type="button" class="btn btn-success" data-jsm-action="save-close">
                    <?php echo Text::_('JSAVE'); ?>
                </button>
                <button type="button" class="btn btn-danger" data-jsm-action="submit-task" data-task="jlextindividualsportes.delete">
                    <?php echo Text::_('JACTION_DELETE'); ?>
                </button>
            </div>

            <table class="table table-striped jsm-individual-result-table" id="<?php echo $this->view; ?>list">
                <thead>
                    <tr>
                        <th class="jsm-col-row-number"><?php echo count($this->matches) . '/' . $this->pagination->total; ?></th>
                        <th class="jsm-col-check"><?php echo HTMLHelper::_('grid.checkall'); ?></th>
                        <th class="title text-nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MATCHNR'); ?></th>
                        <th class="title text-nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SINGLE_MATCH_TYPE'); ?></th>
                        <th class="title text-nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_HOME_TEAM_PLAYER'); ?></th>
                        <th class="title text-nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_AWAY_TEAM_PLAYER'); ?></th>
                        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT'); ?></th>
                        <?php if ($this->projectws->allow_add_time) : ?>
                            <th class="text-center"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RESULT_TYPE'); ?></th>
                        <?php endif; ?>
                        <th class="jsm-col-status text-nowrap"><?php echo Text::_('JSTATUS'); ?></th>
                        <th class="title jsm-col-id text-nowrap">
                            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'mc.id', $this->sortDirection, $this->sortColumn); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <?php if ($smallBore) : ?>
                            <td colspan="<?php echo $this->projectws->allow_add_time ? 10 : 9; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
                        <?php else : ?>
                            <td colspan="3"><?php echo $this->pagination->getListFooter(); ?></td>
                            <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
                <tbody>
                    <?php foreach ($this->matches as $count_i => $item) : ?>
                        <?php require __DIR__ . '/default_result_row.php'; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php $dValue = $this->roundws->round_date_first . ' ' . $this->projectws->start_time; ?>
            <input type="hidden" name="match_date" value="<?php echo $dValue; ?>">
            <input type="hidden" name="act" value="" id="short_act">
            <input type="hidden" name="boxchecked" value="0">
            <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>">
            <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>">
            <input type="hidden" name="rid" value="<?php echo $this->rid; ?>">
            <input type="hidden" name="project_id" value="<?php echo $this->roundws->project_id; ?>">
            <input type="hidden" name="close" id="close" value="0">
            <input type="hidden" name="match_id" value="<?php echo $this->match_id; ?>">
            <input type="hidden" name="projectteam1_id" value="<?php echo $this->projectteam1_id; ?>">
            <input type="hidden" name="projectteam2_id" value="<?php echo $this->projectteam2_id; ?>">
            <input type="hidden" name="task" value="" id="task">
            <?php echo HTMLHelper::_('form.token') . "\n"; ?>
        </form>
    </fieldset>
</div>
