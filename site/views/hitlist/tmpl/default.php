<?php 
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage hitlist
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.framework');
HTMLHelper::_('behavior.modal');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (version_compare(JSM_JVERSION, '4', 'eq')) {
    $uri = Uri::getInstance();   
} else {
    $uri = Factory::getURI();
}
?>
<script language="javascript" type="text/javascript">
function tableOrdering( order, dir, task )
{
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit( task );
}
function searchPerson(val)
    {
        var s= document.getElementById("filter_search");
        s.value = val;
        Joomla.submitform('', this.form)
    }
</script>
<div class="row-fluid">
<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($uri->toString());?>" method="post">
<?php 
echo $this->loadTemplate('items');
echo $this->loadTemplate('jsminfo');
?>

</form>
</div>

