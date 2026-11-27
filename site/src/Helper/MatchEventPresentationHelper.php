<?php
/**
 * Joomla 5/6 SportsManagement site helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Render match events and substitutions without the legacy HTML helper.
 */
final class MatchEventPresentationHelper
{
    /**
     * Render a native Joomla match report from valid events and substitutions.
     *
     * Discards malformed non-object records before tab grouping or column
     * rendering, so corrupt legacy query results cannot cause a PHP 8 type
     * error. Normalises malformed optional display fields before formatting.
     * Maps strictly validated positive event type and team IDs,
     * rejecting malformed imported identifiers before grouping or rendering.
     *
     * @param object $game Match row containing the home and away team IDs.
     * @param array<int,mixed> $projectEvents Available project event types.
     * @param array<int,mixed> $matchEvents Events associated with the fixture.
     * @param array<int,mixed> $substitutions Substitution records for the fixture.
     * @param array<string,mixed> $config Match-report display configuration.
     * @return string HTML report or an empty string for no usable records.
     */
    public static function render(
        object $game,
        array $projectEvents,
        array $matchEvents,
        array $substitutions,
        array $config
    ): string {
        // Legacy query extensions can yield nulls and arrays. Normalise all
        // three collections before callbacks or property reads require objects.
        $matchEvents = array_values(array_filter($matchEvents, 'is_object'));
        $substitutions = array_values(array_filter($substitutions, 'is_object'));

        if ($matchEvents === [] && $substitutions === []) {
            return '';
        }

        $eventTypes = [];
        foreach ($projectEvents as $eventType) {
            if (!is_object($eventType)) {
                continue;
            }

            $eventTypeId = self::positiveId($eventType->id ?? null);

            if ($eventTypeId > 0) {
                $eventTypes[$eventTypeId] = $eventType;
            }
        }

        if (!empty($config['use_tabs_events'])) {
            return self::renderTabs($game, $eventTypes, $matchEvents, $substitutions, $config);
        }

        return self::renderEventColumns($game, $eventTypes, $matchEvents, $config, true);
    }

    /**
     * Group match events by type into Bootstrap-accessible report tabs.
     *
     * Adds an optional substitution tab and constructs stable control/panel
     * identifiers for the current fixture. Encodes labels and attributes
     * before composing the tab list and its corresponding content panels.
     *
     * @param object $game Fixture whose report is displayed.
     * @param array<int,object> $eventTypes Valid event types indexed by ID.
     * @param array<int,object> $matchEvents Normalised match event records.
     * @param array<int,object> $substitutions Normalised substitution records.
     * @param array<string,mixed> $config Display options for events and names.
     * @return string Tab navigation and panels, or an empty string.
     */
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
                static fn (object $event): bool => self::positiveId($event->event_type_id ?? null) === $eventTypeId
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

    /**
     * Render home and away event lists in the match report's two-column table.
     *
     * Obtains the fixture project-team identifiers and delegates the actual
     * event formatting to eventList for each side independently.
     *
     * @param object $game Fixture with two project-team identifiers.
     * @param array<int,object> $eventTypes Available event type records.
     * @param array<int,object> $events Match event rows to render.
     * @param array<string,mixed> $config Visible event information options.
     * @param bool $showEventInfo Whether type labels/icons should be included.
     * @return string Table containing each team's event list.
     */
    private static function renderEventColumns(
        object $game,
        array $eventTypes,
        array $events,
        array $config,
        bool $showEventInfo
    ): string {
        $homeId = self::positiveId($game->projectteam1_id ?? null);
        $awayId = self::positiveId($game->projectteam2_id ?? null);

        return '<table class="matchreport table table-borderless mb-0"><tr>'
            . '<td class="list-left"><ul class="list-inline mb-0">'
            . self::eventList($events, $eventTypes, $homeId, $config, $showEventInfo)
            . '</ul></td>'
            . '<td class="list-right"><ul class="list-inline mb-0">'
            . self::eventList($events, $eventTypes, $awayId, $config, $showEventInfo)
            . '</ul></td>'
            . '</tr></table>';
    }

