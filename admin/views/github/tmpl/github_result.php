<?php
/**
 * Joomla 5/6 GitHub result layout for the SportsManagement administrator.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

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
