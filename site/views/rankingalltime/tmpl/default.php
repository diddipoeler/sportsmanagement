<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_tips');
echo $this->loadTemplate('jsm_notes');

?>
<div class="<?php echo $this->divclasscontainer; ?>" id="defaultrankingalltime">
<?php
echo $this->loadTemplate('projectheading');
echo $this->loadTemplate('ranking');
echo $this->loadTemplate('jsminfo');
?>
</div>
