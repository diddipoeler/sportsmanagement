<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage playground
 */

defined( '_JEXEC' ) or die( 'Restricted access' ); 
?>

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS'); ?></h2>
<!-- Now show teams of this club -->
<div class="row-fluid">
	<?php foreach ((array)$this->teams AS $key => $value): ?>	
	  <?php $projectname = $value->project; ?> 
	  <?php foreach ($value->project_team AS $team): 
			$teaminfo = $value->teaminfo[0][0];
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
$routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
$routeparameter['p'] = $team->project_id;
$routeparameter['tid'] = $team->team_id;
$routeparameter['ptid'] = 0;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo',$routeparameter);
			//$link = sportsmanagementHelperRoute :: getTeamInfoRoute($team->project_id, $team->team_id);	
			?>
			<h4><?php echo $projectname . " - " . JHtml :: link($link, $teaminfo->name) . ($teaminfo->short_name ? " (" . $teaminfo->short_name . ")" : ''); ?></h4>
			<div class="clubteaminfo">
        	<?php
        	$description = $teaminfo->notes;
        	echo (!empty($description) ? JText :: _('COM_SPORTSMANAGEMENT_PLAYGROUND_TEAMINFO') . " " . JHtml::_('content.prepare', $description) : ''); ?>
			</div>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>
