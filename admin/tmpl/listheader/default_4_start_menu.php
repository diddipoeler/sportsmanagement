<?php
/**
 * Native Joomla 5/6 administrator cPanel start menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$links = [
    ['COM_SPORTSMANAGEMENT_MENU', 'index.php?option=com_sportsmanagement', 'transparent_schrift_48.png', false],
    ['COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS', 'index.php?option=com_sportsmanagement&view=extensions', 'extensions.png', false],
    ['COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS', 'index.php?option=com_sportsmanagement&view=specialextensions', 'extensions.png', false],
    ['COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS', 'index.php?option=com_sportsmanagement&view=projects', 'projekte.png', true],
    ['COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS', 'index.php?option=com_sportsmanagement&view=predictiongames', 'tippspiele.png', true],
    ['COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS', 'index.php?option=com_sportsmanagement&view=currentseasons', 'aktuellesaison.png', true],
    ['COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR', 'index.php?option=com_sportsmanagement&view=jsmgcalendars', 'google-calendar-48-icon.png', false],
];
$iconBase = rtrim(Uri::base(false), '/') . '/components/com_sportsmanagement/assets/icons/';
$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
    <div class="card mb-3">
        <nav class="quick-icons px-3 py-3" aria-label="SportsManagement Schnellstartlinks">
            <ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(160px,1fr));">
                <?php foreach ($links as [$textKey, $href, $icon, $whiteBackground]) : ?>
                    <?php $label = Text::_($textKey); ?>
                    <li class="quickicon quickicon-single">
                        <a title="<?php echo $escape($label); ?>" href="<?php echo $escape($href); ?>">
                            <div class="quickicon-icon">
                                <img src="<?php echo $escape($iconBase . $icon); ?>"
                                     <?php echo $whiteBackground ? 'style="background:white;"' : ''; ?>
                                     alt="<?php echo $escape($label); ?>">
                            </div>
                            <div class="quickicon-name d-flex align-items-end">
                                <?php echo $escape($label); ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>
