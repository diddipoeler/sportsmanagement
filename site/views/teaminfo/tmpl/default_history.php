<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_history.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

?>


<h4>

	<?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY'); ?>

</h4>

<table class="<?PHP echo $this->config['table_class']; ?>">
    <thead>
    <tr class="sectiontableheader">
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_SEASON'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PLAYERS_PICTURE'); ?></th>
		<?php
		if ($this->project->project_type == 'DIVISIONS_LEAGUE')
		{
			?>
            <th class="" nowrap=""
                style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_DIVISION'); ?></th>
			<?php
		}
		?>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_RANK'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_POINTS'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?></th>
        <th class="" nowrap=""
            style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS'); ?></th>

		<?PHP
		if ($this->config['show_teams_roster_mean_age'])
		{
			?>
            <th class="" nowrap=""
                style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_PLAYERS_MEAN_AGE'); ?></th>
			<?PHP
		}

		if ($this->config['show_teams_roster_market_value'])
		{
			?>
            <th class="" nowrap=""
                style="background:#BDBDBD;"><?php echo Text::_('COM_SPORTSMANAGEMENT_EURO_MARKET_VALUE'); ?></th>
			<?PHP
		}
		?>


    </tr>
    </thead>
	<?php
	$k = 0;

	foreach ($this->seasons as $season)
	{
		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter['p']                  = $season->project_slug;
		$routeparameter['type']               = 0;
		$routeparameter['r']                  = $season->round_slug;
		$routeparameter['from']               = 0;
		$routeparameter['to']                 = 0;
		$routeparameter['division']           = $season->division_slug;
		$ranking_link                         = sportsmanagementHelperRoute::getSportsmanagementRoute('ranking', $routeparameter);

		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter["p"]                  = $season->project_slug;
		$routeparameter['r']                  = $season->round_slug;
		$routeparameter['division']           = $season->division_slug;
		$routeparameter['mode']               = '';
		$routeparameter['order']              = '';
		$routeparameter['layout']             = '';
		$results_link                         = sportsmanagementHelperRoute::getSportsmanagementRoute('results', $routeparameter);

		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter['p']                  = $season->project_slug;
		$routeparameter['tid']                = $this->team->slug;
		$routeparameter['division']           = $season->division_slug;
		$routeparameter['mode']               = 0;
		$routeparameter['ptid']               = $season->ptid;
		$teamplan_link                        = sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeparameter);

		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter['p']                  = $season->project_slug;
		$routeparameter['tid']                = $this->team->slug;
		$teamstats_link                       = sportsmanagementHelperRoute::getSportsmanagementRoute('teamstats', $routeparameter);

		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter['p']                  = $season->project_slug;
		$routeparameter['tid']                = $season->team_slug;
		$routeparameter['ptid']               = $season->ptid;
		$players_link                         = sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeparameter);
		?>
        <tr class="">
            <td><?php echo $season->season; ?></td>
            <td><?php echo JSMCountries::getCountryFlag($season->leaguecountry).''.$season->league; ?></td>
            <td><?php
		if ( $this->config['show_team_hist_picture'] )
		{
				$picture = !$season->season_picture ? sportsmanagementHelper::getDefaultPlaceholder('team') : $season->season_picture;

				echo sportsmanagementHelperHtml::getBootstrapModalImage('teaminfohistory' . $season->ptid . '-' . $season->projectid,
					$picture,
					$this->team->name,
					'50',
					'',
					$this->modalwidth,
					$this->modalheight,
					$this->overallconfig['use_jquery_modal']
				);
	}
else
      {
        echo HTMLHelper::image('media/com_sportsmanagement/jl_images/icon_copyright_2.png', '', 'height="30"');
      }		
				?></td>
			<?php if ($this->project->project_type == 'DIVISIONS_LEAGUE')
			{
				?>
                <td><?php echo $season->division_name; ?></td>
			<?php } ?>
			<?php
			if ($this->config['show_teams_ranking_link'] == 1)
				:
				?>
                <td><?php echo HTMLHelper::link($ranking_link, $season->rank); ?></td>
			<?php else

				:
				?>
                <td><?php echo $season->rank; ?></td>
			<?php endif; ?>
            <td><?php echo $season->games; ?></td>
			<?php
			if ($this->config['show_teams_results_link'] == 1)
				:
				?>
                <td><?php echo HTMLHelper::link($results_link, $season->points); ?></td>
			<?php else

				:
				?>
                <td><?php echo $season->points; ?></td>
			<?php endif; ?>
			<?php
			if ($this->config['show_teams_teamplan_link'] == 1)
				:
				?>
                <td><?php echo HTMLHelper::link($teamplan_link, $season->series); ?></td>
			<?php else

				:
				?>
                <td><?php echo $season->series; ?></td>
			<?php endif; ?>
			<?php
			if ($this->config['show_teams_teamstats_link'] == 1)
				:
				?>
                <td><?php echo HTMLHelper::link($teamstats_link, $season->goals); ?></td>
			<?php else

				:
				?>
                <td><?php echo $season->goals; ?></td>
			<?php endif; ?>
			<?php
			if ($this->config['show_teams_roster_link'] == 1)
				:
				?>
                <td><?php echo HTMLHelper::link($players_link, $season->playercnt); ?></td>
			<?php else

				:
				?>
                <td><?php echo $season->playercnt; ?></td>
			<?php endif; ?>

			<?php if ($this->config['show_teams_roster_mean_age'] == 1)
				:
				?>
                <td align="right"><?php echo HTMLHelper::link($players_link, $season->playermeanage); ?></td>
			<?php else

				:
				?>

			<?php endif; ?>

			<?php if ($this->config['show_teams_roster_market_value'] == 1)
				:
				?>
                <td align="right"><?php echo HTMLHelper::link($players_link, number_format($season->market_value, 0, ",", ".")); ?></td>
			<?php else

				:
				?>

			<?php endif; ?>

        </tr>
		<?php
		$k = 1 - $k;
	}
	?>
</table>
