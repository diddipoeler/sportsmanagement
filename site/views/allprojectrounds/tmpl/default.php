<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojectrounds
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="container-fluid">
<!-- projectheading -->
<?php echo $this->loadTemplate('projectheading'); ?>
<?php
if ($this->config['show_sectionheader'])
{
	echo $this->loadTemplate('sectionheader');
}
?>
<?php
echo $this->loadTemplate('results_all');
echo $this->loadTemplate('jsminfo');
?>
</div>
