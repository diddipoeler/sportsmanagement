<?php
/**
 * Native Joomla 5/6 statistics layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

if (!isset($this->project))
{
	Log::add(Text::_('Error: ProjectID was not submitted in URL or project was not found in database!'));
}
else
{
	?>
    <div class="<?php echo $this->divclasscontainer; ?>" id="defaultstats">
		<?php
		echo $this->loadTemplate('projectheading');

		if ($this->config['show_sectionheader'])
		{
			echo $this->loadTemplate('sectionheader');
		}

		if ($this->config['show_general_stats'])
		{
			echo $this->loadTemplate('stats');
		}

		if ($this->config['show_goals_stats'])
		{
			echo $this->loadTemplate('goals_stats');
		}

		if ($this->config['show_attendance_stats'])
		{
			echo $this->loadTemplate('attendance_stats');
		}

		if ($this->config['show_goals_stats_flash'])
		{
			echo $this->loadTemplate('flashchart');
		}

		if ($this->config['show_attendance_ranking'])
		{
			echo $this->loadTemplate('ranking');
		}

		echo $this->loadTemplate('jsminfo');
		?>
    </div>
	<?php
}
