<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statsrankingteams
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<table class="<?php echo $this->config['table_class'];?>">
<thead>
<tr class="">
<th class="td_r rank"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_RANK' );	?></th>  
<th class="td_l"><?php	echo JText::_( 'COM_SPORTSMANAGEMENT_STATSRANKING_TEAM' );	?></th>  
<?php  
foreach ( $this->stats AS $rows )
{
  if ( $rows->_name == 'basic' )
  {  
?>  
<th class="td_r" class="nowrap"><?php	echo JText::_($rows->name); ?></th>
<?php  
}
  }  
  
?>  
<th class="td_r" class="nowrap"><?php	echo JText::_('COM_SPORTSMANAGEMENT_STATS_ATTENDANCE_RANKING_TOTAL'); ?></th>
</tr>
</thead>  


<?php
$rank = 1;
foreach ( $this->teamstotal as $key => $value )
{


//echo '<pre>'.print_r($value,true).'</pre>';
$team = $this->teams[$value[team_id]];
$routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',JComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database',0));
$routeparameter['s'] = JRequest::getInt('s',0);
$routeparameter['p'] = $this->project->id;
$routeparameter['tid'] = $value[team_id];
$routeparameter['ptid'] = 0;
$routeparameter['division'] = 0;				
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);	
$teamName = sportsmanagementHelper::formatTeamName($team,'t'.$value[team_id].'st'.$rank.'p',$this->config, $isFavTeam, $link);

?>
<tr>
<td class="td_r rank"><?php echo $rank;?></td>
<td class="td_r rank"><?php echo $teamName;?></td>
<?php
foreach ( $this->stats AS $rows => $rowvalue )
{
//echo 'rows <pre>'.print_r($rows ,true).'</pre>';
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
