<?php
/**
 * Native Joomla 5/6 project event ranking for next-match.
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$events = is_array($this->overallevents ?? null) ? $this->overallevents : [];
$players = is_array($this->alloverevents ?? null) ? $this->alloverevents : [];
$rankings = [];

foreach ($events as $event) {
    $eventId = (int) ($event->id ?? 0);
    if ($eventId <= 0) {
        continue;
    }

    $rows = [];
    foreach ($players as $playerId => $player) {
        $sum = (float) ($player->events[$eventId]->event_sum ?? 0);
        if ($sum == 0.0) {
            continue;
        }

        $rows[] = (object) [
            'playerid' => (int) $playerId,
            'event_sum' => $sum,
        ];
    }

    usort(
        $rows,
        static fn (object $a, object $b): int =>
            ($b->event_sum <=> $a->event_sum) ?: ($a->playerid <=> $b->playerid)
    );
    $rankings[$eventId] = $rows;
}
?>
<div class="<?php echo $escape($this->divclassrow); ?> table-responsive" id="nextmatch-alloverevents-ranking">
    <?php
    $this->notes = [Text::_('COM_SPORTSMANAGEMENT_NEXTMATCH_ALLOVEREVENTSRANKING')];
    echo $this->loadTemplate('jsm_notes');
    ?>

    <?php if ($events) : ?>
        <ul class="nav nav-tabs" id="nextmatch-event-tabs" role="tablist">
            <?php foreach (array_values($events) as $index => $event) : ?>
                <?php
                $eventId = (int) ($event->id ?? 0);
                $tabId = 'nextmatch-event-' . $eventId;
                $title = Text::_((string) ($event->name ?? ''));
                $icon = trim((string) ($event->icon ?? ''));
                if ($icon !== '' && !str_contains($icon, '/')) {
                    $icon = 'media/com_sportsmanagement/events/' . $icon;
                }
                ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link<?php echo $index === 0 ? ' active' : ''; ?>"
                            id="<?php echo $escape($tabId); ?>-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#<?php echo $escape($tabId); ?>"
                            type="button" role="tab"
                            aria-controls="<?php echo $escape($tabId); ?>"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                        <?php if ($icon !== '') : ?>
                            <?php echo HTMLHelper::image($icon, $title, ['title' => $title, 'height' => 20]); ?>
                        <?php endif; ?>
                        <?php echo $escape($title); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content" id="nextmatch-event-tab-content">
            <?php foreach (array_values($events) as $index => $event) : ?>
                <?php
                $eventId = (int) ($event->id ?? 0);
                $tabId = 'nextmatch-event-' . $eventId;
                $rows = $rankings[$eventId] ?? [];
                ?>
                <div class="tab-pane fade<?php echo $index === 0 ? ' show active' : ''; ?>"
                     id="<?php echo $escape($tabId); ?>" role="tabpanel"
                     aria-labelledby="<?php echo $escape($tabId); ?>-tab" tabindex="0">
                    <?php if ($rows) : ?>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_RANK'); ?></th>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_TEAM'); ?></th>
                                <th><?php echo Text::_('COM_SPORTSMANAGEMENT_EVENTSRANKING_PLAYER_NAME'); ?></th>
                                <th></th>
                                <th><?php echo $escape(Text::_((string) ($event->name ?? ''))); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rows as $rankIndex => $ranking) : ?>
                                <?php
                                $player = $players[$ranking->playerid] ?? null;
                                if (!$player) {
                                    continue;
                                }
                                $name = PersonNameFormatter::format(
                                    null,
                                    (string) ($player->firstname1 ?? ''),
                                    (string) ($player->nickname1 ?? ''),
                                    (string) ($player->lastname1 ?? ''),
                                    $this->config['name_format'] ?? 0
                                );
                                $picture = PersonImageHelper::url(
                                    PersonImageHelper::resolve((string) ($player->tppicture1 ?? ''))
                                );
                                ?>
                                <tr>
                                    <td><?php echo $rankIndex + 1; ?></td>
                                    <td><?php echo $escape($player->team_name ?? ''); ?></td>
                                    <td><?php echo $name; ?></td>
                                    <td>
                                        <?php if ($picture !== '') : ?>
                                            <?php echo ModalImageHelper::render(
                                                'nextmatch-event-player-' . (int) $ranking->playerid . '-' . $eventId,
                                                $picture,
                                                strip_tags($name),
                                                20,
                                                '',
                                                $this->modalwidth,
                                                $this->modalheight,
                                                (int) ($this->overallconfig['use_jquery_modal'] ?? 0)
                                            ); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $escape($ranking->event_sum); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p class="text-muted mb-0"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="text-muted mb-0"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></p>
    <?php endif; ?>
</div>
