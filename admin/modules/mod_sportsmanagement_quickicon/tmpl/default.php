<?php
/**
 * SportsManagement Joomla 5/6 file metadata.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$componentEnabled || !$canManage) {
    return;
}

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8',
    false
);
?>
<nav class="quick-icons px-3 pb-3" aria-label="<?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_LABEL')); ?>">
    <ul class="nav flex-wrap">
        <?php foreach ($links as $link) : ?>
            <li class="quickicon quickicon-single">
                <a href="<?php echo $escape($link['url']); ?>" title="<?php echo $escape($link['title']); ?>">
                    <div class="quickicon-icon">
                        <img src="<?php echo $escape($link['icon']); ?>" alt="" loading="lazy">
                    </div>
                    <div class="quickicon-name d-flex align-items-end"><?php echo $escape($link['label']); ?></div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
