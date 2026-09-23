<?php
/**
 * Native Joomla 5/6 administrator database-tools form layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
?>
<form action="<?php echo Route::_('index.php?option=com_sportsmanagement&view=databasetools'); ?>" method="post" id="adminForm" name="adminForm">
    <?php echo $this->loadTemplate('data'); ?>
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
