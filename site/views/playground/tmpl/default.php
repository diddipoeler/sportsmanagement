<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       deafault.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage playground
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

/**
 *  Make sure that in case extensions are written for mentioned (common) views,
 *  that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
/**
 * kml file laden
 */
if ($this->mapconfig['map_kmlfile'])
{
	$this->kmlpath = Uri::root() . 'tmp' . DIRECTORY_SEPARATOR . $this->playground->id . '-playground.kml';
	$this->kmlfile = $this->playground->id . '-playground.kml';
}
?>

<div class="<?php echo $this->divclasscontainer;?>" id="playground">
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

if ($this->config['show_playground'])
{
	echo $this->loadTemplate('playground');
}

if ($this->config['show_extended'])
{
	echo $this->loadTemplate('extended');
}

if ($this->config['show_picture'])
{
	echo $this->loadTemplate('picture');
}

if ($this->playground->latitude && $this->playground->longitude)
{
	if ($this->config['show_maps'])
	{
		echo $this->loadTemplate('googlemap');
	}
}

if ($this->config['show_description'])
{
	echo $this->loadTemplate('description');
}

if ($this->config['show_teams'])
{
	echo $this->loadTemplate('teams');
}

if ($this->config['show_matches'])
{
	echo $this->loadTemplate('matches');
}

if ($this->config['show_played_matches'])
{
	echo $this->loadTemplate('played_matches');
}

echo $this->loadTemplate('jsminfo');
?>
</div>
