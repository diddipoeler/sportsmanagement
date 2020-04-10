<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
/**
 * kml file laden
 */
if ($this->mapconfig['map_kmlfile'])
{
	$this->kmlpath = Uri::root() . 'tmp' . DIRECTORY_SEPARATOR . $this->club->id . '-club.kml';
	$this->kmlfile = $this->club->id . '-club.kml';
}

$params = ComponentHelper::getParams('com_sportsmanagement');

if (version_compare(JSM_JVERSION, '4', 'eq') || $params->get('use_jsmgrid'))
{
	$container = 'container';
}
else
{
	$container = "container-fluid";
}
?>
<div class="<?php echo $container ?>" id="clubinfo">
	<?php
	if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
	{
		echo $this->loadTemplate('debug');
	}
	?>

	<?PHP
	echo $this->loadTemplate('projectheading');
	?>

	<?PHP
	if ($this->config['show_sectionheader'])
	{
		?>

		<?PHP
		echo $this->loadTemplate('sectionheader');
		?>

		<?PHP
	}
	?>
    <!-- <div class="row"> -->
	<?PHP
	echo $this->loadTemplate('clubinfo');
	?>
    <!-- </div> -->
	<?PHP
	/**
	 * diddipoeler
	 * aufbau der templates als array
	 */
	$this->output = array();

	if ($this->config['show_extra_fields'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TABS_EXTRA_FIELDS'] = 'extrafields';
	}

	if ($this->config['show_extended'])
	{
		$this->output['COM_SPORTSMANAGEMENT_TABS_EXTENDED'] = 'extended';
	}

	if ($this->club->latitude && $this->club->longitude)
	{
		if ($this->config['show_maps'])
		{
			if ($this->club->latitude != '0.00000000' && $this->club->longitude != '0.00000000')
			{
				$this->output['COM_SPORTSMANAGEMENT_GMAP_DIRECTIONS'] = 'googlemap';
			}
		}
	}

	if ($this->config['show_teams_of_club'])
	{
		$this->output['COM_SPORTSMANAGEMENT_CLUBINFO_TEAMS'] = 'teams';
	}

	if ($this->config['show_club_rssfeed'])
	{
		if ($this->rssfeeditems)
		{
			if (version_compare(JSM_JVERSION, '4', 'eq'))
			{
				$this->output['COM_SPORTSMANAGEMENT_CLUBINFO_RSSFEED'] = 'rssfeed_4';
			}
			else
			{
				$this->output['COM_SPORTSMANAGEMENT_CLUBINFO_RSSFEED'] = 'rssfeed';
			}
		}
	}

	/**
	 * je nach einstellung der templates im backend, wird das template
	 * aus dem verzeichnis globalviews geladen.
	 * hat den vorteil, das weniger programmcode erzeugt wird.
	 * no_tabs
	 * show_tabs
	 * show_slider
	 */
	$template = isset($this->config['show_clubinfo_tabs']) ? $this->config['show_clubinfo_tabs'] : 'no_tabs';
	echo $this->loadTemplate($template);

	echo $this->loadTemplate('jsminfo');
	?>

    <!-- ende clubinfo -->
</div>

