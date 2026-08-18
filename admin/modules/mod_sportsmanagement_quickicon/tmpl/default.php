<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$componentEnabled) {
    return;
}
?>
<nav class="quick-icons px-3 pb-3" aria-label="<?php echo htmlspecialchars(Text::_('MOD_SPORTSMANAGEMENT_QUICKICON_LABEL'), ENT_QUOTES, 'UTF-8'); ?>">
    <ul class="nav flex-wrap">
        <?php foreach ($links as $link) : ?>
            <li class="quickicon quickicon-single">
                <a href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="quickicon-icon"><img src="<?php echo htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8'); ?>" alt="" /></div>
                    <div class="quickicon-name d-flex align-items-end"><?php echo htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
