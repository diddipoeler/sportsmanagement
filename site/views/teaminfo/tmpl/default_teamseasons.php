<?php
defined('_JEXEC') or die('Restricted access');

if ($this->config['show_teams_seasons'] == "1")
{
	?>
<table class="fixtures">
	<tr class="sectiontableheader">
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TITLE');?></td>
	</tr>
</table>

	<?php
	foreach ($this->seasons as $season)
	{
		?>
<table class="fixtures">
<?php
if ($season->projectname)
{
	?>
	<tr>
		<td><?php
		/*
		 //Maybe this thing with ul and li should be solved by css so everybody may decide for himself about using it or not
		 <ul>
		 <li>
		 */
		?> <a href="javascript:void(0)"
			onclick="switchMenu('tid<?php echo $this->team->id . $season->projectid; ?>');"
			title="<?php echo JText::_('COM_SPORTSMANAGEMENT_SHOW_OPTIONS'); ?>"><?php echo $season->projectname; ?>
		</a> <?php
		/*
		 //Maybe this thing with ul and li should be solved by css so everybody may decide for himself about using it or not
		 </li>
		 </ul>
		 */
		?></td>
	</tr>
	<?php
}

/*
 //We should think about this part with the info.
 //I think it is of no use and might blow up the page size if we have a lot of seasons and long team descripions
 <?php
 if ( $season->info )
 {
 ?>
 <tr>
 <td>
 <b><?php echo JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_TEAMSEASON_DESC');?></b>
 </td>
 <td><?php echo $season->info; ?></td>
 </tr>
 <?php
 }
 */
?>
</table>

<div id="tid<?php echo $this->team->id . $season->projectid;?>"
	align="center" style="display: none"><?php
	if ($this->config['show_teams_logos'])
	{
		$picture = $season->picture;

		if ((@is_null($picture)) or
		(strpos($picture, "/com_sportsmanagement/images/placeholders/placeholder_450.png")) or
		(strpos($picture, "/joomleague/placeholders/placeholder_450.png")))
		{
			$picture = JoomleagueHelper::getDefaultPlaceholder("team");
		}

		$picture_descr = JText::_("COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE") . " " . $this->team->name . " (" . $season->projectname . ")";
		echo JHtml::image($picture, $picture_descr, array("title" => $picture_descr));
	}
	?> <br />
	<?php
	$link = JoomleagueHelperRoute::getPlayersRoute($season->project_slug, $season->team_slug);
	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_PLAYERS'));
	?> <br />
	<?php
	$link = JoomleagueHelperRoute::getResultsRoute($season->project_slug);
	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_RESULTS'));
	?> <br />
	<?php
	$link = JoomleagueHelperRoute::getRankingRoute($season->project_slug);
	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TABLES'));
	?> <br />
</div>
	<?php
	}
}
?>