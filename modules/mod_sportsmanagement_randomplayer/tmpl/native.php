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

$e = static fn(mixed $value): string => htmlspecialchars(
    is_scalar($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
// These parameters are inserted into CSS declarations, not just HTML text.
// Restrict them to hex and named CSS colours to prevent declaration injection.
$safeColor = static function (mixed $value, string $default): string {
    return is_string($value)
        && strlen($value) <= 40
        && preg_match('/^(?:#[0-9a-fA-F]{3,4}|#[0-9a-fA-F]{6}|#[0-9a-fA-F]{8}|[a-zA-Z]{3,30})$/D', $value) === 1
        ? $value : $default;
};
// CSS lengths must remain finite and within a practical display range.
$safeWidth = static function (mixed $value): int {
    if (!is_int($value) && !is_string($value)) {
        return 50;
    }
    $text = (string) $value;
    return strlen($text) <= 5 && preg_match('/^[0-9]{1,5}$/D', $text) === 1
        ? min(1000, max(1, (int) $text)) : 50;
};
$rawMode = $params->get('mode', 'S');
$mode = is_string($rawMode) ? strtoupper($rawMode) : 'S';
$name = $e($row->display_name ?? '');
$playerUrl = $e($row->player_url ?? '');
$teamUrl = $e($row->team_url ?? '');
$picture = $e($row->picture_url ?? '');
$teamPicture = $e($row->team_picture_url ?? '');
$flag = $e($row->flag_url ?? '');
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
            <a href="<?php echo $playerUrl; ?>"><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="width:100%;height:auto;border-radius:16px;"></a>
        <?php endif; ?>
        <p><strong><?php echo $name; ?></strong></p>
        <?php if ($positionName !== '') : ?><p><?php echo $positionName; ?></p><?php endif; ?>
        <p><?php echo $projectName; ?></p>
        <p><a href="<?php echo $teamUrl; ?>"><?php echo $teamName; ?></a></p>
        <?php if ($flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country ?? ''); ?>" style="max-width:30px;height:auto;"><?php endif; ?>
    </div>
    <?php
    return;
}
?>
<div class="container-fluid mod-sm-randomplayer">
    <?php if ($params->get('show_project_name', 1)) : ?><h4><?php echo $projectName; ?></h4><?php endif; ?>
    <?php if ($picture !== '') : ?>
        <p><a href="<?php echo $playerUrl; ?>"><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="max-width:<?php echo $safeWidth($params->get('picture_width', 50)); ?>px;height:auto;"></a></p>
    <?php endif; ?>
    <p>
        <?php if ($params->get('show_player_flag', 1) && $flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country); ?>" style="max-width:24px;height:auto;"> <?php endif; ?>
        <?php if ($params->get('show_player_link', 1)) : ?><a href="<?php echo $playerUrl; ?>"><?php echo $name; ?></a><?php else : ?><?php echo $name; ?><?php endif; ?>
    </p>
    <?php if ($params->get('show_team_name', 1)) : ?>
        <p>
            <?php if ($teamPicture !== '') : ?><img src="<?php echo $teamPicture; ?>" alt="<?php echo $teamName; ?>" style="max-width:<?php echo $safeWidth($params->get('team_picture_width', 50)); ?>px;height:auto;"> <?php endif; ?>
            <?php if ($params->get('show_team_link', 1)) : ?><a href="<?php echo $teamUrl; ?>"><?php echo $teamName; ?></a><?php else : ?><?php echo $teamName; ?><?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($params->get('show_position_name', 1) && $positionName !== '') : ?><p><?php echo $e(Text::_(is_string($row->position_name ?? null) ? $row->position_name : '')); ?></p><?php endif; ?>
</div>
