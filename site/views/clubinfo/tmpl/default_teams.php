<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default_teams.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

?>
<div class="<?php echo $this->divclassrow; ?>" id="default_teams" itemscope itemtype="http://schema.org/SportsTeam">
	
		<?php
		$this->notes = array();
		$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS');
		echo $this->loadTemplate('jsm_notes');

		$params          = array();
		$params['width'] = "30";

		foreach ($this->teams as $team)
		{
			if ($team->team_name && property_exists($team,'ptid'))
			{
				$routeparameter                       = array();
				$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
				$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
				$routeparameter['p']                  = $team->pid;
				$routeparameter['tid']                = $team->team_slug;
				$routeparameter['ptid']               = $team->ptid;
				$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);
				?>
				<div class="row">
<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
					<?php

					if ($team->team_shortcut)
					{
						// Echo "(" . $team->team_shortcut . ")";
						if ($this->config['show_teams_trikot_of_club'])
						{
							if ($this->config['show_teams_shortcut_of_club'])
							{
								echo HTMLHelper::link($link, HTMLHelper::image($team->trikot_home, $team->team_name, $params) . '<span itemprop="name">' . $team->team_name . " (" . $team->team_shortcut . ")" . '</span>');
							}
							else
							{
								echo HTMLHelper::link($link, HTMLHelper::image($team->trikot_home, $team->team_name, $params) . '<span itemprop="name">' . $team->team_name . '</span>');
							}
						}
						else
						{
							if ($this->config['show_teams_shortcut_of_club'])
							{
								echo HTMLHelper::link($link, '<span itemprop="name">' . $team->team_name . " (" . $team->team_shortcut . ")" . '</span>');
							}
							else
							{
								echo HTMLHelper::link($link, '<span itemprop="name">' . $team->team_name . '</span>');
							}
						}
					}
					else
					{
						if ($this->config['show_teams_trikot_of_club'])
						{
							echo HTMLHelper::link($link, HTMLHelper::image($team->trikot_home, $team->team_name, $params) . $team->team_name);
						}
						else
						{
							echo HTMLHelper::link($link, $team->team_name);
						}
					}

					echo "&nbsp;";
					?>
                      </div>
<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
				<?php
				if ($team->team_description && $this->config['show_teams_description_of_club'])
				{
					echo $team->team_description;
				}
				else
				{
					echo "&nbsp;";
				}
?>
                      </div>
  <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
  <?php
				if ($this->config['show_teams_picture'])
				{
					if (empty($team->project_team_picture))
					{
						$team->project_team_picture = sportsmanagementHelper::getDefaultPlaceholder("team");
					}

					echo sportsmanagementHelperHtml::getBootstrapModalImage(
						'clubteam' . $team->id,
						$team->project_team_picture,
						$team->team_name,
						$this->config['team_picture_width'],
						'',
						$this->modalwidth,
						$this->modalheight,
						$this->overallconfig['use_jquery_modal'],
						'itemprop',
						'image'
					);
				}
				?>
                  </div>
				</div>
				<?PHP
			}
		}
		?>
	<!-- </div> -->
</div>
