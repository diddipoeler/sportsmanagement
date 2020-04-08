<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionranking
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

?>

<?php
/**
 * Make sure that in case extensions are written for mentioned (common) views,
 * that they are loaded i.s.o. of the template of this view
 */
$templatesToLoad = array('globalviews','predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$this->kmlpath = Uri::root() . 'tmp' . DIRECTORY_SEPARATOR . $this->predictionGame->id . '-prediction.kml';
$this->kmlfile = $this->predictionGame->id . '-prediction.kml';

?>
<div class="<?php echo $this->divclasscontainer;?>" id="defaultpredictionranking">
<?php

echo $this->loadTemplate('predictionheading');
echo $this->loadTemplate('sectionheader');

echo $this->loadTemplate('ranking');

if ($this->config['show_all_user_google_map'])
{
	echo $this->loadTemplate('googlemap');
}

if ($this->config['show_help'])
{
	echo $this->loadTemplate('show_help');
}

echo $this->loadTemplate('jsminfo');
?>
</div>
