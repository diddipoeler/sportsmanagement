<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="alert alert-info">
    <p><?php echo Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_01'); ?></p>
    <p><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_02', '<a href="' . Route::_('index.php?option=com_users&view=login') . '"><b><i>', '</i></b></a>'); ?></p>
    <p><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_DENY_INFO_03', '<a href="' . Route::_('index.php?option=com_users&view=registration') . '"><b><i>', '</i></b></a>'); ?></p>
</div>
