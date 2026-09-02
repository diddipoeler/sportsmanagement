<?php
/**
 * Native Joomla 5/6 rendering layout for the TeamPlayers module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$moduleId = (int) $module->id;
$mode = (string) $params->get('template', 'L');
?>
<div class="mod-sportsmanagement-teamplayers <?php echo $escape($params->get('moduleclass_sfx', '')); ?>" id="mod-sportsmanagement-teamplayers-<?php echo $moduleId; ?>">
    <?php if (!$project || !$players) : ?>
        <p class="modteamplayers"><?php echo Text::_('NO ITEMS'); ?></p>
    <?php else : ?>
        <?php if ((int) $params->get('show_project_name', 0) === 1) : ?>
            <div class="projectname"><?php echo $escape($project->name ?? ''); ?></div>
        <?php endif; ?>
        <?php if ((int) $params->get('show_team_name', 0) === 1) : ?>
            <div class="projectname"><?php echo $escape($project->team_name ?? ''); ?></div>
        <?php endif; ?>

        <?php if ($mode === 'C') : ?>
            <?php
            $sliderMode = match ((string) $params->get('slider_mode', 'H')) {
                'V' => 'vertical',
                'F' => 'fade',
                default => 'horizontal',
            };
            ?>
            <div class="jsm-teamplayers-carousel is-<?php echo $escape($sliderMode); ?>"
                 data-jsm-teamplayers-carousel
                 data-mode="<?php echo $escape($sliderMode); ?>"
                 data-auto="<?php echo (int) $params->get('slider_auto', 1); ?>"
                 data-speed="<?php echo max(250, (int) $params->get('slider_speed', 500)); ?>"
                 style="--jsm-card-width: <?php echo max(160, (int) $params->get('slider_width', 250)); ?>px; --jsm-visible: <?php echo max(1, (int) $params->get('max_slides', 3)); ?>;">
                <div class="jsm-teamplayers-track">
                    <?php foreach ($players as $index => $player) : ?>
                        <article class="jsm-teamplayers-card<?php echo $index === 0 ? ' is-active' : ''; ?>" data-jsm-teamplayers-card>
                            <?php if (!empty($player->image_url)) : ?>
                                <img class="jsm-teamplayers-photo" src="<?php echo $escape($player->image_url); ?>" alt="<?php echo $escape($player->display_name); ?>" loading="lazy" />
                            <?php endif; ?>
                            <div class="jsm-teamplayers-name">
                                <?php echo $player->flag_html; ?>
                                <?php if (!empty($player->player_url)) : ?>
                                    <a href="<?php echo $escape($player->player_url); ?>"><?php echo nl2br($escape($player->display_name)); ?></a>
                                <?php else : ?>
                                    <?php echo nl2br($escape($player->display_name)); ?>
                                <?php endif; ?>
                            </div>
                            <?php if ((int) $params->get('show_positions', 1) === 1 && !empty($player->position)) : ?>
                                <div class="jsm-teamplayers-position"><?php echo Text::_((string) $player->position); ?></div>
                            <?php endif; ?>
                            <?php if ((int) $params->get('show_mins_played', 1) === 1) : ?>
                                <div class="jsm-teamplayers-minutes"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMPLAYERS_MINS_PLAYED'); ?>: <?php echo (int) $player->minutes_played; ?></div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="jsm-teamplayers-controls">
                    <?php if ((int) $params->get('slider_navigation', 1) === 1) : ?>
                        <button type="button" class="btn btn-sm btn-secondary" data-jsm-teamplayers-prev aria-label="<?php echo $escape(Text::_('JPREVIOUS')); ?>">&lsaquo;</button>
                        <button type="button" class="btn btn-sm btn-secondary" data-jsm-teamplayers-next aria-label="<?php echo $escape(Text::_('JNEXT')); ?>">&rsaquo;</button>
                    <?php endif; ?>
                    <?php if ((int) $params->get('slider_pagination', 1) === 1) : ?>
                        <span class="jsm-teamplayers-pagination" data-jsm-teamplayers-pagination>1 / <?php echo count($players); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php else : ?>
            <div class="table-responsive">
                <table class="<?php echo $escape($params->get('table_class', 'table')); ?> jsm-teamplayers-table">
                    <thead>
                        <tr>
                            <?php if ((int) $params->get('show_positions', 1) === 1) : ?>
                                <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_POSITION'); ?></th>
                            <?php endif; ?>
                            <th scope="col"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_PERSON'); ?></th>
                            <?php if ((int) $params->get('show_mins_played', 1) === 1) : ?>
                                <th scope="col"><?php echo Text::_('MOD_SPORTSMANAGEMENT_TEAMPLAYERS_MINS_PLAYED'); ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($players as $player) : ?>
                            <tr>
                                <?php if ((int) $params->get('show_positions', 1) === 1) : ?>
                                    <td><?php echo !empty($player->position) ? Text::_((string) $player->position) : ''; ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php echo $player->flag_html; ?>
                                    <?php if (!empty($player->player_url)) : ?>
                                        <a href="<?php echo $escape($player->player_url); ?>"><?php echo nl2br($escape($player->display_name)); ?></a>
                                    <?php else : ?>
                                        <?php echo nl2br($escape($player->display_name)); ?>
                                    <?php endif; ?>
                                </td>
                                <?php if ((int) $params->get('show_mins_played', 1) === 1) : ?>
                                    <td><?php echo (int) $player->minutes_played; ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
