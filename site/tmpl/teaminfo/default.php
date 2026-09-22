<?php
/**
 * Native Joomla 5/6 team information layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

?>
<div class="<?php echo $this->divclasscontainer; ?>" id="teaminfo">
	<?php
	if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
	{
		echo $this->loadTemplate('debug');
	}


	if ($this->config['show_projectheader'])
	{
		echo $this->loadTemplate('projectheading');
	}

	if ($this->config['show_sectionheader'])
	{
		echo $this->loadTemplate('sectionheader');
	}

	/**
	 * diddipoeler
	 * aufbau der templates
	 */
	$this->output = array();

	if ($this->config['show_teaminfo'])
	{
		echo $this->loadTemplate('teaminfo');
	}

	if ($this->config['show_description'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TEAMINFO_TEAMINFORMATION'] = 'description';
	}

	if ($this->config['show_extra_fields'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
	}

	if ($this->config['show_extended'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}

	if ($this->config['show_history'])
	{
		//$this->output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY'] = 'history';
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_SIMPLE_LEAGUE'] = 'simple_league';
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_TOURNAMENT'] = 'tournament';
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_FRIENDLY_MATCHES'] = 'friendly_matches';
        $this->output['COM_SPORTSMANAGEMENT_TEAMINFO_DIVISIONS_LEAGUE'] = 'divisions_league';
	}

	if ($this->config['show_history_leagues'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_PER_LEAGUE_SUMMARY'] = 'history_leagues';
	}

	if ($this->config['show_training'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TEAMINFO_TRAINING'] = 'training';
	}

	$template = isset($this->config['show_teaminfo_tabs']) ? $this->config['show_teaminfo_tabs'] : 'no_tabs';
	echo $this->loadTemplate($template);
	echo $this->loadTemplate('jsminfo');
	?>

</div>
