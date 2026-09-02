<?php
/**
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$textLine = (string) ($textLine ?? ($rows[$num ?? 0] ?? ''));

if ($textLine === '') {
    echo Text::_('MOD_SPORTSMANAGEMENT_RQUOTES_NUMBER_RANDOM_QUOTES_ERROR');
    return;
}
?>
<span class="mod_rquote_quote_text_file"><?php echo htmlspecialchars($textLine, ENT_QUOTES, 'UTF-8'); ?></span>
