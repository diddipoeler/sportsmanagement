<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage rosterpositions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

// Ordering allowed ?
$ordering = ($this->sortColumn == 'obj.ordering');



?>
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm">
  
<?PHP
// Welche joomla version ?
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	echo $this->loadTemplate('joomla3');
}
else
{
	echo $this->loadTemplate('joomla2');
}

echo $this->loadTemplate('data');
?>
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
