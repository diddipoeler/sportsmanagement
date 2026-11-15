<?php
/**
 * Native Joomla 5/6 layout for the SportsManagement Random Player module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$row = $list['player'] ?? null;
if (!is_object($row)) {
    echo '<p class="modjlgrandomplayer">' . Text::_('NO ITEMS') . '</p>';
    return;
}

/**
 * Escape a player, country or module setting as safe UTF-8 HTML.
 *
 * Historical player records sometimes contain malformed scalar values.
 *
 * @param mixed $value Template data supplied by the module dispatcher.
 * @return string HTML-escaped text or empty text for non-scalar input.
 */
$e = static fn(mixed $value): string => htmlspecialchars(
    is_scalar($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
// These parameters are inserted into CSS declarations, not just HTML text.
// Restrict them to hex and named CSS colours to prevent declaration injection.
/**
 * Restrict CSS colour declarations to hexadecimal or named colours.
 *
 * @param mixed $value Joomla module colour setting.
 * @param string $default Safe fallback colour.
 * @return string Safe CSS colour expression.
 */
$safeColor = static function (mixed $value, string $default): string {
    return is_string($value)
        && strlen($value) <= 40
        && preg_match('/^(?:#[0-9a-fA-F]{3,4}|#[0-9a-fA-F]{6}|#[0-9a-fA-F]{8}|[a-zA-Z]{3,30})$/D', $value) === 1
        ? $value : $default;
};
// CSS lengths must remain finite and within a practical display range.
/**
 * Bound legacy image widths to a positive, practical CSS pixel range.
 *
 * @param mixed $value Raw image-width module setting.
 * @return int Width in CSS pixels, between 1 and 1000.
 */
$safeWidth = static function (mixed $value): int {
    if (!is_int($value) && !is_string($value)) {
        return 50;
    }
    $text = (string) $value;
    return strlen($text) <= 5 && preg_match('/^[0-9]{1,5}$/D', $text) === 1
        ? min(1000, max(1, (int) $text)) : 50;
};
/**
 * Reject executable or ambiguous player, team and image URL schemes.
 *
 * Escaping an href attribute does not prevent javascript: or data: from
 * executing. Generated Joomla routes and absolute HTTP(S) URLs remain valid,
 * while protocol-relative paths, URL credentials and controls are refused.
 *
 * @param mixed $value Player link, team link or media source.
 * @return string Safe URL or an empty string for unsupported values.
 */
$safeUrl = static function (mixed $value): string {
    if (!is_string($value)) {
        return '';
    }

    $url = trim($value);

    if ($url === '' || strlen($url) > 4096
        || preg_match('/[\x00-\x20\x7f]/', $url)
        || str_contains($url, '\\')
        || str_starts_with($url, '//')) {
        return '';
    }

    $parts = parse_url($url);

    if (!is_array($parts)) {
        return '';
    }

    $scheme = strtolower((string) ($parts['scheme'] ?? ''));

    if ($scheme !== '' && (!in_array($scheme, ['http', 'https'], true)
        || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']))) {
        return '';
    }

    return $url;
};

$rawMode = $params->get('mode', 'S');
$mode = is_string($rawMode) ? strtoupper($rawMode) : 'S';
$name = $e($row->display_name ?? '');
$playerUrl = $e($safeUrl($row->player_url ?? ''));
$teamUrl = $e($safeUrl($row->team_url ?? ''));
$picture = $e($safeUrl($row->picture_url ?? ''));
$teamPicture = $e($safeUrl($row->team_picture_url ?? ''));
$flag = $e($safeUrl($row->flag_url ?? ''));
$projectName = $e($row->project_name ?? '');
$teamName = $e($row->team_name ?? '');
$positionName = $e($row->position_name ?? '');

if ($mode === 'S') {
    $border = $safeColor($params->get('border_color', 'rebeccapurple'), 'rebeccapurple');
    $inside = $safeColor($params->get('background_color', $params->get('inside_color', '#cccccc')), '#cccccc');
    $textColor = $safeColor($params->get('text_color', '#ffffff'), '#ffffff');
    ?>
    <div class="mod-sm-randomplayer-card" style="border:2px solid <?php echo $border; ?>;background:<?php echo $inside; ?>;border-radius:20px;padding:1rem;max-width:250px;box-shadow:10px 10px 6px 3px #474747;color:<?php echo $textColor; ?>;">
        <?php if ($picture !== '') : ?>
            <?php if ($playerUrl !== '') : ?><a href="<?php echo $playerUrl; ?>"><?php endif; ?><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="width:100%;height:auto;border-radius:16px;"><?php if ($playerUrl !== '') : ?></a><?php endif; ?>
        <?php endif; ?>
        <p><strong><?php echo $name; ?></strong></p>
        <?php if ($positionName !== '') : ?><p><?php echo $positionName; ?></p><?php endif; ?>
        <p><?php echo $projectName; ?></p>
        <p><?php if ($teamUrl !== '') : ?><a href="<?php echo $teamUrl; ?>"><?php endif; ?><?php echo $teamName; ?><?php if ($teamUrl !== '') : ?></a><?php endif; ?></p>
        <?php if ($flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country ?? ''); ?>" style="max-width:30px;height:auto;"><?php endif; ?>
    </div>
    <?php
    return;
}
?>
<div class="container-fluid mod-sm-randomplayer">
    <?php if ($params->get('show_project_name', 1)) : ?><h4><?php echo $projectName; ?></h4><?php endif; ?>
    <?php if ($picture !== '') : ?>
        <p><?php if ($playerUrl !== '') : ?><a href="<?php echo $playerUrl; ?>"><?php endif; ?><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="max-width:<?php echo $safeWidth($params->get('picture_width', 50)); ?>px;height:auto;"><?php if ($playerUrl !== '') : ?></a><?php endif; ?></p>
    <?php endif; ?>
    <p>
        <?php if ($params->get('show_player_flag', 1) && $flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country); ?>" style="max-width:24px;height:auto;"> <?php endif; ?>
        <?php if ($params->get('show_player_link', 1) && $playerUrl !== '') : ?><a href="<?php echo $playerUrl; ?>"><?php echo $name; ?></a><?php else : ?><?php echo $name; ?><?php endif; ?>
    </p>
    <?php if ($params->get('show_team_name', 1)) : ?>
        <p>
            <?php if ($teamPicture !== '') : ?><img src="<?php echo $teamPicture; ?>" alt="<?php echo $teamName; ?>" style="max-width:<?php echo $safeWidth($params->get('team_picture_width', 50)); ?>px;height:auto;"> <?php endif; ?>
            <?php if ($params->get('show_team_link', 1) && $teamUrl !== '') : ?><a href="<?php echo $teamUrl; ?>"><?php echo $teamName; ?></a><?php else : ?><?php echo $teamName; ?><?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($params->get('show_position_name', 1) && $positionName !== '') : ?><p><?php echo $e(Text::_(is_string($row->position_name ?? null) ? $row->position_name : '')); ?></p><?php endif; ?>
</div>
