<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default_stats.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage stats
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

?>
<div class="<?php echo $this->divclassrow;?> table-responsive" id="stats">
<table class="<?php echo $this->config['stats_table_class'];?>">
<thead>
    <tr class="sectiontableheader">
        <th colspan="2"><?php	echo Text::_('COM_SPORTSMANAGEMENT_STATS_GENERAL'); ?></th>
    </tr>
</thead>
<tbody>
    <tr class="sectiontableentry1">
        <td class="statlabel"><?php	echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHDAYS'); ?>:</td>
        <td class="statvalue"><?php	echo $this->totalrounds; ?></td>
    </tr>
    <tr class="sectiontableentry2">
        <td class="statlabel"><?php	echo Text::_('COM_SPORTSMANAGEMENT_STATS_CURRENT_MATCHDAY');    ?>:
        </td>
        <td class="statvalue"><?php	echo $this->actualround; ?></td>
    </tr>
    <tr class="sectiontableentry1">
        <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_PER_MATCHDAY'); ?>:</td>
        <td class="statvalue"><?php	echo ($this->totalrounds > 0 ? round(($this->totals->totalmatches / $this->totalrounds), 2) : 0); ?>
        </td>
    </tr>
    <tr class="sectiontableentry2">
        <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_OVERALL');?>:</td>
        <td class="statvalue"><?php	echo $this->totals->totalmatches;?></td>
    </tr>
    <tr  class="sectiontableentry1">
        <td class="statlabel"><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_PLAYED');?>:</td>
        <td class="statvalue"><?php	echo $this->totals->playedmatches;?></td>
    </tr>
  
    <?php	if ($this->config['home_away_stats']) : ?>
    <tr  class="sectiontableentry2">
        <td class="statlabel"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WON_HOME');?>:</b>
        <br />
    <?php
    if($this->totals->playedmatches>0 && $this->highest_home) {
        echo $this->highest_home->hometeam." - ".$this->highest_home->guestteam;
    } ?>
        </td>
        <td class="statvalue"><br />
    <?php
    if($this->totals->playedmatches>0 && $this->highest_home) {
        echo $this->highest_home->homegoals.$this->overallconfig['seperator'].$this->highest_home->guestgoals;
    } ?>
        </td>
    </tr>
    <tr  class="sectiontableentry1">
        <td class="statlabel"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WON_AWAY');?>:</b>
        <br />
    <?php
    if($this->totals->playedmatches>0 && $this->highest_away) {
        $projectteamid = $this->highest_away->project_hometeam_id;
        $homeTeaminfo = sportsmanagementModelProject::getTeaminfo($projectteamid);
        echo $homeTeaminfo->name ." - ".$this->highest_away->guestteam;
    }
      
    ?>
        </td>
        <td class="statvalue"><br />
    <?php
    if($this->totals->playedmatches>0 && $this->highest_away) {
        echo $this->highest_away->homegoals.$this->overallconfig['seperator'].$this->highest_away->guestgoals;
    }?>
        </td>
    </tr>
    <?php	else :
    if (( $this->highest_home->homegoals - $this->highest_home->guestgoals ) >        ( $this->highest_away->guestgoals - $this->highest_away->homegoals )
    ) {
        $this->highest = $this->highest_home;
    }
    else
    {
        $this->highest = $this->highest_away;
    }
    ?>
    <tr  class="sectiontableentry2">
        <td class="statlabel"><b><?php echo Text::_('COM_SPORTSMANAGEMENT_STATS_MATCHES_HIGHEST_WIN');?>:</b>
        <br />
    <?php echo $this->highest->hometeam." - ".$this->highest->guestteam; ?>
        </td>
        <td class="statvalue"><br />
    <?php echo $this->highest->homegoals." : ".$this->highest->guestgoals;?>
        </td>
    </tr>
    <?php endif; ?>
</tbody>	
</table>
</div>
