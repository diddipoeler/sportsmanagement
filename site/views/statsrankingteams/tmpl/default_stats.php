<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage statsrankingteams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

?>
<table class="<?php echo $this->config['table_class'];?>">
<thead>
<tr class="">
<th class="td_r rank"><?php	echo Text::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_RANK' );	?></th>  
<th class="td_l"><?php	echo Text::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_TEAM' );	?></th>  
<?php  
foreach ( $this->stats AS $rows )
{
  if ( $rows->_name == 'basic' )
  {  
?>  
<th class="td_r" class="nowrap"><?php	echo Text::_($rows->name); ?></th>
<?php  
}
  }  
  
?>  
<th class="td_r" class="nowrap"><?php	echo Text::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TOTAL'); ?></th>
</tr>
</thead>  


<?php
$rank = 1;
foreach ( $this->teamstotal as $key => $value )
{
$team = $this->teams[$value[team_id]];
$routeparameter = array();
$routeparameter['cfg_which_database'] = Factory::getApplication()->input->get('cfg_which_database', 0), ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0));
$routeparameter['s'] = Factory::getApplication()->input->get('s', '');
$routeparameter['p'] = $this->project->id;
$routeparameter['tid'] = $value[team_id];
$routeparameter['ptid'] = 0;
$routeparameter['division'] = 0;				
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);	
$teamName = sportsmanagementHelper::formatTeamName($team, 't'.$value[team_id].'st'.$rank.'p', $this->config, $isFavTeam, $link);

?>
<tr>
<td class="td_r rank"><?php echo $rank;?></td>
<td class="td_r rank"><?php echo $teamName;?></td>
<?php
foreach ( $this->stats AS $rows => $rowvalue )
{
if ( $rowvalue->_name == 'basic' )
{  
?>  
<td class="td_r" class="nowrap"><?php echo $value[$rows]; ?></td>
<?php  
}

}
?>
<td class="td_r" class="nowrap"><?php echo $value[total]; ?></td>
</tr>
<?php
$rank++;
}

?>









</table>  
