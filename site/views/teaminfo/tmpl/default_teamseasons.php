<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      deafult_teamseasons.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teaminfo
 */

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
		<td>
        <?php

		?> <a href="javascript:void(0)"
			onclick="switchMenu('tid<?php echo $this->team->id . $season->projectid; ?>');"
			title="<?php echo JText::_('COM_SPORTSMANAGEMENT_SHOW_OPTIONS'); ?>"><?php echo $season->projectname; ?>
		</a> 
        <?php

		?></td>
	</tr>
	<?php
}


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
	$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $season->project_slug;
       $routeparameter['tid'] = $season->team_slug;
       $routeparameter['ptid'] = 0;
       		$link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster',$routeparameter);
	
	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_PLAYERS'));
	?> <br />
	<?php
	$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $season->project_slug;
       $routeparameter['r'] = 0;
       $routeparameter['division'] = 0;
       $routeparameter['mode'] = 0;
       $routeparameter['order'] = 0;
       $routeparameter['layout'] = 0;
       		$link = sportsmanagementHelperRoute::getSportsmanagementRoute('results',$routeparameter);

	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_RESULTS'));
	?> <br />
	<?php
	$routeparameter = array();
       $routeparameter['cfg_which_database'] = JFactory::getApplication()->input->getInt('cfg_which_database',0);
       $routeparameter['s'] = JFactory::getApplication()->input->getInt('s',0);
       $routeparameter['p'] = $season->project_slug;
       $routeparameter['type'] = 0;
       $routeparameter['r'] = 0;
       $routeparameter['from'] = 0;
       $routeparameter['to'] = 0;
       $routeparameter['division'] = 0;
       		$link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking',$routeparameter);
	
	echo JHtml::link($link, JText::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TABLES'));
	?> <br />
</div>
	<?php
	}
}
?>