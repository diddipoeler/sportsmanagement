<?php
/**
 * Default Joomla 5/6 layout for the events ranking module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$project = $rankingData['project'] ?? null;
$eventTypes = $rankingData['eventtypes'] ?? [];
$rankings = $rankingData['rankings'] ?? [];
$showPicture = (int) $params->get('show_picture', 0) === 1;
$showTeam = (int) $params->get('show_team', 1) === 1;
$showLogo = (int) $params->get('show_logo', 0);
$showPlayerLink = (int) $params->get('show_player_link', 1) === 1;
$pictureHeight = max(1, (int) $params->get('picture_height', 40));
$pictureWidth = max(1, (int) $params->get('picture_width', 40));
$isDart = $project && (string) $project->sport_type_name === 'COM_SPORTSMANAGEMENT_ST_DART';
?>
<div class="<?= htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($project && (int) $params->get('show_project_name', 0) === 1) : ?>
        <p class="projectname"><?= htmlspecialchars((string) $project->name, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!$eventTypes) : ?>
        <p class="modjlgstat"><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_EVENTS_SELECTED') ?></p>
    <?php endif; ?>

    <?php foreach ($eventTypes as $eventType) : ?>
        <?php $rows = $rankings[(int) $eventType->id] ?? []; ?>
        <section class="mb-3">
            <h4 class="eventtype"><?= htmlspecialchars(Text::_((string) $eventType->name), ENT_QUOTES, 'UTF-8') ?></h4>
            <?php if (!$rows) : ?>
                <p class="modjlgstat"><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_ITEMS') ?></p>
                <?php continue; ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="<?= htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8') ?>">
                    <thead>
                    <tr>
                        <th><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_RANK') ?></th>
                        <?php if ($showPicture) : ?><th><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_PICTURE') ?></th><?php endif; ?>
                        <th><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_NAME') ?></th>
                        <?php if ($showTeam) : ?><th><?= Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_TEAM') ?></th><?php endif; ?>
                        <th<?= $isDart ? ' colspan="2"' : '' ?>>
                            <?php if ((int) $params->get('show_event_icon', 1) === 1 && !empty($eventType->icon) && $eventType->icon !== 'media/com_sportsmanagement/event_icons/event.gif') : ?>
                                <img src="<?= htmlspecialchars((string) $eventType->icon, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars(Text::_((string) $eventType->name), ENT_QUOTES, 'UTF-8') ?>" width="20">
                            <?php else : ?>
                                <?= htmlspecialchars(Text::_((string) $eventType->name), ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $lastRank = null; ?>
                    <?php foreach ($rows as $index => $item) : ?>
                        <tr class="<?= htmlspecialchars((string) $params->get($index % 2 === 0 ? 'style_class1' : 'style_class2', ''), ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= $lastRank === $item->rank ? '-' : (int) $item->rank ?></td>
                            <?php $lastRank = $item->rank; ?>
                            <?php if ($showPicture) : ?>
                                <td>
                                    <?php if (!empty($item->picture_url)) : ?>
                                        <img src="<?= htmlspecialchars((string) $item->picture_url, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars((string) $item->display_name, ENT_QUOTES, 'UTF-8') ?>"
                                             width="<?= $pictureWidth ?>" height="<?= $pictureHeight ?>">
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td>
                                <?php if ($showPlayerLink && !empty($item->player_url)) : ?>
                                    <a href="<?= htmlspecialchars((string) $item->player_url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $item->display_name, ENT_QUOTES, 'UTF-8') ?></a>
                                <?php else : ?>
                                    <?= htmlspecialchars((string) $item->display_name, ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </td>
                            <?php if ($showTeam) : ?>
                                <td>
                                    <?php $logo = $showLogo === 1 ? $item->team_logo_url : ($showLogo === 2 ? $item->country_logo_url : ''); ?>
                                    <?php if ($logo) : ?><img src="<?= htmlspecialchars((string) $logo, ENT_QUOTES, 'UTF-8') ?>" alt="" width="20" class="teamlogo"> <?php endif; ?>
                                    <?php if (!empty($item->team_url)) : ?>
                                        <a href="<?= htmlspecialchars((string) $item->team_url, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $item->team_display_name, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php else : ?>
                                        <?= htmlspecialchars((string) $item->team_display_name, ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td><?= htmlspecialchars((string) $item->p, ENT_QUOTES, 'UTF-8') ?></td>
                            <?php if ($isDart) : ?><td><?= htmlspecialchars((string) ($item->zaehler ?? ''), ENT_QUOTES, 'UTF-8') ?></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</div>
