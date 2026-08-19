<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_history.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SIMPLE_LEAGUE');
echo $this->loadTemplate('jsm_notes');

$mediawikitable = array();
$mediawikitable[] = '{| class="wikitable sortable"';
$mediawikitable[] = '|+ Ligenzugehörigkeit ' . $this->team->tname;
$mediawikitable[] = '|-';
$mediawikitable[] = '! Saison !! Liga !! Platz !! gespielt !! gewonnen !! unentschieden !! verloren !! Tore !! Punkte';
foreach ($this->seasons as $season) if ($season->project_type == 'SIMPLE_LEAGUE')
{
    $mediawikitable[] = '|-';
    $mediawikitable[] = '|' . $season->season . '||' . $season->league . '||' . $season->rank . '||' . $season->matches_finally . '||' . $season->won_finally . '||' . $season->draws_finally . '||' . $season->lost_finally . '||' . $season->homegoals_finally . '-' . $season->guestgoals_finally . '||' . $season->points_finally . ':' . $season->neg_points_finally;
}
$mediawikitable[] = '|}';

$js = "\nfunction downmediwiki() {\nalert('" . implode('\\r', $mediawikitable) . "');\n}\n";
$this->document->addScriptDeclaration($js);
?>
<button name="insertwikipage" onclick="javascript:downmediwiki()">
    <?php echo HTMLHelper::_('image', 'media/com_sportsmanagement/jl_images/mediawiki.png', Text::_('COM_SPORTSMANAGEMENT_FES_OVERALL_PARAM_LABEL_SHOW_BUTTON_DOWNLOAD_MEDIAWIKI'), array('width' => 40)); ?> Mediawiki
</button>
<table class="<?PHP echo $this->config['table_class']; ?>">
    <thead>
    <tr class="sectiontableheader">
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
        <?php if ($this->project->project_type == 'DIVISIONS_LEAGUE') { ?>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_DIVISION'); ?></th>
        <?php } ?>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_RANK'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_POINTS'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?></th>
        <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS'); ?></th>
        <?php if ($this->config['show_teams_roster_mean_age']) { ?>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE'); ?></th>
        <?php } ?>
        <?php if ($this->config['show_teams_roster_market_value']) { ?>
            <th class="" nowrap="" style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
        <?php } ?>
    </tr>
    </thead>
    <?php
    foreach ($this->seasons as $season) if ($season->project_type == 'SIMPLE_LEAGUE')
    {
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $this->input->getInt('s', 0);
        $routeparameter['p'] = $season->project_slug;
        $routeparameter['type'] = 0;
        $routeparameter['r'] = $season->round_slug;
        $routeparameter['from'] = 0;
        $routeparameter['to'] = 0;
        $routeparameter['division'] = $season->division_slug;
        $ranking_link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $this->input->getInt('s', 0);
        $routeparameter['p'] = $season->project_slug;
        $routeparameter['r'] = $season->round_slug;
        $routeparameter['division'] = $season->division_slug;
        $routeparameter['mode'] = '';
        $routeparameter['order'] = '';
        $routeparameter['layout'] = '';
        $results_link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $this->input->getInt('s', 0);
        $routeparameter['p'] = $season->project_slug;
        $routeparameter['tid'] = $this->team->slug;
        $routeparameter['division'] = $season->division_slug;
        $routeparameter['mode'] = 0;
        $routeparameter['ptid'] = $season->ptid;
        $teamplan_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $this->input->getInt('s', 0);
        $routeparameter['p'] = $season->project_slug;
        $routeparameter['tid'] = $this->team->slug;
        $teamstats_link = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats', $routeparameter);

        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $this->input->getInt('cfg_which_database', 0);
        $routeparameter['s'] = $this->input->getInt('s', 0);
        $routeparameter['p'] = $season->project_slug;
        $routeparameter['tid'] = $season->team_slug;
        $routeparameter['ptid'] = $season->ptid;
        $players_link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
        ?>
        <tr class="">
            <td><?php echo $season->season; ?></td>
            <td><?php echo JSMCountries::getCountryFlag($season->leaguecountry) . $season->league; ?></td>
            <td>
                <?php
                if ($this->config['show_team_hist_picture'])
                {
                    $picture = !$season->season_picture ? sportsmanagementHelper::getDefaultPlaceholder('team') : $season->season_picture;
                    echo sportsmanagementHelperHtml::getBootstrapModalImage(
                        'teaminfohistory' . $season->ptid . '-' . $season->projectid,
                        $picture,
                        $this->team->name,
                        '50',
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $this->overallconfig['use_jquery_modal']
                    );
                }
                else
                {
                    echo sportsmanagementHelperHtml::getBootstrapModalImage(
                        'teaminfohistory' . $season->ptid . '-' . $season->projectid,
                        'media/com_sportsmanagement/jl_images/icon_copyright_2.png',
                        $this->team->name,
                        '50',
                        '',
                        $this->modalwidth,
                        $this->modalheight,
                        $this->overallconfig['use_jquery_modal']
                    );
                }

                if ($this->showediticon)
                {
                    $link = 'index.php?option=com_sportsmanagement&tmpl=component&view=editprojectteam&ptid=' . $season->ptid . '&tid=' . $this->teamid . '&p=' . $season->projectid;
                    echo sportsmanagementHelperHtml::getBootstrapModalImage(
                        'teamedit' . $season->ptid,
                        'administrator/components/com_sportsmanagement/assets/images/teams.png',
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMINFO_EDIT_DETAILS'),
                        '20',
                        $link,
                        $this->modalwidth,
                        $this->modalheight,
                        $this->overallconfig['use_jquery_modal']
                    );
                }
                ?>
            </td>
            <?php if ($this->project->project_type == 'DIVISIONS_LEAGUE') { ?>
                <td><?php echo $season->division_name; ?></td>
            <?php } ?>
            <td><?php echo $this->config['show_teams_ranking_link'] == 1 ? HTMLHelper::link($ranking_link, $season->rank) : $season->rank; ?></td>
            <td><?php echo $season->games; ?></td>
            <td><?php echo $this->config['show_teams_results_link'] == 1 ? HTMLHelper::link($results_link, $season->points) : $season->points; ?></td>
            <td><?php echo $this->config['show_teams_teamplan_link'] == 1 ? HTMLHelper::link($teamplan_link, $season->series) : $season->series; ?></td>
            <td><?php echo $this->config['show_teams_teamstats_link'] == 1 ? HTMLHelper::link($teamstats_link, $season->goals) : $season->goals; ?></td>
            <td><?php echo $this->config['show_teams_roster_link'] == 1 ? HTMLHelper::link($players_link, $season->playercnt) : $season->playercnt; ?></td>
            <?php if ($this->config['show_teams_roster_mean_age'] == 1) { ?>
                <td align="right"><?php echo HTMLHelper::link($players_link, $season->playermeanage); ?></td>
            <?php } ?>
            <?php if ($this->config['show_teams_roster_market_value'] == 1) { ?>
                <td align="right"><?php echo HTMLHelper::link($players_link, number_format($season->market_value, 0, ',', '.')); ?></td>
            <?php } ?>
        </tr>
        <?php
    }
    ?>
</table>
