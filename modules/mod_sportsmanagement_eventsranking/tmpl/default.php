<?php 

/**
 * @version	 $Id: default.php 4971 2018-07-31 05:00:49Z timoline $
 * @package	 Joomla
 * @subpackage  Sportmanagement eventsranking module
 * @developer  llambion 
 * @copyright   Copyright (C) 2008 Open Source Matters. All rights reserved.
 * @license	 GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */



// No direct access
defined('_JEXEC') or die; 
?>

<div class="row-fluid">

<?php
$header = ""; 
if ($params->get('show_project_name', 0))
{
	$header .= $list['project']->name;
}
/*
if ($params->get('show_division_name', 0))
{
	$division = $list['model']->getDivision();
	if (property_exists($division->name) && length($division->name) > 0)
	{
		if (length($header) > 0)
		{
			$header .= " - ";
		}
		$header .= $list['model']->getDivision()->name;
	}
}*/

$showPicture = $params->get('show_picture', 0);
$pictureHeight = $params->get('picture_height', 40);
$pictureWidth = $params->get('picture_width', 40);
$showTeam = $params->get('show_team', 1);
$showLogo = $params->get('show_logo', 0);
$teamLink = $params->get('teamlink', '');
$teamnametype = $params->get('teamnametype', 'short_name');
?>
<p class="projectname"><?php echo $header; ?></p>
<?php 
if (count($list['eventtypes']) > 0)
{
?>
<table class="table">
	<tbody>
	<?php 
	foreach ($list['eventtypes'] as $eventtype)
	{
			$rankingforevent = $list['ranking'];
			?>
		<tr class="sectiontableheader">
			<td class="eventtype"><?php echo JText::_($eventtype->name); ?></td>
		</tr>
		<tr>
			<td>
			<?php
			if (count($rankingforevent) > 0)
			{
				?>
				<table class="<?php echo $params->get('table_class', '');?>">
					<thead>
						<tr class="sectiontableheader">
							<th class="rank"><?php echo JText::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_RANK')?></th>
							<?php if ($showPicture == 1) : ?>
							<th class="picture"><?php echo JText::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_PICTURE');?></th>
							<?php endif; ?>
							<th class="personname"><?php echo JText::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_NAME')?></th>
							<?php if ($showTeam == 1) : ?>
							<th class="team"><?php echo JText::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_TEAM');?></th>
							<?php endif; ?>
							<th class="td_c">
							<?php if ($params->get('show_event_icon', 1)) : ?>
								<?php echo modSMEventsrankingHelper::getEventIcon($eventtype);?>
							<?php else: ?>
								<?php echo JText::_($eventtype->name);?>
							<?php endif; ?>
							</th>
						</tr>
					</thead>
					<tbody>
				<?php
				$lastRank = 0;
				$k = 0;
				foreach (array_slice($rankingforevent, 0, $params->get('limit', 5)) as $item)
				{
					$team = $list['teams'][$item->tid];
					$style_class = ( $k == 0 ) ? 'style_class1' : 'style_class2';
					$class = $params->get($style_class, 0);
					?>
						<tr class="<?php echo $class; ?>">
							<td class="rank">
					<?php
					$rank = ($item->rank == $lastRank) ? "-" : $item->rank;
					$lastRank = $item->rank;
					echo $rank;
					?>
							</td>
					<?php
					if ($showPicture == 1)
					{
						$picture = isset($item->teamplayerpic) ? $item->teamplayerpic : null;
						if ((empty($picture)) || ($picture == sportsmanagementHelper::getDefaultPlaceholder("player")))
						{
							$picture = $item->picture;
						}
						if (!file_exists($picture))
						{
							$picture = sportsmanagementHelper::getDefaultPlaceholder("player");
						}
						$name = sportsmanagementHelper::formatName(null, $item->fname, $item->nname, $item->lname, $params->get("name_format"));
						?>
							<td class="picture">
								<?php echo sportsmanagementHelper::getPictureThumb($picture, $name, $pictureWidth, $pictureHeight ); ?>
							</td>
						<?php
					}
					?>
							<td class="personname">
								<?php modSMEventsrankingHelper::printName($item, $team, $params, $list['project']); ?>
							</td>
					<?php
					if ($showTeam == 1)
					{
						?>
							<td class="team">
						<?php
						if ( $showLogo )
						{
							echo modSMEventsrankingHelper::getLogo($team, $showLogo);
						}
						if ($teamLink)
						{
							echo JHTML::link(modSMEventsrankingHelper::getTeamLink($team, $params, $list['project']), $team->$teamnametype);
						}
						else
						{
							echo $team->$teamnametype;
						}
						?>
							</td>
						<?php
					}
					?>
							<td class="td_c"><?php echo $item->p; ?></td>
						</tr>
					<?php
					$k=(1-$k);
				}
				?>
					</tbody>
				</table>
				<?php
			}
			else
			{
				?>
				<p class="modjlgstat"><?php echo JText::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_ITEMS');?></p>
				<?php
			}
			?>
			</td>
		</tr>
		<?php
		//}
	}
	?>
	</tbody>
</table>
<?php
}
else
{
?>
<p class="modjlgstat"><?php echo JText::_("MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_EVENTS_SELECTED"); ?></p>
<?php
}
?>
<?php if ($params->get('show_full_link', 1)):?>
<p class="fulltablelink">
	<?php //echo JHTML::link(	sportsmanagementHelperRoute::getEventsRankingRoute($list['project']->slug, $params->get('divisionid',0) , $params->get('tid',0), $params->get('evid',0), $params->get('mid',0)), 
							//JText::_('MOD_JOOMLEAGUE_EVENTSRANKING_VIEW_FULL_TABLE')); ?>
</p>
<?php endif; ?>



</div>
