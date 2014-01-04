<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 

//jimport('joomla.html.pane');

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
    echo JHTML::_('tabs.start','tabs_matchreport_stats', array('useCookie'=>1));
// 		$pane =& JPane::getInstance('tabs',array('startOffset'=>0));
// 		echo $pane->startPane('pane');
// 		echo $pane->startPanel($this->team1->name,'panel1');
		echo JHTML::_('tabs.panel', $this->team1->name, 'panel'.($idxTab++));
    echo $this->loadTemplate('stats_home');
// 		echo $pane->endPanel();
		
// 		echo $pane->startPanel($this->team2->name,'panel2');
		echo JHTML::_('tabs.panel', $this->team2->name, 'panel'.($idxTab++));
    echo $this->loadTemplate('stats_away');
// 		echo $pane->endPanel();
		
// 		echo $pane->endPane();
    echo JHTML::_('tabs.end');
    
	endif;
}
?>
<!-- END of game stats -->
