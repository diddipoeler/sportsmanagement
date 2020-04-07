<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage clubplan
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer;?>" id="clubplan">
<?php
echo $this->loadTemplate('projectheading');

if (($this->config['show_sectionheader']) == 1 && $this->club)
{
	echo $this->loadTemplate('sectionheader');
}

echo $this->loadTemplate('datenav');

switch ($this->config['type_matches'])
{
	case 0 : // All matches
		if (!empty($this->allmatches))
		{
			$tm = count($this->allmatches);
			echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			$this->matches = $this->allmatches;
			echo $this->loadTemplate('matches');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES') . "</h4><br/>";
		}
	break;

	case 1 : // Home matches
		if (!empty($this->homematches))
		{
			$tm = count($this->homematches);
			echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			$this->matches = $this->homematches;
			echo $this->loadTemplate('matches');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES') . "</h4><br/>";
		}
	break;

	case 2 : // Away matches
		if (!empty($this->awaymatches))
		{
			$tm = count($this->awaymatches);
			echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			$this->matches = $this->awaymatches;
			echo $this->loadTemplate('matches');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES') . "</h4><br/>";
		}
	break;

	case 4 : // Matches sorted by date
		if (!empty($this->allmatches))
		{
			$tm = count($this->allmatches);
			echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			$this->matches = $this->allmatches;
			echo $this->loadTemplate('matches_sorted_by_date');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES') . "</h4><br/>";
		}
	break;

	default : // Home+Away matches
		if (!empty($this->homematches))
		{
			$tm = count($this->homematches);

			if (count($this->awaymatches) == 0)
			{
				echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			}
			else
			{
				echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_HOME_MATCHES') . "</h4>";
			}

			$this->matches = $this->homematches;
			echo $this->loadTemplate('matches');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES') . "</h4><br/>";
		}


		if (!empty($this->awaymatches))
		{
			$tm = count($this->awaymatches);

			if (count($this->homematches) == 0)
			{
				echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . "</h4>";
			}
			else
			{
				echo "<h4>" . $tm . " " . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_AWAY_MATCHES') . "</h4>";
			}

			$this->matches = $this->awaymatches;
			echo $this->loadTemplate('matches');// Or use matches_sbd (sort by date)
		}
		else
		{
			echo "<h4>" . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES') . "</h4><br/>";
		}
	break;
}

echo $this->loadTemplate('jsminfo');
?>
</div>
