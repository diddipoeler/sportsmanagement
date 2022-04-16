<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage hitlist
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
	HTMLHelper::_('behavior.keepalive');
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
	HTMLHelper::_('behavior.tooltip');
}
HTMLHelper::_('behavior.framework');
HTMLHelper::_('behavior.modal');

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (version_compare(JSM_JVERSION, '4', 'eq'))
{
	$uri = Uri::getInstance();
}
else
{
	$uri = Factory::getURI();
}
?>
<script language="javascript" type="text/javascript">
    function tableOrdering(order, dir, task) {
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    }

    function searchPerson(val) {
        var s = document.getElementById("filter_search");
        s.value = val;
        Joomla.submitform('', this.form)
    }
</script>
<div class="row-fluid">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($uri->toString()); ?>" method="post">
		<?php
		echo $this->loadTemplate('items');
		echo $this->loadTemplate('jsminfo');
		?>

    </form>
</div>

