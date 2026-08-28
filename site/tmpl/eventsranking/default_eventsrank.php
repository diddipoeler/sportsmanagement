<?php
/**
 * Native Joomla 5/6 event ranking table.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\TeamPresentationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$input = $this->input;
$databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
$seasonId = max(0, $input->getInt('s', 0));
$colspanevent = (($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_DART') ? 2 : 1;
$showIcons = (int) ($this->config['show_icons'] ?? 0) === 1;
?>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="default_eventsrank">
    <?php foreach ($this->eventtypes as $eventType): ?>
        <?php if ($this->multiple_events) : ?>
            <h2><?php echo Text::_((string) $eventType->name); ?></h2>
        <?php endif; ?>

        <table class="<?php echo $escape($this->config['table_class'] ?? 'table'); ?>">
            <thead>
            <tr class="sectiontableheader">
                <th class="rank"><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_RANK'); ?></th>

                <?php if (!empty($this->config['show_picture_thumb'])) : ?>
                    <th class="td_c">&nbsp;</th>
                <?php endif; ?>

                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PLAYER_NAME'); ?></th>

                <?php if (!empty($this->config['show_nation'])) : ?>
                    <th class="td_c">&nbsp;</th>
                <?php endif; ?>

                <th class="td_l"><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TEAM'); ?></th>

                <?php if ($showIcons) : ?>
                    <th class="td_c" colspan="<?php echo $colspanevent; ?>">
                        <?php
                        $iconPath = (string) ($eventType->icon ?? '');
                        if ($iconPath !== '' && !str_contains($iconPath, '/')) {
                            $iconPath = 'media/com_sportsmanagement/events/' . $iconPath;
                        }
                        echo $iconPath !== ''
                            ? HTMLHelper::image(
                                $iconPath,
                                Text::_((string) $eventType->name),
                                ['title' => Text::_((string) $eventType->name), 'height' => 20]
                            )
                            : Text::_((string) $eventType->name);
                        ?>
                    </th>
                <?php else: ?>
                    <th class="td_c" colspan="<?php echo $colspanevent; ?>">
                        <?php echo Text::_((string) $eventType->name); ?>
                    </th>
                <?php endif; ?>
            </tr>
            </thead>
            <tbody>
            <?php
            $eventId = (int) ($eventType->id ?? 0);
            $rankingRows = $this->eventranking[$eventId] ?? [];
            $counter = 0;
            $lastRank = 0;

            foreach ((array) $rankingRows as $row) {
                $rank = $lastRank == ($row->rank ?? null) ? '-' : ($row->rank ?? '');
                $lastRank = $row->rank ?? 0;
                $teamId = (int) ($row->tid ?? 0);
                $isFavTeam = in_array($teamId, $this->favteams, true);
                $highlightFavTeam = !empty($this->config['highlight_fav']) && $isFavTeam;
                $favStyle = '';

                if ($highlightFavTeam && (int) ($this->project->fav_team_highlight_type ?? 0) === 1) {
                    $styles = [];
                    if (!empty($this->project->fav_team_text_bold)) {
                        $styles[] = 'font-weight:bold';
                    }
                    if (trim((string) ($this->project->fav_team_text_color ?? '')) !== '') {
                        $styles[] = 'color:' . trim((string) $this->project->fav_team_text_color);
                    }
                    if (trim((string) ($this->project->fav_team_color ?? '')) !== '') {
                        $styles[] = 'background-color:' . trim((string) $this->project->fav_team_color);
                    }
                    if ($styles) {
                        $favStyle = ' style="' . $escape(implode(';', $styles)) . '"';
                    }
                }

                $playerName = PersonNameFormatter::format(
                    null,
                    (string) ($row->fname ?? ''),
                    (string) ($row->nname ?? ''),
                    (string) ($row->lname ?? ''),
                    $this->config['name_format'] ?? 0
                );
                ?>
                <tr<?php echo $favStyle; ?>>
                    <td class="rank"><?php echo $escape($rank); ?></td>

                    <?php if (!empty($this->config['show_picture_thumb'])) : ?>
                        <td class="td_c playerpic">
                            <?php
                            $picture = PersonImageHelper::resolve(
                                (string) ($row->teamplayerpic ?? ''),
                                (string) ($row->picture ?? '')
                            );
                            $pictureUrl = PersonImageHelper::url($picture);

                            if ($pictureUrl !== '') {
                                echo ModalImageHelper::render(
                                    'evplayer' . (int) ($row->pid ?? 0),
                                    $pictureUrl,
                                    $playerName,
                                    max(1, (int) ($this->config['player_picture_width'] ?? 40)),
                                    '',
                                    $this->modalwidth,
                                    $this->modalheight,
                                    (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
                                );
                            }
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="td_l playername" width="30%">
                        <?php
                        if (!empty($this->config['link_to_player'])) {
                            $link = SiteRouteHelper::view('player', [
                                'cfg_which_database' => $databaseSelector,
                                's' => $seasonId,
                                'p' => $this->project->slug ?? $this->project->id,
                                'tid' => $row->team_slug ?? $teamId,
                                'pid' => $row->person_slug ?? (int) ($row->pid ?? 0),
                            ]);
                            echo HTMLHelper::link($link, $playerName);
                        } else {
                            echo $playerName;
                        }
                        ?>
                    </td>

                    <?php if (!empty($this->config['show_nation'])) : ?>
                        <td class="td_c playercountry">
                            <?php echo CountryPresentationHelper::flag((string) ($row->country ?? '')); ?>
                        </td>
                    <?php endif; ?>

                    <td class="td_l playerteam" width="30%">
                        <?php
                        $team = $this->teams[$teamId] ?? null;
                        if ($team) {
                            $teamLink = null;
                            if (!empty($this->config['link_to_team']) && ($this->project->id ?? 0) > 0 && $teamId > 0) {
                                $teamLink = SiteRouteHelper::view('teaminfo', [
                                    'cfg_which_database' => $databaseSelector,
                                    's' => $seasonId,
                                    'p' => $this->project->slug ?? $this->project->id,
                                    'tid' => $row->team_slug ?? $teamId,
                                    'ptid' => $row->projectteam_slug ?? 0,
                                ]);
                            }

                            echo TeamPresentationHelper::formatName(
                                $team,
                                'e' . $eventId . 'c' . $counter . 't' . $teamId,
                                $this->config,
                                $highlightFavTeam,
                                $this->project,
                                $databaseSelector,
                                $seasonId,
                                $teamLink
                            );
                        }
                        ?>
                    </td>

                    <td class="td_c playertotal"><?php echo (float) ($row->p ?? 0); ?></td>

                    <?php if (($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_DART') : ?>
                        <td class="td_c playertotal"><?php echo (int) ($row->zaehler ?? 0); ?></td>
                    <?php endif; ?>
                </tr>
                <?php
                $counter++;
            }
            ?>
            </tbody>
        </table>

        <?php if ($this->multiple_events) : ?>
            <div class="fulltablelink">
                <?php
                $link = SiteRouteHelper::view('eventsranking', [
                    'cfg_which_database' => $databaseSelector,
                    's' => $seasonId,
                    'p' => $this->project->slug ?? $this->project->id,
                    'tid' => $this->teamid,
                    'evid' => $eventType->event_slug ?? $eventId,
                    'mid' => $this->matchid,
                    'division' => (int) ($this->division->id ?? 0),
                ]);
                echo HTMLHelper::link($link, Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_MORE'));
                ?>
            </div>
        <?php elseif ($this->pagination) : ?>
            <div class="pageslinks"><?php echo $this->pagination->getPagesLinks(); ?></div>
            <p class="pagescounter"><?php echo $this->pagination->getPagesCounter(); ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
