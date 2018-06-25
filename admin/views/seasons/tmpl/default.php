<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage seasons
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
// welche joomla version
if (version_compare(JVERSION, '3.0.0', 'ge')) {
    JHtml::_('behavior.framework', true);
} else {
    JHtml::_('behavior.mootools');
}
$modalheight = JComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_height', 600);
$modalwidth = JComponentHelper::getParams($this->jinput->getCmd('option'))->get('modal_popup_width', 900);
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
echo $this->loadTemplate('joomla_version');
?>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
    <?php echo JHtml::_('form.token') . "\n"; ?>
<?php echo $this->table_data_div; ?>
</form>
<?PHP
echo $this->loadTemplate('footer');
?>   
