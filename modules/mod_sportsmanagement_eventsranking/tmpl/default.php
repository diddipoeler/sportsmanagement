<?php
/**
 * Safe Joomla 5/6 native Events Ranking module layout.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Escape legacy ranking and module settings before rendering HTML.
 *
 * Accept only scalar text and numbers, preventing PHP 8 warnings from
 * corrupted array/object values and replacing malformed UTF-8 sequences.
 *
 * @param mixed $value Event name, statistic or module option.
 * @return string Escaped text suitable for HTML attributes and content.
 */
$escape = static fn (mixed $value): string => htmlspecialchars(
    is_string($value) || is_numeric($value) ? (string) $value : '',
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);

/**
 * Restrict module image sources and links to safe relative and HTTP(S) URLs.
 *
 * Legacy ranking rows may contain untrusted or corrupt URL fields. Escaping
 * HTML alone does not stop javascript: links, data: images, protocol-relative
 * redirects or credential-bearing external URLs.
 *
 * @param mixed $value Candidate native route or ranking image URL.
 * @return string Safe URL, or empty text when the value is not usable.
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

$project = is_object($rankingData['project'] ?? null) ? $rankingData['project'] : null;
$eventTypes = is_array($rankingData['eventtypes'] ?? null) ? $rankingData['eventtypes'] : [];
$rankings = is_array($rankingData['rankings'] ?? null) ? $rankingData['rankings'] : [];
$showPicture = (int) $params->get('show_picture', 0) === 1;
$showTeam = (int) $params->get('show_team', 1) === 1;
$showLogo = (int) $params->get('show_logo', 0);
$showPlayerLink = (int) $params->get('show_player_link', 1) === 1;
$pictureHeight = max(1, (int) $params->get('picture_height', 40));
$pictureWidth = max(1, (int) $params->get('picture_width', 40));
$isDart = $project && ($project->sport_type_name ?? null) === 'COM_SPORTSMANAGEMENT_ST_DART';
?>
<div class="<?= $escape($params->get('moduleclass_sfx', '')) ?>">
    <?php if ($project && (int) $params->get('show_project_name', 0) === 1) : ?>
        <p class="projectname"><?= $escape($project->name ?? '') ?></p>
    <?php endif; ?>

    <?php if (!$eventTypes) : ?>
        <p class="modjlgstat"><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_EVENTS_SELECTED')) ?></p>
    <?php endif; ?>

    <?php foreach ($eventTypes as $eventType) : ?>
        <?php
        if (!is_object($eventType)) {
            continue;
        }
        $eventId = (int) ($eventType->id ?? 0);
        $eventName = is_string($eventType->name ?? null) ? $eventType->name : '';
        $eventLabel = $escape(Text::_($eventName));
        $rows = $rankings[$eventId] ?? [];
        $rows = is_array($rows) ? $rows : [];
        $eventIconUrl = $safeUrl($eventType->icon_url ?? '');
        ?>
        <section class="mb-3">
            <h4 class="eventtype"><?= $eventLabel ?></h4>
            <?php if (!$rows) : ?>
                <p class="modjlgstat"><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_NO_ITEMS')) ?></p>
                <?php continue; ?>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="<?= $escape($params->get('table_class', 'table')) ?>">
                    <thead>
                    <tr>
                        <th><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_RANK')) ?></th>
                        <?php if ($showPicture) : ?><th><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_PICTURE')) ?></th><?php endif; ?>
                        <th><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_NAME')) ?></th>
                        <?php if ($showTeam) : ?><th><?= $escape(Text::_('MOD_SPORTSMANAGEMENT_EVENTSRANKING_COL_TEAM')) ?></th><?php endif; ?>
                        <th<?= $isDart ? ' colspan="2"' : '' ?>>
                            <?php if ((int) $params->get('show_event_icon', 1) === 1 && $eventIconUrl !== '' && ($eventType->icon ?? '') !== 'media/com_sportsmanagement/event_icons/event.gif') : ?>
                                <img src="<?= $escape($eventIconUrl) ?>" alt="<?= $eventLabel ?>" width="20">
                            <?php else : ?>
                                <?= $eventLabel ?>
                            <?php endif; ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $lastRank = null; ?>
                    <?php foreach ($rows as $index => $item) : ?>
                        <?php
                        if (!is_object($item)) {
                            continue;
                        }
                        $rank = (int) ($item->rank ?? 0);
                        $playerUrl = $safeUrl($item->player_url ?? '');
                        $teamUrl = $safeUrl($item->team_url ?? '');
                        $pictureUrl = $safeUrl($item->picture_url ?? '');
                        $logo = $showLogo === 1 ? ($item->team_logo_url ?? '') : ($showLogo === 2 ? ($item->country_logo_url ?? '') : '');
                        $logoUrl = $safeUrl($logo);
                        ?>
                        <tr class="<?= $escape($params->get((int) $index % 2 === 0 ? 'style_class1' : 'style_class2', '')) ?>">
                            <td><?= $lastRank === $rank ? '-' : $rank ?></td>
                            <?php $lastRank = $rank; ?>
                            <?php if ($showPicture) : ?>
                                <td>
                                    <?php if ($pictureUrl !== '') : ?>
                                        <img src="<?= $escape($pictureUrl) ?>"
                                             alt="<?= $escape($item->display_name ?? '') ?>"
                                             width="<?= $pictureWidth ?>" height="<?= $pictureHeight ?>">
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td>
                                <?php if ($showPlayerLink && $playerUrl !== '') : ?>
                                    <a href="<?= $escape($playerUrl) ?>"><?= $escape($item->display_name ?? '') ?></a>
                                <?php else : ?>
                                    <?= $escape($item->display_name ?? '') ?>
                                <?php endif; ?>
                            </td>
                            <?php if ($showTeam) : ?>
                                <td>
                                    <?php if ($logoUrl !== '') : ?><img src="<?= $escape($logoUrl) ?>" alt="" width="20" class="teamlogo"> <?php endif; ?>
                                    <?php if ($teamUrl !== '') : ?>
                                        <a href="<?= $escape($teamUrl) ?>"><?= $escape($item->team_display_name ?? '') ?></a>
                                    <?php else : ?>
                                        <?= $escape($item->team_display_name ?? '') ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td><?= $escape($item->p ?? '') ?></td>
                            <?php if ($isDart) : ?><td><?= $escape($item->zaehler ?? '') ?></td><?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>
</div>