    /**
     * Create the list of event entries for one valid project team.
     *
     * Skips unmatched records and missing/zero team IDs, formats player names
     * through the native formatter, and optionally displays event type,
     * minute, quantity and escaped notice text.
     *
     * @param array<int,object> $events Valid match events for this fixture.
     * @param array<int,object> $eventTypes Event metadata indexed by type ID.
     * @param int $projectTeamId Selected home or away project team.
     * @param array<string,mixed> $config Event display options.
     * @param bool $showEventInfo Whether to include event icons or labels.
     * @return string HTML list items for the selected side.
     */
    private static function eventList(
        array $events,
        array $eventTypes,
        int $projectTeamId,
        array $config,
        bool $showEventInfo
    ): string {
        $output = '';

        if ($projectTeamId <= 0) {
            return '';
        }

        foreach ($events as $event) {
            if (self::positiveId($event->ptid ?? null) !== $projectTeamId) {
                continue;
            }

            $eventType = $eventTypes[self::positiveId($event->event_type_id ?? null)] ?? null;
            $output .= '<li class="list-inline-item d-block">';

            if ($showEventInfo && $eventType) {
                if (!empty($config['show_events_with_icons'])) {
                    $output .= self::eventIcon($eventType);
                } else {
                    $output .= self::escape(Text::_(self::textValue($eventType->name ?? null))) . ' ';
                }
            }

            if (!empty($config['show_event_minute']) && (int) ($event->event_time ?? 0) > 0) {
                $output .= '<strong>' . str_pad((string) ((int) $event->event_time), 2, '0', STR_PAD_LEFT) . '\'</strong> ';
            }

            $name = PersonNameFormatter::format(
                null,
                self::textValue($event->firstname1 ?? null),
                self::textValue($event->nickname1 ?? null),
                self::textValue($event->lastname1 ?? null),
                $config['name_format'] ?? 0
            );
            $output .= $name !== '' ? $name : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON'));

            $details = [];
            if (!empty($config['show_event_sum']) && (float) ($event->event_sum ?? 0) > 0) {
                $details[] = self::escape((string) $event->event_sum);
            }
            if (!empty($config['show_event_notice']) && trim(self::textValue($event->notice ?? null)) !== '') {
                $details[] = self::escape((string) $event->notice);
            }
            if ($details) {
                $output .= ' (' . implode(' | ', $details) . ')';
            }

            $output .= '</li>';
        }

        return $output;
    }

    /**
     * Show the two teams' substitutions in side-by-side HTML columns.
     *
     * Uses the fixture's project-team IDs to split inbound and outbound
     * player changes without invoking legacy component presentation helpers.
     *
     * @param object $game Fixture with home and away project-team IDs.
     * @param array<int,object> $substitutions Valid substitution records.
     * @param array<string,mixed> $config Player naming configuration.
     * @return string HTML table containing both substitution lists.
     */
    private static function renderSubstitutionColumns(object $game, array $substitutions, array $config): string
    {
        $homeId = self::positiveId($game->projectteam1_id ?? null);
        $awayId = self::positiveId($game->projectteam2_id ?? null);

        return '<table class="matchreport table table-borderless mb-0"><tr>'
            . '<td class="list"><ul class="list-unstyled mb-0">'
            . self::substitutionList($substitutions, $homeId, $config)
            . '</ul></td>'
            . '<td class="list"><ul class="list-unstyled mb-0">'
            . self::substitutionList($substitutions, $awayId, $config)
            . '</ul></td>'
            . '</tr></table>';
    }

