<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$componentEnabled || !$canManage) {
    return;
}

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
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
