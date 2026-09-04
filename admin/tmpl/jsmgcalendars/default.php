<?php
/**
 * Native Joomla 5/6 administrator Google calendars list layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$this->getDocument()->getWebAssetManager()->useScript('multiselect');
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jsmgcalendars'); ?>"
      method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="boxchecked" value="0">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
