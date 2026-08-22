<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="container-popup">
    <div class="alert alert-info">
        <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_TITLE'); ?>
    </div>
    <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=github&tmpl=component'); ?>">
        <?php echo Text::_('JBACK'); ?>
    </a>
    <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=close&tmpl=component'); ?>">
        <?php echo Text::_('JCLOSE'); ?>
    </a>
</div>
