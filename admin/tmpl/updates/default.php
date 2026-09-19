<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Updates layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
?>
<form action="<?php echo $this->escape($this->request_url); ?>"
      method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <input type="hidden" name="view" value="updates">
    <input type="hidden" name="task" value="">
    <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
