<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
?>
<div class="alert alert-info">
    <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBTOOLS_TITLE'); ?>
</div>
<p>
    <a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_sportsmanagement&view=databasetools'); ?>">
        <?php echo Text::_('JTOOLBAR_BACK'); ?>
    </a>
</p>
<?php echo HTMLHelper::_('form.token'); ?>
