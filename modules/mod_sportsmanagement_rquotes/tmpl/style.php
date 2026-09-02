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

if (!$list) {
    echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_NUMBER_RANDOM_QUOTES_ERROR');
    return;
}
?>
<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php foreach ($list as $rquote) : ?>
        <div class="mod_rquote_style">
            <?php require __DIR__ . '/_rquote.php'; ?>
        </div>
    <?php endforeach; ?>
</div>
