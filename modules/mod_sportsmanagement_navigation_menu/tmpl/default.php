<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="jl-nav-module">
<div class="jl-nav-module">

<form method="post" action="<?php echo JUri::root();?>">

<ul class="nav menu<?php echo $params->get('moduleclass_sfx'); ?>">

	<?php if ($params->get('show_project_dropdown') == 'season') :?>
		<?php if ($seasonselect): ?>
		<li class="season-select"><?php echo $seasonselect; ?></li>
		<?php endif; ?>
		<?php if ($leagueselect): ?>
		<li class="league-select"><?php echo $leagueselect; ?></li>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ($params->get('show_project_dropdown')) :?>
		<?php if ($projectselect): ?>
		<li class="project-select"><?php echo $projectselect; ?></li>
		<?php endif; ?>
	<?php endif; ?>
	
	<?php if ($params->get('show_division_dropdown')) :?>
		<?php if ($divisionselect): ?>
		<li class="division-select"><?php echo $divisionselect; ?></li>
		<?php endif; ?>
	<?php endif; ?>
	
		<?php if ($params->get('show_teams_dropdown') && $teamselect): ?>
	<li class="team-select">
		<?php if ($params->get('heading_teams_dropdown')): ?>
			<span class="label"><?php echo $params->get('heading_teams_dropdown'); ?></span><?php echo $teamselect; ?>
		<?php else: ?>
			<?php echo $teamselect; ?>
		<?php endif; ?>
	</li>
	<?php endif; ?>
	
	<?php if ($params->get('show_nav_links')): ?>
	
		<?php for ($i = 1; $i < 17; $i++): ?>
			<?php if ($params->get('navpoint'.$i) && $link = $helper->getLink($params->get('navpoint'.$i))): ?>
				<li class="nav-item"><?php echo JHtml::link(JRoute::_($link), $params->get('navpoint_label'.$i)); ?></li>
			<?php elseif ($params->get('navpoint'.$i) == "separator"): ?>
				<li class="nav-item separator"><?php echo $params->get('navpoint_label'.$i); ?></li>
			<?php endif; ?>
		<?php endfor; ?>		
		
	<?php endif; ?>
</ul>

<input type="hidden" name="option" value="com_sportsmanagement" />
<input type="hidden" name="view" value="<?php echo $defaultview; ?>" />
<input type="hidden" name="teamview" value="<?php echo $params->get('link_teams_dropdown', 'roster'); ?>" />
<input type="hidden" name="o" value="<?php echo $params->get('project_ordering', 0); ?>" />
<input type="hidden" name="include_season" value="<?php echo $params->get('project_include_season_name', 0); ?>" />
<input type="hidden" name="itemid" value="<?php echo $defaultitemid; ?>" />
</form>

</div>
</div>