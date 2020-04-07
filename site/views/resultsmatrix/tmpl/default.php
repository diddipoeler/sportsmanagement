<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage resultsmatrix
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews', 'results', 'matrix');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer;?>" id="resultsmatrix">
<?php
if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
{
	echo $this->loadTemplate('debug');
}

echo $this->loadTemplate('projectheading');
echo $this->loadTemplate('selectround');

/**
 * diddipoeler
 * aufbau der templates
 */
$this->output = array();

$this->output['COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS'] = 'results';

if (isset($this->divisions) && count($this->divisions) > 1)
{
	$this->output['COM_SPORTSMANAGEMENT_MATRIX'] = 'matrix_division';
}
else
{
	$this->output['COM_SPORTSMANAGEMENT_MATRIX'] = 'matrix';
}

echo $this->loadTemplate('show_tabs');

if ($this->params->get('show_map', 0))
{
	echo $this->loadTemplate('googlemap');
}

echo $this->loadTemplate('jsminfo');
?>
</div>
