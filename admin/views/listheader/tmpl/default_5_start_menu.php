<?php
/**
 * SportsManagement Joomla 5/6 migration.
 *
 * @version    5.6.0 sportsmanagement
 * @author     diddipoeler <diddipoeler@gmx.de>
 * @copyright  Copyright (C) diddipoeler. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
?>
<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
    <div class="card mb-3">
        <nav class="quick-icons px-3 py-3" aria-label="SportsManagement Schnellstartlinks">
            <ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(160px,1fr));">
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_MENU') ?>"
                       href="index.php?option=com_sportsmanagement">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/transparent_schrift_48.png"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_MENU') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_MENU') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS') ?>"
                       href="index.php?option=com_sportsmanagement&view=extensions">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS') ?>"
                       href="index.php?option=com_sportsmanagement&view=specialextensions">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/extensions.png"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS') ?>"
                       href="index.php?option=com_sportsmanagement&view=projects">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/projekte.png"
                                 style="background:white;"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS') ?>"
                       href="index.php?option=com_sportsmanagement&view=predictiongames">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/tippspiele.png"
                                 style="background:white;"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS') ?>"
                       href="index.php?option=com_sportsmanagement&view=currentseasons">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/aktuellesaison.png"
                                 style="background:white;"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS') ?>
                        </div>
                    </a>
                </li>
                <li class="quickicon quickicon-single">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR') ?>"
                       href="index.php?option=com_sportsmanagement&view=jsmgcalendars">
                        <div class="quickicon-icon">
                            <img src="<?php echo Uri::base(false) ?>/components/com_sportsmanagement/assets/icons/google-calendar-48-icon.png"
                                 alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR') ?>">
                        </div>
                        <div class="quickicon-name d-flex align-items-end">
                            <?php echo Text::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR') ?>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
