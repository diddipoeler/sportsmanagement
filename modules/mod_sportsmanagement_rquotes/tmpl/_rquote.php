<?php
/**
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

$quotemarks = (int) $params->get('quotemarks', 0);
$showPicture = (bool) $params->get('showpicture', 0);
$author = htmlspecialchars((string) ($rquote->author ?? ''), ENT_QUOTES, 'UTF-8');
$quote = (string) ($rquote->quote ?? '');
$pictureUrl = (string) ($rquote->picture_url ?? '');

if ($quotemarks > 0) {
    $quote = strip_tags($quote, '<img><br><a>');
}
?>
<strong>
    <p>
        <?php if ($showPicture && $pictureUrl !== '') : ?>
            <img class="float-start me-2"
                 src="<?php echo htmlspecialchars($pictureUrl, ENT_QUOTES, 'UTF-8'); ?>"
                 alt="<?php echo $author; ?>"
                 width="50">
        <?php endif; ?>

        <?php if ($quotemarks === 1) : ?>
            <span>&quot; <?php echo $quote; ?> &quot;</span>
        <?php elseif ($quotemarks === 2) : ?>
            <span>
                <img src="modules/mod_sportsmanagement_rquotes/assets/images/quote1_25_start.png" width="15" height="15" alt="">
                <?php echo $quote; ?>
                <img src="modules/mod_sportsmanagement_rquotes/assets/images/quote1_25_end.png" width="15" height="15" alt="">
            </span>
        <?php elseif ($quotemarks === 3) : ?>
            <span class="mod_rquote_css"><span><?php echo $quote; ?></span></span>
        <?php else : ?>
            <?php echo $quote; ?>
        <?php endif; ?>

        <span class="mod_rquote_author d-block text-end"><?php echo $author; ?></span>
    </p>
</strong>
