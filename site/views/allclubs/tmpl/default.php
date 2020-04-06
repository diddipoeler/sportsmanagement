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
 * @subpackage allclubs
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.framework');
HTMLHelper::_('behavior.modal');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<script language="javascript" type="text/javascript">
    function tableOrdering(order, dir, task)
    {
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit(task);
    }
    function searchPerson(val)
    {
        var s = document.getElementById("filter_search");
        s.value = val;
        Joomla.submitform('', this.form)
    }
</script>
<div class="container-fluid">
<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>" method="post">
<fieldset class="filters">
<legend class="hidelabeltxt">
<?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?>
</legend>
<div class="filter-search">

<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />
<button type="submit" class="btn" title=""><i class="icon-search"><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></i></button>
<button type="button" class="btn" title="" onclick="document.id('filter_search').value = '';this.form.submit();"><i class="icon-remove"><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></i></button>

<td nowrap='nowrap' align='right'><?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?></td>
<td align="center" colspan="4">
<?php
$startRange = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('character_filter_start_hex', '0');
$endRange = ComponentHelper::getParams(Factory::getApplication()->input->getCmd('option'))->get('character_filter_end_hex', '0');
for ($i = $startRange; $i <= $endRange; $i++) {
    printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;", '&#' . $i . ';', '&#' . $i . ';');
}
?>
</td>
</div>
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="limitstart" value="" />
<div class="display-limit">
<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>;
<?php echo $this->pagination->getLimitBox(); ?>
</div>
</fieldset>
<?php
echo $this->loadTemplate('items');
echo $this->loadTemplate('jsminfo');
?>
</form>
</div>
