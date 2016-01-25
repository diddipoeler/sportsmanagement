<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' ); ?>

<h2><?php echo JText::_('COM_SPORTSMANAGEMENT_PLAYGROUND_CLUB_TEAMS'); ?></h2>
<!-- Now show teams of this club -->
<div class="venuecontent">
	<?php foreach ((array)$this->teams AS $key => $value): ?>	
	  <?php $projectname = $value->project; ?> 
	  <?php foreach ($value->project_team AS $team): 
			$teaminfo = $value->teaminfo[0][0];
            $routeparameter = array();
$routeparameter['cfg_which_database'] = JRequest::getInt('cfg_which_database',0);
$routeparameter['s'] = JRequest::getInt('s',0);
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
