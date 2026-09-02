<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$row = $list['player'] ?? null;
if (!$row) {
    echo '<p class="modjlgrandomplayer">' . Text::_('NO ITEMS') . '</p>';
    return;
}

$e = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$mode = strtoupper((string) $params->get('mode', 'S'));
$name = $e($row->display_name);
$playerUrl = $e($row->player_url);
$teamUrl = $e($row->team_url);
$picture = $e($row->picture_url);
$teamPicture = $e($row->team_picture_url);
$flag = $e($row->flag_url);
$projectName = $e($row->project_name);
$teamName = $e($row->team_name);
$positionName = $e($row->position_name);

if ($mode === 'S') {
    $border = $e($params->get('border_color', 'rebeccapurple'));
    $inside = $e($params->get('background_color', $params->get('inside_color', '#cccccc')));
    $textColor = $e($params->get('text_color', '#ffffff'));
    ?>
    <div class="mod-sm-randomplayer-card" style="border:2px solid <?php echo $border; ?>;background:<?php echo $inside; ?>;border-radius:20px;padding:1rem;max-width:250px;box-shadow:10px 10px 6px 3px #474747;color:<?php echo $textColor; ?>;">
        <?php if ($picture !== '') : ?>
            <a href="<?php echo $playerUrl; ?>"><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="width:100%;height:auto;border-radius:16px;"></a>
        <?php endif; ?>
        <p><strong><?php echo $name; ?></strong></p>
        <?php if ($positionName !== '') : ?><p><?php echo $positionName; ?></p><?php endif; ?>
        <p><?php echo $projectName; ?></p>
        <p><a href="<?php echo $teamUrl; ?>"><?php echo $teamName; ?></a></p>
        <?php if ($flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country); ?>" style="max-width:30px;height:auto;"><?php endif; ?>
    </div>
    <?php
    return;
}
?>
<div class="container-fluid mod-sm-randomplayer">
    <?php if ($params->get('show_project_name', 1)) : ?><h4><?php echo $projectName; ?></h4><?php endif; ?>
    <?php if ($picture !== '') : ?>
        <p><a href="<?php echo $playerUrl; ?>"><img src="<?php echo $picture; ?>" alt="<?php echo $name; ?>" style="max-width:<?php echo (int) $params->get('picture_width', 50); ?>px;height:auto;"></a></p>
    <?php endif; ?>
    <p>
        <?php if ($params->get('show_player_flag', 1) && $flag !== '') : ?><img src="<?php echo $flag; ?>" alt="<?php echo $e($row->country); ?>" style="max-width:24px;height:auto;"> <?php endif; ?>
        <?php if ($params->get('show_player_link', 1)) : ?><a href="<?php echo $playerUrl; ?>"><?php echo $name; ?></a><?php else : ?><?php echo $name; ?><?php endif; ?>
    </p>
    <?php if ($params->get('show_team_name', 1)) : ?>
        <p>
            <?php if ($teamPicture !== '') : ?><img src="<?php echo $teamPicture; ?>" alt="<?php echo $teamName; ?>" style="max-width:<?php echo (int) $params->get('team_picture_width', 50); ?>px;height:auto;"> <?php endif; ?>
            <?php if ($params->get('show_team_link', 1)) : ?><a href="<?php echo $teamUrl; ?>"><?php echo $teamName; ?></a><?php else : ?><?php echo $teamName; ?><?php endif; ?>
        </p>
    <?php endif; ?>
    <?php if ($params->get('show_position_name', 1) && $positionName !== '') : ?><p><?php echo Text::_((string) $row->position_name); ?></p><?php endif; ?>
</div>
