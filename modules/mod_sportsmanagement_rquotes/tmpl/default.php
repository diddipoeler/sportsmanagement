<?php
/**
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (($source ?? 'db') === 'text') {
    require __DIR__ . '/textfile.php';
    return;
}

if (($source ?? 'db') !== 'db') {
    echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION');
    return;
}

$quoteStyle = $quoteStyle ?? (string) $params->get('template', 'default');
if ($quoteStyle !== 'default' && in_array($quoteStyle, ['bold', 'italic', 'style', 'sticker'], true)) {
    require __DIR__ . '/' . $quoteStyle . '.php';
    return;
}

if (!$list) {
    echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_NUMBER_RANDOM_QUOTES_ERROR');
    return;
}
?>
<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>"
     id="mod-sportsmanagement-rquotes-<?php echo (int) $module->id; ?>">
    <?php foreach ($list as $rquote) : ?>
        <?php require __DIR__ . '/_rquote.php'; ?>
    <?php endforeach; ?>
</div>
