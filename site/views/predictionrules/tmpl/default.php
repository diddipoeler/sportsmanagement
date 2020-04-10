<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionrules
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews', 'predictionheading');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="row-fluid">
	<?php
	echo $this->loadTemplate('predictionheading');
	echo $this->loadTemplate('sectionheader');
	echo $this->loadTemplate('info');
	echo $this->loadTemplate('jsminfo');
	?>

</div>
