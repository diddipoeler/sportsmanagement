<?php
/** Joomla 5/6 SportsManagement installation helper completion step. */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="alert alert-info" role="status">
    <strong><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NOTE'); ?></strong>
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_C'); ?>
</div>

<div class="card">
    <div class="card-body">
        <p class="mb-3">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_C'); ?>
        </p>
        <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_sportsmanagement', false); ?>">
            <span class="icon-home" aria-hidden="true"></span>
            <?php echo Text::_('JTOOLBAR_CLOSE'); ?>
        </a>
    </div>
</div>
