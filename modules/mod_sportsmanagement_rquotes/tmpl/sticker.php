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

$color = static function (string $value, string $fallback): string {
    $value = trim($value);
    return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : $fallback;
};
$border = (bool) $params->get('border', 1);
$borderRounded = (bool) $params->get('border_rounded', 1);
$borderShadow = (bool) $params->get('border_shadow', 1);
$borderColor = $color((string) $params->get('border_color', '#41008a'), '#41008a');
$backgroundColor = $color((string) $params->get('background_color', '#eeeeee'), '#eeeeee');
$textColor = $color((string) $params->get('text_color', '#000000'), '#000000');
$authorColor = $color((string) $params->get('author_color', '#000000'), '#000000');
$textSize = max(8, min(72, (int) $params->get('text_size', 14)));
$authorSize = max(8, min(72, (int) $params->get('author_size', 18)));
$showPicture = (bool) $params->get('showpicture', 0);
$pictureWidth = max(1, min(1000, (int) $params->get('picture_width', 50)));
$boxStyle = 'background-color:' . $backgroundColor . ';margin:0 0 25px;';
if ($border) {
    $boxStyle .= 'border:1px solid ' . $borderColor . ';';
}
if ($borderRounded) {
    $boxStyle .= 'border-radius:20px;';
}
if ($borderShadow) {
    $boxStyle .= 'box-shadow:10px 10px 6px 3px #474747;';
}
?>
<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php foreach ($list as $rquote) : ?>
        <div class="container-fluid mod-rquote-sticker" style="<?php echo htmlspecialchars($boxStyle, ENT_QUOTES, 'UTF-8'); ?>">
            <?php if ($showPicture && !empty($rquote->picture_url)) : ?>
                <div class="photo">
                    <img src="<?php echo htmlspecialchars((string) $rquote->picture_url, ENT_QUOTES, 'UTF-8'); ?>"
                         class="img-fluid"
                         width="<?php echo $pictureWidth; ?>"
                         alt="<?php echo htmlspecialchars((string) ($rquote->author ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            <?php endif; ?>

            <div class="row"
                 style="text-align:left;font-style:<?php echo (bool) $params->get('text_italic', 1) ? 'italic' : 'normal'; ?>;font-size:<?php echo $textSize; ?>px;color:<?php echo $textColor; ?>;display:block;margin:0 0 10px 10px;clear:both;">
                <?php echo (string) ($rquote->quote ?? ''); ?>
            </div>

            <div class="row"
                 style="text-align:<?php echo (bool) $params->get('author_align', 1) ? 'right' : 'left'; ?>;font-style:<?php echo (bool) $params->get('author_italic', 1) ? 'italic' : 'normal'; ?>;font-size:<?php echo $authorSize; ?>px;color:<?php echo $authorColor; ?>;display:block;margin:0 0 10px 10px;clear:both;">
                <?php echo htmlspecialchars((string) ($rquote->author ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
