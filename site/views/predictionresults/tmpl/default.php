<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionresults
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('globalviews', 'predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="row-fluid">
	<?php
	echo $this->loadTemplate('predictionheading');
	echo $this->loadTemplate('sectionheader');
	echo $this->loadTemplate('results');

	if ($this->config['show_help'])
	{
		echo $this->loadTemplate('show_help');
	}

	echo $this->loadTemplate('jsminfo');
	?>

</div>
