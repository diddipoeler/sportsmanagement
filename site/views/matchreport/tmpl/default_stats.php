<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_stats.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage matchreport
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 

?>

<!-- START: game stats -->
<?php
if (!empty($this->matchplayerpositions ))
{
	$hasMatchPlayerStats = false;
	$hasMatchStaffStats = false;
	foreach ( $this->matchplayerpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchPlayerStats = true;
					break;
				}
			}
		}
	}	
	foreach ( $this->matchstaffpositions as $pos )
	{
		if(isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id])>0) {
			foreach ($this->stats[$pos->position_id] as $stat) {
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport()) {
					$hasMatchStaffStats = true;
				}
			}
		}
	}
	if($hasMatchPlayerStats || $hasMatchStaffStats) :
	?>

	<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'); ?></h2>
	
		<?php
    $idxTab = 100;
    echo JHtml::_('tabs.start','tabs_matchreport_stats', array('useCookie'=>1));
	echo JHtml::_('tabs.panel', $this->team1->name, 'panel'.($idxTab++));
    echo $this->loadTemplate('stats_home');
	echo JHtml::_('tabs.panel', $this->team2->name, 'panel'.($idxTab++));
    echo $this->loadTemplate('stats_away');
    echo JHtml::_('tabs.end');
    
	endif;
}
?>
<!-- END of game stats -->
