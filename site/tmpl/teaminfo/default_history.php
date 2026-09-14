<?php
/**
 * Joomla 5/6 Teaminfo history layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$cfgWhichDatabase = $this->databaseSelector;
$seasonFilter = $this->input->getInt('s', 0);
$componentParams = ComponentHelper::getParams('com_sportsmanagement');
$teamPlaceholder = trim((string) $componentParams->get('ph_team', ''));
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);

$this->notes = [];
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY');
echo $this->loadTemplate('jsm_notes');
?>
<table class="<?php echo $this->config['table_class']; ?>">
    <thead>
    <tr class="sectiontableheader">
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?>
        </th>
        <?php if ($this->project->project_type === 'DIVISIONS_LEAGUE') : ?>
            <th nowrap="" style="background:#BDBDBD;">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_DIVISION'); ?>
            </th>
        <?php endif; ?>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_RANK'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_POINTS'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?>
        </th>
        <th nowrap="" style="background:#BDBDBD;">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS'); ?>
        </th>

        <?php if (!empty($this->config['show_teams_roster_mean_age'])) : ?>
            <th nowrap="" style="background:#BDBDBD;">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE'); ?>
            </th>
        <?php endif; ?>

        <?php if (!empty($this->config['show_teams_roster_market_value'])) : ?>
            <th nowrap="" style="background:#BDBDBD;">
                <?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?>
            </th>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->seasons as $season) : ?>
        <?php
        $rankingLink = SiteRouteHelper::view('ranking', [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'type' => 0,
            'r' => $season->round_slug,
            'from' => 0,
            'to' => 0,
            'division' => $season->division_slug,
        ]);

        $resultsLink = SiteRouteHelper::view('results', [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'r' => $season->round_slug,
            'division' => $season->division_slug,
            'mode' => '',
            'order' => '',
            'layout' => '',
        ]);

        $teamPlanLink = SiteRouteHelper::view('teamplan', [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'tid' => $this->team->slug,
            'division' => $season->division_slug,
            'mode' => 0,
            'ptid' => $season->ptid,
        ]);

        $teamStatsLink = SiteRouteHelper::view('teamstats', [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'tid' => $this->team->slug,
        ]);

        $playersLink = SiteRouteHelper::view('roster', [
            'cfg_which_database' => $cfgWhichDatabase,
            's' => $seasonFilter,
            'p' => $season->project_slug,
            'tid' => $season->team_slug,
            'ptid' => $season->ptid,
        ]);

        $seasonPicture = trim((string) ($season->season_picture ?? ''));
        if ($seasonPicture === '') {
            $seasonPicture = $teamPlaceholder;
        }
        ?>
        <tr>
            <td><?php echo $season->season; ?></td>
            <td><?php echo CountryPresentationHelper::flag((string) $season->leaguecountry) . $season->league; ?></td>
            <td>
                <?php if (!empty($this->config['show_team_hist_picture'])) : ?>
                    <?php if ($seasonPicture !== '') : ?>
                        <?php
                        echo ModalImageHelper::render(
                            'teaminfohistory' . $season->ptid . '-' . $season->projectid,
                            $seasonPicture,
                            (string) $this->team->name,
                            50,
                            '',
                            $this->modalwidth,
                            $this->modalheight,
                            $modalMode
                        );
                        ?>
                    <?php endif; ?>
                <?php else : ?>
                    <?php
                    echo ModalImageHelper::render(
                        'teaminfohistory' . $season->ptid . '-' . $season->projectid,
                        'media/com_sportsmanagement/jl_images/icon_copyright_2.png',
                        (string) $this->team->name,
                        50,
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode
                    );
                    ?>
                <?php endif; ?>

                <?php if ($this->showediticon) : ?>
                    <?php
                    $editLink = 'index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid='
                        . (int) $season->ptid . '&tid=' . (int) $this->teamid . '&p=' . (int) $season->projectid;
                    echo ModalImageHelper::render(
                        'teamedit' . $season->ptid,
                        'administrator/components/com_sportsmanagement/assets/images/teams.png',
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
                        20,
                        $editLink,
                        $this->modalwidth,
                        $this->modalheight,
                        $modalMode
                    );
                    ?>
                <?php endif; ?>
            </td>

            <?php if ($this->project->project_type === 'DIVISIONS_LEAGUE') : ?>
                <td><?php echo $season->division_name; ?></td>
            <?php endif; ?>

            <td>
                <?php echo (int) $this->config['show_teams_ranking_link'] === 1
                    ? HTMLHelper::link($rankingLink, $season->rank)
                    : $season->rank; ?>
            </td>
            <td><?php echo $season->games; ?></td>
            <td>
                <?php echo (int) $this->config['show_teams_results_link'] === 1
                    ? HTMLHelper::link($resultsLink, $season->points)
                    : $season->points; ?>
            </td>
            <td>
                <?php echo (int) $this->config['show_teams_teamplan_link'] === 1
                    ? HTMLHelper::link($teamPlanLink, $season->series)
                    : $season->series; ?>
            </td>
            <td>
                <?php echo (int) $this->config['show_teams_teamstats_link'] === 1
                    ? HTMLHelper::link($teamStatsLink, $season->goals)
                    : $season->goals; ?>
            </td>
            <td>
                <?php echo (int) $this->config['show_teams_roster_link'] === 1
                    ? HTMLHelper::link($playersLink, $season->playercnt)
                    : $season->playercnt; ?>
            </td>

            <?php if ((int) $this->config['show_teams_roster_mean_age'] === 1) : ?>
                <td class="text-end"><?php echo HTMLHelper::link($playersLink, $season->playermeanage); ?></td>
            <?php endif; ?>

            <?php if ((int) $this->config['show_teams_roster_market_value'] === 1) : ?>
                <td class="text-end">
                    <?php echo HTMLHelper::link($playersLink, number_format((float) $season->market_value, 0, ',', '.')); ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
