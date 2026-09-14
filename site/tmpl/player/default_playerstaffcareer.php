<?php
/**
 * Native Joomla 5/6 player staff career layout.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa https://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$history = is_array($this->historyPlayerStaff ?? null) ? $this->historyPlayerStaff : [];

if (!$history) {
    return;
}

$config = is_array($this->config ?? null) ? $this->config : [];
$containerClass = $this->escape((string) ($this->divclassrow ?? ''));
$tableClass = $this->escape((string) ($config['history_table_class'] ?? 'table'));
$databaseSelector = (int) ($this->input->getInt('cfg_which_database', 0));
$seasonId = (int) ($this->input->getInt('s', 0));
$showTeamLink = (int) ($config['show_staffcareer_teamlink'] ?? 0) === 1;
?>
<div class="<?php echo $containerClass; ?> table-responsive" id="playerstaffcareer">
    <h2><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_STAFF_CAREER'); ?></h2>

    <table class="<?php echo $tableClass; ?> table-responsive">
        <tr>
            <td>
                <table id="playerhistory" class="<?php echo $tableClass; ?> table-responsive">
                    <thead>
                        <tr class="sectiontableheader">
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_COMPETITION'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_SEASON'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_TEAM'); ?></th>
                            <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_PERSON_POSITION'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $station) : ?>
                            <?php
                            $playerRoute = SiteRouteHelper::view('player', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => (string) ($station->project_slug ?? ''),
                                'tid' => (string) ($station->team_slug ?? ''),
                                'pid' => (string) ($station->person_slug ?? ''),
                            ]);
                            $rosterRoute = SiteRouteHelper::view('roster', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => (string) ($station->project_slug ?? ''),
                                'tid' => (string) ($station->team_slug ?? ''),
                                'ptid' => 0,
                            ]);
                            $projectName = $this->escape((string) ($station->project_name ?? ''));
                            $seasonName = $this->escape((string) ($station->season_name ?? ''));
                            $teamName = $this->escape((string) ($station->team_name ?? ''));
                            ?>
                            <tr>
                                <td class="td_l"><?php echo HTMLHelper::link($playerRoute, $projectName); ?></td>
                                <td class="td_l"><?php echo $seasonName; ?></td>
                                <td class="td_l">
                                    <?php echo $showTeamLink ? HTMLHelper::link($rosterRoute, $teamName) : $teamName; ?>
                                </td>
                                <td class="td_l"><?php echo Text::_((string) ($station->position_name ?? '')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
