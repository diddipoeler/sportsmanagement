<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage stats
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
	/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
	$templatesToLoad = array('globalviews');
	sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
	?>
<div class="<?php echo $this->divclasscontainer;?>" id="defaultstats">
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
