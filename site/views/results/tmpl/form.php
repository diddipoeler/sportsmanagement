<?php
/**
 * SportsManagement results edit form.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$app = Factory::getApplication();
$input = $app->getInput();
$user = $app->getIdentity();
$uri = Uri::getInstance();
$saveshortall = $user->authorise('editmatch.saveshortall', 'com_sportsmanagement');
$saveshortresults = $user->authorise('editmatch.saveshortresults', 'com_sportsmanagement');
$databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = (int) ($this->project->season_id ?? $input->getInt('s', 0));
$projectRoute = (string) ($this->project->slug ?? $this->project->id ?? $input->getInt('p', 0));
$roundRoute = (string) ($this->roundid ?? $input->getInt('r', 0));
$divisionId = $input->getInt('division', 0);
$mode = $input->getInt('mode', 0);
$order = $input->getInt('order', 0);

if (!$this->showediticon) {
    $app->redirect(str_ireplace('layout=form', '', $uri->toString()), Text::_('ALERTNOTAUTH'));
}
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="defaultresultsform" style="overflow:auto;">
    <table class="table" id="results-tmpl-form-contentheading">
        <tr>
            <td class="contentheading">
                <?php
                if ($this->roundid > 0) {
                    sportsmanagementHelperHtml::showMatchdaysTitle(
                        Text::_('COM_SPORTSMANAGEMENT_RESULTS_ENTER_EDIT_RESULTS'),
                        $this->roundid,
                        $this->config
                    );

                    if ($this->showediticon) {
                        $routeparameter = [
                            'cfg_which_database' => $databaseSelector,
                            's' => $seasonId,
                            'p' => $projectRoute,
                            'r' => $roundRoute,
                            'division' => $divisionId,
                            'mode' => $mode,
                            'order' => $order,
                            'layout' => '',
                        ];
                        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);
                        $imgTitle = Text::_('COM_SPORTSMANAGEMENT_RESULTS_CLOSE_EDIT_RESULTS');
                        $desc = HTMLHelper::image(
                            'media/com_sportsmanagement/jl_images/edit_exit.png',
                            $imgTitle,
                            ['title' => $imgTitle]
                        );
                        echo '&nbsp;' . HTMLHelper::link($link, $desc);
                    }
                }
                ?>
            </td>
            <td>
                <?php
                echo RoundPaginationHelper::selectNavigation(
                    $this->project,
                    $databaseSelector,
                    $seasonId,
                    'form'
                );
                ?>
            </td>
        </tr>
    </table>

    <form name="adminForm" id="adminForm" method="post" action="<?php echo $uri->toString(); ?>">
        <table class="<?php echo $this->config['table_class']; ?>" id="results-tmpl-form">
            <?php
            if (count($this->matches) > 0) {
                $colspan = ($this->project->allow_add_time) ? 15 : 14;
                ?>
                <thead>
                <tr>
                    <th width="20" style="vertical-align: top;">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->matches); ?>);"/>
                    </th>
                    <th style="vertical-align: top;"></th>
                    <th style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_ROUND'); ?></th>
                    <?php
                    if ($this->project->project_type == 'DIVISIONS_LEAGUE') {
                        $colspan++;
                        ?>
                        <th style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DIVISION'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_number']) {
                        ?>
                        <th width="20" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_MATCHNR'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_date']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_DATE'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_time']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_TIME'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_time_present']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PRESENT'); ?></th>
                        <?php
                    }
                    ?>
                    <th colspan="2" class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_HOME_TEAM'); ?></th>
                    <th colspan="2" class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_AWAY_TEAM'); ?></th>
                    <th style="text-align:center; vertical-align: top;">
                        <?php
                        echo Text::_($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART'
                            ? 'COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_DART'
                            : 'COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT');
                        ?>
                    </th>
                    <?php
                    if ($this->project->allow_add_time) {
                        ?>
                        <th style="text-align:center; vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_RESULT_TYPE'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_events']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;">
                            <?php
                            echo Text::_($this->project->sport_type_name == 'COM_SPORTSMANAGEMENT_ST_DART'
                                ? 'COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS_DARTS'
                                : 'COM_SPORTSMANAGEMENT_EDIT_RESULTS_EVENTS');
                            ?>
                        </th>
                        <?php
                    }

                    if ($this->config['show_edit_match_statistic']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_STATISTICS'); ?></th>
                        <?php
                    }

                    if ($this->config['show_edit_match_referees']) {
                        ?>
                        <th class="title nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_REFEREE'); ?></th>
                        <?php
                    }
                    ?>
                    <th width="1%" class="nowrap" style="vertical-align: top;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EDIT_RESULTS_PUBLISHED'); ?></th>
                </tr>
                </thead>
                <?php
                $i = 0;
                foreach ($this->matches as $match) {
                    if (isset($match->allowed) && $match->allowed) {
                        $this->game = $match;
                        $this->i = $i;
                        echo $this->loadTemplate('row');
                    }
                    $i++;
                }
            }
            ?>
        </table>
        <br/>
        <input type="hidden" name="option" value="com_sportsmanagement"/>
        <input type="hidden" name="view" value="results"/>
        <input type="hidden" name="cfg_which_database" value="<?php echo $databaseSelector; ?>"/>
        <input type="hidden" name="s" value="<?php echo $seasonId; ?>"/>
        <input type="hidden" name="p" value="<?php echo (int) ($this->project->id ?? 0); ?>"/>
        <input type="hidden" name="r" value="<?php echo $roundRoute; ?>"/>
        <input type="hidden" name="division" value="<?php echo $divisionId; ?>"/>
        <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
        <input type="hidden" name="order" value="<?php echo $order; ?>"/>
        <input type="hidden" name="layout" value="form"/>
        <input type="hidden" name="task" value="results.saveshort"/>
        <input type="hidden" name="saveshortall" value="<?php echo (int) $saveshortall; ?>"/>
        <input type="hidden" name="sel_r" value="<?php echo $roundRoute; ?>"/>
        <input type="hidden" name="Itemid" value="<?php echo $input->getInt('Itemid', 1); ?>"/>
        <input type="hidden" name="boxchecked" value="0" id="boxchecked"/>
        <input type="hidden" name="checkmycontainers" value="0" id="checkmycontainers"/>
        <input type="hidden" name="save_data" value="1" class="button"/>
        <input type="submit" name="save" value="<?php echo Text::_('JSAVE'); ?>"/>
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
<br/>
<div class="display-limit">
    <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
    <?php echo $this->pagination->getLimitBox(); ?>
</div>
<div class="pagination">
    <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
    <p class="counter"><?php echo $this->pagination->getResultsCounter(); ?></p>
    <?php echo $this->pagination->getPagesLinks(); ?>
</div>
<table class="table not-playing" width="96%" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td style="text-align:center;">
            <?php echo $this->showNotPlayingTeams($this->matches, $this->teams, $this->config, $this->favteams, $this->project); ?>
        </td>
    </tr>
</table>
