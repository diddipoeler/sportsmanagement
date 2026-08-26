<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage statsrankingteams
 * @file       default_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

$input = $this->input;
?>
<table class="<?php echo $this->config['table_class']; ?>">
    <thead>
    <tr class="">
        <th class="td_r rank"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_RANK'); ?></th>
        <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATSRANKING_TEAM'); ?></th>
        <?php
        foreach ($this->stats as $rows)
        {
            if ($rows->_name == 'basic')
            {
                ?>
                <th class="td_r nowrap"><?php echo Text::_($rows->name); ?></th>
                <?php
            }
        }
        ?>
        <th class="td_r nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TOTAL'); ?></th>
    </tr>
    </thead>

    <?php
    $rank = 1;

    foreach ($this->teamstotal as $value)
    {
        $teamId = (int) ($value['team_id'] ?? 0);
        if ($teamId <= 0 || !isset($this->teams[$teamId]))
        {
            continue;
        }

        $team = $this->teams[$teamId];
        $routeparameter = array();
        $routeparameter['cfg_which_database'] = $input->getInt('cfg_which_database', 0)
            ? ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            : 0;
        $routeparameter['s'] = $input->get('s', '');
        $routeparameter['p'] = $this->project->id;
        $routeparameter['tid'] = $teamId;
        $routeparameter['ptid'] = 0;
        $routeparameter['division'] = 0;
        $link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
        $isFavTeam = false;
        $teamName = sportsmanagementHelper::formatTeamName(
            $team,
            't' . $teamId . 'st' . $rank . 'p',
            $this->config,
            $isFavTeam,
            $link
        );
        ?>
        <tr>
            <td class="td_r rank"><?php echo $rank; ?></td>
            <td class="td_r rank"><?php echo $teamName; ?></td>
            <?php
            foreach ($this->stats as $rows => $rowvalue)
            {
                if ($rowvalue->_name == 'basic')
                {
                    ?>
                    <td class="td_r nowrap"><?php echo $value[$rows] ?? 0; ?></td>
                    <?php
                }
            }
            ?>
            <td class="td_r nowrap"><?php echo $value['total'] ?? 0; ?></td>
        </tr>
        <?php
        $rank++;
    }
    ?>
</table>
