<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.keepalive');

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

switch ($this->project->sport_type_name)
{
case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
echo $this->loadTemplate('projectheading');
echo $this->loadTemplate('sectionheader');
echo $this->loadTemplate('result');
echo $this->loadTemplate('details');
echo $this->loadTemplate('sporttype_tennis');
break;
default:
$hasMatchPlayerStats = false;
$hasMatchStaffStats  = false;

if (!empty($this->matchplayerpositions))
{
	$hasMatchPlayerStats = false;
	$hasMatchStaffStats  = false;

	foreach ($this->matchplayerpositions as $pos)
	{
		if (isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id]) > 0)
		{
			foreach ($this->stats[$pos->position_id] as $stat)
			{
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
				{
					$hasMatchPlayerStats = true;
					break;
				}
			}
		}
	}


	foreach ($this->matchstaffpositions as $pos)
	{
		if (isset($this->stats[$pos->position_id]) && count($this->stats[$pos->position_id]) > 0)
		{
			foreach ($this->stats[$pos->position_id] as $stat)
			{
				if ($stat->showInSingleMatchReports() && $stat->showInMatchReport())
				{
					$hasMatchStaffStats = true;
				}
			}
		}
	}
}


?>

<div class="<?php echo $this->divclasscontainer; ?>" id="matchreport">

	<?php

	if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
	{
		echo $this->loadTemplate('debug');
	}

	echo $this->loadTemplate('projectheading');

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	if ($this->config['show_result'])
	{
		echo $this->loadTemplate('result');
	}

	if (!empty($this->matchevents))
	{
	}

	// ################################################################
	// diddipoeler
	// aufbau der templates
	$output = array();

	if ($this->config['show_details'])
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_DETAILS'] = 'details';
	}

	if ($this->config['show_youtube'])
	{
		$output['COM_SPORTSMANAGEMENT_TABS_YOUTUBE'] = 'youtube';
	}

	if ($this->config['show_extended'] && $this->extended)
	{
		$output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}

	if ($this->config['show_roster'])
	{
		if ($this->matchplayerpositions)
		{
			$output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_PLAYER'] = $this->config['show_roster_card'];
		}


		if ($this->matchstaffpositions)
		{
			$output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE_UP_STAFF'] = $this->config['show_staff_card'];
		}


		if ($this->matchplayerpositions)
		{
			$output['COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'] = 'subst';
		}
	}

	if ($this->config['show_roster_playground'])
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_PLAYGROUND'] = 'rosterplayground';
	}

	if (!empty($this->matchevents))
	{
		if ($this->config['show_events'])
		{
			switch ($this->config['use_tabs_events'])
			{
				case 0:
					// No tabs
					if (!empty($this->eventtypes))
					{
						$output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events';
					}
					break;
				case 1:
					// Tabs
					if (!empty($this->eventtypes))
					{
						// Echo $this->loadTemplate('events_tabs');
						$output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events_tabs';
					}
					break;
				case 2:
					// Table/Ticker layout
					$output['COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'] = 'events_ticker';
					break;
			}
		}
	}

	if ($this->config['show_stats'] && ($hasMatchPlayerStats || $hasMatchStaffStats))
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_STATISTICS'] = 'stats';
	}

	if ($this->config['show_summary'] && $this->match->summary)
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_SUMMARY'] = 'summary';
	}

	if ($this->config['show_comments'])
	{
		$output['CCOM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTS'] = 'comments';
	}

	if ($this->config['show_article'])
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_ARTICLE'] = 'article';
	}

	if ($this->config['show_commentary'] && $this->matchcommentary)
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_COMMENTARY'] = 'commentary';
	}

	if ($this->config['show_pictures'] && isset($this->matchimages))
	{
		$output['COM_SPORTSMANAGEMENT_MATCHREPORT_MATCH_PICTURES'] = 'pictures';
	}
	break;
	}

	$this->output = $output;

	if ($this->config['show_result_tabs'] == "show_slider")
	{
		echo $this->loadTemplate($this->config['show_result_tabs']);
	}

	if ($this->config['show_result_tabs'] == "show_tabs")
	{
		echo $this->loadTemplate($this->config['show_result_tabs']);
	}

	if ($this->config['show_result_tabs'] == "no_tabs")
	{
		foreach ($output as $key => $value)
		{
			echo $this->loadTemplate($value);
		}
	}

	echo $this->loadTemplate('jsminfo');
	?>

</div>
