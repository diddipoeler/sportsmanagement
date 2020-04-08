<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_teamseasons.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

if ($this->config['show_teams_seasons'] == "1")
{
	?>
<table class="fixtures">
	<tr class="sectiontableheader">
		<td><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TITLE');?></td>
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
			title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SHOW_OPTIONS'); ?>"><?php echo $season->projectname; ?>
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

		if ((@is_null($picture))
			|| (strpos($picture, "/com_sportsmanagement/images/placeholders/placeholder_450.png"))
		)
			{
			$picture = sportsmanagementHelper::getDefaultPlaceholder("team");
		}

		$picture_descr = Text::_("COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE") . " " . $this->team->name . " (" . $season->projectname . ")";
		echo HTMLHelper::image($picture, $picture_descr, array("title" => $picture_descr));
	}
	?> <br />
	<?php
	$routeparameter = array();
	   $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
	   $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
	   $routeparameter['p'] = $season->project_slug;
	   $routeparameter['tid'] = $season->team_slug;
	   $routeparameter['ptid'] = 0;
			   $link = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);

	echo HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_PLAYERS'));
	?> <br />
	<?php
	$routeparameter = array();
	   $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
	   $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
	   $routeparameter['p'] = $season->project_slug;
	   $routeparameter['r'] = 0;
	   $routeparameter['division'] = 0;
	   $routeparameter['mode'] = 0;
	   $routeparameter['order'] = 0;
	   $routeparameter['layout'] = 0;
			   $link = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

	echo HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_RESULTS'));
	?> <br />
	<?php
	$routeparameter = array();
	   $routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
	   $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
	   $routeparameter['p'] = $season->project_slug;
	   $routeparameter['type'] = 0;
	   $routeparameter['r'] = 0;
	   $routeparameter['from'] = 0;
	   $routeparameter['to'] = 0;
	   $routeparameter['division'] = 0;
			   $link = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

	echo HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON_TABLES'));
	?> <br />
	  </div>
	<?php
	}
}
