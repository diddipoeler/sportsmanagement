<?php
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Render match events and substitutions without the legacy HTML helper.
 */
final class MatchEventPresentationHelper
{
    public static function render(
        object $game,
        array $projectEvents,
        array $matchEvents,
        array $substitutions,
        array $config
    ): string {
        if (!$matchEvents && !$substitutions) {
            return '';
        }

        $eventTypes = [];
        foreach ($projectEvents as $eventType) {
            $eventTypes[(int) ($eventType->id ?? 0)] = $eventType;
        }

        if (!empty($config['use_tabs_events'])) {
            return self::renderTabs($game, $eventTypes, $matchEvents, $substitutions, $config);
        }

        return self::renderEventColumns($game, $eventTypes, $matchEvents, $config, true);
    }

    private static function renderTabs(
        object $game,
        array $eventTypes,
        array $matchEvents,
        array $substitutions,
        array $config
    ): string {
        $tabs = [];

        foreach ($eventTypes as $eventTypeId => $eventType) {
            $events = array_values(array_filter(
                $matchEvents,
                static fn (object $event): bool => (int) ($event->event_type_id ?? 0) === $eventTypeId
            ));

            if (!$events) {
                continue;
            }

            $tabs[] = [
                'id' => 'event-' . $eventTypeId,
                'label' => self::eventLabel($eventType, $config),
                'content' => self::renderEventColumns($game, $eventTypes, $events, $config, false),
            ];
        }

        if ($substitutions) {
            $tabs[] = [
                'id' => 'substitutions',
                'label' => self::escape(Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION')),
                'content' => self::renderSubstitutionColumns($game, $substitutions, $config),
            ];
        }

        if (!$tabs) {
            return '';
        }

        $selector = 'nextmatch-events-' . (int) ($game->id ?? 0);
        $output = '<ul class="nav nav-tabs" id="' . self::escape($selector) . '-tabs" role="tablist">';

        foreach ($tabs as $index => $tab) {
            $active = $index === 0;
            $tabId = $selector . '-' . $tab['id'];
            $output .= '<li class="nav-item" role="presentation">'
                . '<button class="nav-link' . ($active ? ' active' : '') . '"'
                . ' id="' . self::escape($tabId) . '-tab"'
                . ' data-bs-toggle="tab" data-bs-target="#' . self::escape($tabId) . '"'
                . ' type="button" role="tab" aria-selected="' . ($active ? 'true' : 'false') . '">'
                . $tab['label']
                . '</button></li>';
        }

        $output .= '</ul><div class="tab-content">';

        foreach ($tabs as $index => $tab) {
            $active = $index === 0;
            $tabId = $selector . '-' . $tab['id'];
            $output .= '<div class="tab-pane fade' . ($active ? ' show active' : '') . '"'
                . ' id="' . self::escape($tabId) . '" role="tabpanel"'
                . ' aria-labelledby="' . self::escape($tabId) . '-tab" tabindex="0">'
                . $tab['content']
                . '</div>';
        }

        return $output . '</div>';
    }

    private static function renderEventColumns(
        object $game,
        array $eventTypes,
        array $events,
        array $config,
        bool $showEventInfo
    ): string {
        $homeId = (int) ($game->projectteam1_id ?? 0);
        $awayId = (int) ($game->projectteam2_id ?? 0);

        return '<table class="matchreport table table-borderless mb-0"><tr>'
            . '<td class="list-left"><ul class="list-inline mb-0">'
            . self::eventList($events, $eventTypes, $homeId, $config, $showEventInfo)
            . '</ul></td>'
            . '<td class="list-right"><ul class="list-inline mb-0">'
            . self::eventList($events, $eventTypes, $awayId, $config, $showEventInfo)
            . '</ul></td>'
            . '</tr></table>';
    }

    private static function eventList(
        array $events,
        array $eventTypes,
        int $projectTeamId,
        array $config,
        bool $showEventInfo
    ): string {
        $output = '';

        foreach ($events as $event) {
            if ((int) ($event->ptid ?? 0) !== $projectTeamId) {
                continue;
            }

            $eventType = $eventTypes[(int) ($event->event_type_id ?? 0)] ?? null;
            $output .= '<li class="list-inline-item d-block">';

            if ($showEventInfo && $eventType) {
                if (!empty($config['show_events_with_icons'])) {
                    $output .= self::eventIcon($eventType);
                } else {
                    $output .= self::escape(Text::_((string) ($eventType->name ?? ''))) . ' ';
                }
            }

            if (!empty($config['show_event_minute']) && (int) ($event->event_time ?? 0) > 0) {
                $output .= '<strong>' . str_pad((string) ((int) $event->event_time), 2, '0', STR_PAD_LEFT) . '\'</strong> ';
            }

            $name = PersonNameFormatter::format(
                null,
                (string) ($event->firstname1 ?? ''),
                (string) ($event->nickname1 ?? ''),
                (string) ($event->lastname1 ?? ''),
                $config['name_format'] ?? 0
            );
            $output .= $name !== '' ? $name : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON'));

            $details = [];
            if (!empty($config['show_event_sum']) && (float) ($event->event_sum ?? 0) > 0) {
                $details[] = self::escape((string) $event->event_sum);
            }
            if (!empty($config['show_event_notice']) && trim((string) ($event->notice ?? '')) !== '') {
                $details[] = self::escape((string) $event->notice);
            }
            if ($details) {
                $output .= ' (' . implode(' | ', $details) . ')';
            }

            $output .= '</li>';
        }

        return $output;
    }

    private static function renderSubstitutionColumns(object $game, array $substitutions, array $config): string
    {
        $homeId = (int) ($game->projectteam1_id ?? 0);
        $awayId = (int) ($game->projectteam2_id ?? 0);

        return '<table class="matchreport table table-borderless mb-0"><tr>'
            . '<td class="list"><ul class="list-unstyled mb-0">'
            . self::substitutionList($substitutions, $homeId, $config)
            . '</ul></td>'
            . '<td class="list"><ul class="list-unstyled mb-0">'
            . self::substitutionList($substitutions, $awayId, $config)
            . '</ul></td>'
            . '</tr></table>';
    }

    private static function substitutionList(array $substitutions, int $projectTeamId, array $config): string
    {
        $output = '';

        foreach ($substitutions as $substitution) {
            if ((int) ($substitution->ptid ?? 0) !== $projectTeamId) {
                continue;
            }

            $incoming = PersonNameFormatter::format(
                null,
                (string) ($substitution->firstname ?? ''),
                (string) ($substitution->nickname ?? ''),
                (string) ($substitution->lastname ?? ''),
                $config['name_format'] ?? 0
            );
            $outgoing = PersonNameFormatter::format(
                null,
                (string) ($substitution->out_firstname ?? ''),
                (string) ($substitution->out_nickname ?? ''),
                (string) ($substitution->out_lastname ?? ''),
                $config['name_format'] ?? 0
            );

            $output .= '<li class="mb-2">'
                . '<strong>' . self::escape((string) ($substitution->in_out_time ?? '')) . '. '
                . self::escape(Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')) . '</strong><br>'
                . '<span aria-hidden="true">↓</span> ' . ($outgoing !== '' ? $outgoing : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON')))
                . self::position((string) ($substitution->out_position ?? '')) . '<br>'
                . '<span aria-hidden="true">↑</span> ' . ($incoming !== '' ? $incoming : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON')))
                . self::position((string) ($substitution->in_position ?? ''))
                . '</li>';
        }

        return $output;
    }

    private static function eventLabel(object $eventType, array $config): string
    {
        if (!empty($config['show_events_with_icons'])) {
            return self::eventIcon($eventType) . ' ' . self::escape(Text::_((string) ($eventType->name ?? '')));
        }

        return self::escape(Text::_((string) ($eventType->name ?? '')));
    }

    private static function eventIcon(object $eventType): string
    {
        $icon = trim((string) ($eventType->icon ?? $eventType->eventtype_icon ?? ''));
        if ($icon === '') {
            return '';
        }

        $url = preg_match('#^https?://#i', $icon)
            ? $icon
            : rtrim(Uri::root(), '/') . '/' . ltrim($icon, '/');

        return '<img src="' . self::escape($url) . '" alt="'
            . self::escape(Text::_((string) ($eventType->name ?? ''))) . '" width="20" height="20">';
    }

    private static function position(string $position): string
    {
        $position = trim($position);

        return $position !== '' ? ' (' . self::escape(Text::_($position)) . ')' : '';
    }

    private static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
