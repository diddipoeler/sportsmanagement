<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matchreport
 * @file       default_events_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

?>
<!-- START of match events -->

<h2>
<?php
echo Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS');
?>
</h2>	
<?php
if ($this->config['show_timeline'] && !$this->config['show_timeline_under_results'])
{
	echo $this->loadTemplate('timeline');
}

if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
{
	$visible = 'text';
}
else
{
	$visible = 'hidden';
}

/**
*
 * joomla 3 anfang ------------------------------------------------------------------------
*/
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	// Joomla! 3.0 code here
	$idxTab = 0;

?>
<!-- This is a list with tabs names. anfang -->
<div class="panel with-nav-tabs panel-default">
<!-- Tabs-heading anfang -->
<div class="panel-heading">
<!-- Tabs-Navs anfang -->
<ul class="nav nav-tabs" role="tablist">
<?PHP
foreach ($this->eventtypes AS $event)
	{
	$active = ($idxTab == 0) ? 'in active' : '';

	$pic_tab = $event->icon;

	if ($pic_tab == '/events/event.gif')
		{
		$text_bild = '';
		$text = Text::_($event->name);
	}
	else
		{
		$imgTitle = Text::_($event->name);
		$imgTitle2 = array(' title' => $imgTitle, ' alt' => $imgTitle, ' style' => 'max-height:40px;');
		$text_bild = HTMLHelper::image(Uri::root() . $pic_tab, $imgTitle, $imgTitle2);
		$text = Text::_($event->name);
	}


?>
<li role="presentation" class="<?PHP echo $active; ?>"><a href="#event<?PHP echo $event->id; ?>" role="tab" data-toggle="tab"><?PHP echo $text_bild . $text; ?></a>
</li>

<?PHP
$idxTab++;
}
?>
<!-- Tabs-Navs ende -->
</ul>
<!-- Tabs-heading ende -->
</div>


<!-- Tab-Inhalte anfang-->
<div class="panel-body">
<!-- Tab-content anfang-->
<div class="tab-content">
<?PHP
$idxTab = 0;

foreach ($this->eventtypes AS $event)
	{
	$active = ($idxTab == 0) ? 'in active' : '';
	$text = Text::_($event->name);

?>
<!-- Tab-event anfang-->
<div role="tabpanel" class="tab-pane fade <?PHP echo $active; ?>" id="event<?PHP echo $event->id; ?>">
<?PHP
$idxTab++;

foreach ($this->matchevents AS $me)
		{
	if ($me->event_type_id == $event->id
		&& ( $me->ptid == $this->match->projectteam1_id || $me->ptid == $this->match->projectteam2_id )
	)
			{
		if ($this->config['show_event_minute'] == 1 && $me->event_time > 0)
				{
			$prefix = str_pad($me->event_time, 2, '0', STR_PAD_LEFT) . "' ";
		}
		else
				{
					$prefix = null;
		}

							  $match_player = sportsmanagementHelper::formatName($prefix, $me->firstname1, $me->nickname1, $me->lastname1, $this->config["name_format"]);

		if ($this->config['event_link_player'] == 1 && $me->playerid != 0)
				{
					$routeparameter = array();
					$routeparameter['cfg_which_database'] = Factory::getApplication()->input->getInt('cfg_which_database', 0);
					$routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
					$routeparameter['p'] = $this->project->slug;
					$routeparameter['tid'] = $me->team_id;
					$routeparameter['pid'] = $me->playerid;
					$player_link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);
					$match_player = HTMLHelper::link($player_link, $match_player);
		}

							echo $match_player;

							// Only show event sum and match notice when set to on in template cofig
							$sum_notice = "";

		if ($this->config['show_event_sum'] == 1 || $this->config['show_event_notice'] == 1)
				{
			if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) || ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
					{
				$sum_notice .= ' (';

				if ($this->config['show_event_sum'] == 1 && $me->event_sum > 0)
						{
					  $sum_notice .= $me->event_sum;
				}


				if (($this->config['show_event_sum'] == 1 && $me->event_sum > 0) && ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0))
						{
					  $sum_notice .= ' | ';
				}


				if ($this->config['show_event_notice'] == 1 && strlen($me->notice) > 0)
						{
					  $sum_notice .= $me->notice;
				}

				$sum_notice .= ')';
			}
		}

							echo $sum_notice;

							echo '<br>';
	}
}
?>
<!-- Tab-event ende-->
</div>	

<?php
}
?>

<!-- Tab-content ende-->
</div>
<!-- Tab-Inhalte ende-->
</div>
<!-- This is a list with tabs names. ende -->
</div>

<?PHP
}

/**
*
 * joomla 3 ende ------------------------------------------------------------------------
*/

