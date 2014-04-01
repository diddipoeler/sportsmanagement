<?php
/**
 * @version $Id: default.php 4905 2010-01-30 08:51:33Z and_one $
 * @package Joomleague
 * @subpackage  Joomleague navigation module
 * @copyright	Copyright (C) 2005-2014 joomleague.at. All rights reserved.
 * @license	 GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="jl-nav-module">
<div class="jl-nav-module<?php echo $params->get('moduleclass_sfx'); ?>">

<form method="post" action="<?php echo JUri::root();?>">

<ul class="nav-list">

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