    /**
     * Format the substitution records for one project team.
     *
     * Converts incoming and outgoing player names with the native name
     * formatter, escapes timestamps and translated labels and appends any
     * available playing-position descriptions.
     *
     * @param array<int,object> $substitutions Substitutions in this match.
     * @param int $projectTeamId Team for which changes are displayed.
     * @param array<string,mixed> $config Player name format options.
     * @return string HTML list entries for matching substitutions.
     */
    private static function substitutionList(array $substitutions, int $projectTeamId, array $config): string
    {
        $output = '';

        if ($projectTeamId <= 0) {
            return '';
        }

        foreach ($substitutions as $substitution) {
            if (self::positiveId($substitution->ptid ?? null) !== $projectTeamId) {
                continue;
            }

            $incoming = PersonNameFormatter::format(
                null,
                self::textValue($substitution->firstname ?? null),
                self::textValue($substitution->nickname ?? null),
                self::textValue($substitution->lastname ?? null),
                $config['name_format'] ?? 0
            );
            $outgoing = PersonNameFormatter::format(
                null,
                self::textValue($substitution->out_firstname ?? null),
                self::textValue($substitution->out_nickname ?? null),
                self::textValue($substitution->out_lastname ?? null),
                $config['name_format'] ?? 0
            );

            $output .= '<li class="mb-2">'
                . '<strong>' . self::escape(self::textValue($substitution->in_out_time ?? null)) . '. '
                . self::escape(Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTION_MINUTE')) . '</strong><br>'
                . '<span aria-hidden="true">↓</span> ' . ($outgoing !== '' ? $outgoing : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON')))
                . self::position(self::textValue($substitution->out_position ?? null)) . '<br>'
                . '<span aria-hidden="true">↑</span> ' . ($incoming !== '' ? $incoming : self::escape(Text::_('COM_SPORTSMANAGEMENT_UNKNOWN_PERSON')))
                . self::position(self::textValue($substitution->in_position ?? null))
                . '</li>';
        }

        return $output;
    }

    /**
     * Build a translated event-type label for a report tab.
     *
     * Includes the optional event icon when configured, and always HTML
     * encodes the translated event name used in the tab control.
     *
     * @param object $eventType Stored event type with name and icon fields.
     * @param array<string,mixed> $config Icon display setting.
     * @return string HTML label for a match-event tab.
     */
    private static function eventLabel(object $eventType, array $config): string
    {
        if (!empty($config['show_events_with_icons'])) {
            return self::eventIcon($eventType) . ' ' . self::escape(Text::_(self::textValue($eventType->name ?? null)));
        }

        return self::escape(Text::_(self::textValue($eventType->name ?? null)));
    }

    /**
     * Render an optional event-type image with a translated accessible label.
     *
     * Resolves relative icon paths against the Joomla site root, preserves
     * configured HTTP(S) images and escapes URL and alt-text attributes.
     *
     * @param object $eventType Event type with optional configured icon URL.
     * @return string HTML image element or an empty string for no icon.
     */
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
            . self::escape(Text::_(self::textValue($eventType->name ?? null))) . '" width="20" height="20">';
    }

    /**
     * Append a player's translated playing position to a substitution.
     *
     * Skips blank positions and HTML-escapes the resulting translated text.
     *
     * @param string $position Stored position identifier or label.
     * @return string Parenthesised position suffix, or an empty string.
     */
    private static function position(string $position): string
    {
        $position = trim($position);

        return $position !== '' ? ' (' . self::escape(Text::_($position)) . ')' : '';
    }

    /**
     * Validate a complete positive decimal identifier from a legacy record.
     *
     * Accepts zero-padded numeric strings and integers without converting
     * malformed strings, booleans or arrays into another team's identifier.
     * @param mixed $value Raw event or project-team identifier.
     * @return int Positive ID, or zero for invalid input.
     */
    private static function positiveId(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }

        if (!is_string($value) || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $id = filter_var(ltrim($value, '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Normalise optional legacy display fields without unsafe string casts.
     * Accepts text and numeric values; structured values become empty text.
     * @param mixed $value Imported display field.
     * @return string Safe scalar text for further formatting.
     */
    private static function textValue(mixed $value): string
    {
        return is_string($value) || is_int($value) || is_float($value) ? (string) $value : '';
    }

    /**
     * Escape display values for safe inclusion in match report HTML.
     *
     * Encodes double/single quotes and markup delimiters using UTF-8 so
     * event labels, comments and generated attributes remain plain text.
     *
     * @param mixed $value Scalar display value to encode.
     * @return string HTML-escaped text.
     */
    private static function escape(mixed $value): string
    {
        return htmlspecialchars(self::textValue($value), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
