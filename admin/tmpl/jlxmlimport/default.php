<?php
/** Joomla 5/6 compatibility entry page for the XML import workflow. */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="card">
    <div class="card-body">
        <h2 class="h5"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3'); ?></h2>
        <p><?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT'); ?></p>
        <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=jlxmlimports&layout=default', false); ?>">
            <?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT'); ?>
        </a>
    </div>
</div>
