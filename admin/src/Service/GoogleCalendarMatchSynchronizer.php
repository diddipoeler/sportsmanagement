<?php
/**
 * Google Calendar synchronisation service for SportsManagement matches.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleCalendarService;
use Google\Service\Calendar\Event as GoogleCalendarEvent;
use Google\Service\Calendar\EventDateTime as GoogleCalendarEventDateTime;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use RuntimeException;

/**
 * Synchronises selected SportsManagement matches with a configured Google Calendar.
 *
 * The Google API client remains an optional runtime dependency. When it is not
 * installed, callers receive a controlled exception instead of a fatal error.
 */
final class GoogleCalendarMatchSynchronizer
{
    private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar';

    /**
     * Initialise the match synchronisation service with the database connection used to load match and calendar records.
     * @param DatabaseInterface $db SportsManagement database connection.
     */
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * Validate the requested project, calendar and match identifiers, load the optional Google API client and authenticate using the stored OAuth refresh credentials. Update existing calendar events or insert new ones and persist their returned identifiers.
     * @param array<int|string> $matchIds Selected match identifiers.
     * @param int $projectId Selected project identifier.
     * @param int $calendarId Local calendar configuration identifier.
     * @return int Number of matches synchronised.
     * @throws RuntimeException If required selections, dependencies, credentials or event identifiers are missing.
     */
    public function synchronize(array $matchIds, int $projectId, int $calendarId): int
    {
        $matchIds = $this->normaliseIds($matchIds);

        if ($projectId <= 0) {
            throw new RuntimeException('No SportsManagement project was selected.');
        }

        if ($calendarId <= 0) {
            throw new RuntimeException('No Google Calendar was selected.');
        }

        if ($matchIds === []) {
            throw new RuntimeException('No matches were selected for Google Calendar synchronisation.');
        }

        $this->loadGoogleClient();
        $calendar = $this->loadCalendar($calendarId);
        $project = $this->loadProject($projectId);
        $params = new Registry();
        $params->loadString((string) ($calendar->params ?? ''));

        $clientId = trim((string) $params->get('client-id', ''));
        $clientSecret = trim((string) $params->get('client-secret', ''));
        $refreshToken = trim((string) $params->get('refreshToken', ''));
        $googleCalendarId = trim((string) $params->get('calendarId', (string) ($calendar->calendar_id ?? '')));

        if ($clientId === '' || $clientSecret === '' || $refreshToken === '' || $googleCalendarId === '') {
            throw new RuntimeException('Google Calendar credentials are incomplete. Please reconnect the calendar first.');
        }

        $client = new GoogleClient();
        $client->setApplicationName('JSMCalendar');
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes([self::CALENDAR_SCOPE]);
        $client->setAccessType('offline');

        if (method_exists($client, 'fetchAccessTokenWithRefreshToken')) {
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);

            if (is_array($token) && !empty($token['error'])) {
                throw new RuntimeException(
                    'Google Calendar authentication failed: ' . (string) ($token['error_description'] ?? $token['error'])
                );
            }
        } elseif (method_exists($client, 'refreshToken')) {
            // Compatibility with older google/apiclient 2.x releases.
            $client->refreshToken($refreshToken);
        } else {
            throw new RuntimeException('Installed Google API client cannot refresh OAuth access tokens.');
        }

        $calendarService = new GoogleCalendarService($client);
        $matches = $this->loadMatches($matchIds, $projectId, $project);
        $updated = 0;

        foreach ($matches as $match) {
            $event = $this->createEvent($match, $project);

            if (!empty($match->gcal_event_id)) {
                $calendarService->events->update($googleCalendarId, (string) $match->gcal_event_id, $event);
            } else {
                $createdEvent = $calendarService->events->insert($googleCalendarId, $event);
                $eventId = trim((string) $createdEvent->getId());

                if ($eventId === '') {
                    // Without an event ID a later synchronization would insert
                    // the same match again rather than updating this event.
                    throw new RuntimeException(
                        'Google Calendar returned no event ID for match ' . (int) $match->id . '.'
                    );
                }

                $this->storeEventId((int) $match->id, $eventId);
            }

            ++$updated;
        }

        return $updated;
    }

    /**
     * Load the bundled Google API autoloader when needed and verify that the client and calendar event classes required for synchronisation are available.
     * @return void
     * @throws RuntimeException If a required Google API class is unavailable.
     */
    private function loadGoogleClient(): void
    {
        $autoload = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/libraries/google-php/vendor/autoload.php';

        if (!class_exists(GoogleClient::class) && is_file($autoload)) {
            require_once $autoload;
        }

        foreach (
            [
                GoogleClient::class,
                GoogleCalendarService::class,
                GoogleCalendarEvent::class,
                GoogleCalendarEventDateTime::class,
            ] as $class
        ) {
            if (!class_exists($class)) {
                throw new RuntimeException(
                    'Google API PHP Client is not installed. Install google/apiclient before using calendar synchronisation.'
                );
            }
        }
    }

    /**
     * Retrieve the selected local calendar configuration, including its remote calendar identifier and stored parameters.
     * @param int $calendarId Local calendar configuration identifier.
     * @return object Calendar configuration record.
     * @throws RuntimeException If the selected configuration does not exist.
     */
    private function loadCalendar(int $calendarId): object
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('calendar_id'),
                $this->db->quoteName('params'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_gcalendar'))
            ->where($this->db->quoteName('id') . ' = :calendarId')
            ->bind(':calendarId', $calendarId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);
        $calendar = $this->db->loadObject();

        if (!$calendar) {
            throw new RuntimeException('The selected Google Calendar configuration no longer exists.');
        }

        return $calendar;
    }

    /**
     * Load the project's timezone, match duration and favourite-team synchronisation settings.
     * @param int $projectId Project identifier.
     * @return object Project configuration record.
     * @throws RuntimeException If the project does not exist.
     */
    private function loadProject(int $projectId): object
    {
        $query = $this->db->createQuery()
            ->select([
                $this->db->quoteName('id'),
                $this->db->quoteName('timezone'),
                $this->db->quoteName('game_regular_time'),
                $this->db->quoteName('halftime'),
                $this->db->quoteName('gcalendar_use_fav_teams'),
                $this->db->quoteName('fav_team'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_project'))
            ->where($this->db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);
        $project = $this->db->loadObject();

        if (!$project) {
            throw new RuntimeException('The selected SportsManagement project no longer exists.');
        }

        return $project;
    }

    /**
     * Load published matches matching the selected identifiers and project, including team names, venue data and existing calendar event identifiers. Apply the favourite-team restriction when configured, returning no matches if that restriction has no valid team identifiers.
     * @param array<int> $matchIds Validated match identifiers.
     * @param int $projectId Project identifier.
     * @param object $project Project synchronisation settings.
     * @return array<object> Matches ordered by date and match number.
     */
    private function loadMatches(array $matchIds, int $projectId, object $project): array
    {
        $query = $this->db->createQuery()
            ->select([
                'm.id',
                'm.match_date',
                'm.team1_result',
                'm.team2_result',
                'm.gcal_event_id',
                'm.cancel',
                'm.cancel_reason',
                'playground.name AS playground_name',
                'playground.city AS playground_city',
                'playground.address AS playground_address',
                't1.name AS hometeam',
                't2.name AS awayteam',
                'r.name AS roundname',
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_round', 'r') . ' ON r.id = m.round_id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON m.projectteam1_id = pt1.id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON m.projectteam2_id = pt2.id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON st1.id = pt1.team_id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON st2.id = pt2.team_id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON t1.id = st1.team_id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON t2.id = st2.team_id')
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON playground.id = m.playground_id')
            ->where('m.published = 1')
            ->whereIn($this->db->quoteName('m.id'), $matchIds, ParameterType::INTEGER)
            ->where('r.project_id = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order('m.match_date ASC, m.match_number ASC');

        if (!empty($project->gcalendar_use_fav_teams)) {
            $favoriteTeamIds = $this->normaliseIds(
                preg_split('/\s*,\s*/', (string) ($project->fav_team ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
            );

            // The favorites-only option must not silently import every match
            // when a project has no favorite teams configured.
            if ($favoriteTeamIds === []) {
                return [];
            }

            $favoriteTeams = implode(',', $favoriteTeamIds);
            $query->where('(t1.id IN (' . $favoriteTeams . ') OR t2.id IN (' . $favoriteTeams . '))');
        }

        $this->db->setQuery($query);

        return (array) $this->db->loadObjectList();
    }

    /**
     * Build a Google Calendar event with team names, result or cancellation information, round description and available venue details. Derive start and end times from the match date, project timezone and configured duration, with UTC and one-hour fallbacks.
     * @param object $match Match record.
     * @param object $project Project time settings.
     * @return GoogleCalendarEvent Prepared calendar event.
     */
    private function createEvent(object $match, object $project): GoogleCalendarEvent
    {
        $detail = '';

        if ((int) ($match->cancel ?? 0) === 1) {
            $reason = trim((string) ($match->cancel_reason ?? ''));
            $detail = $reason !== '' ? ' (' . $reason . ')' : '';
        } elseif ($match->team1_result !== null && $match->team2_result !== null) {
            $detail = ' (' . $match->team1_result . ':' . $match->team2_result . ')';
        }

        $event = new GoogleCalendarEvent();
        $event->setSummary(trim((string) $match->hometeam) . ' - ' . trim((string) $match->awayteam) . $detail);
        $event->setDescription((string) ($match->roundname ?? ''));

        $location = array_filter([
            trim((string) ($match->playground_name ?? '')),
            trim((string) ($match->playground_city ?? '')),
            trim((string) ($match->playground_address ?? '')),
        ], static fn (string $value): bool => $value !== '');

        if ($location !== []) {
            $event->setLocation(implode(', ', $location));
        }

        $timezone = trim((string) ($project->timezone ?? '')) ?: 'UTC';

        try {
            $timezoneObject = new DateTimeZone($timezone);
        } catch (\Throwable) {
            $timezone = 'UTC';
            $timezoneObject = new DateTimeZone($timezone);
        }

        $startDate = new DateTimeImmutable((string) $match->match_date, $timezoneObject);
        $duration = (int) ($project->game_regular_time ?? 0) + (int) ($project->halftime ?? 0);

        // Google Calendar requires an end later than the start. Old projects
        // may have no game duration configured; use a one-hour fallback.
        if ($duration <= 0) {
            $duration = 60;
        }

        $endDate = $startDate->add(new DateInterval('PT' . $duration . 'M'));

        $start = new GoogleCalendarEventDateTime();
        $start->setDateTime($startDate->format(DATE_ATOM));
        $start->setTimeZone($timezone);
        $event->setStart($start);

        $end = new GoogleCalendarEventDateTime();
        $end->setDateTime($endDate->format(DATE_ATOM));
        $end->setTimeZone($timezone);
        $event->setEnd($end);

        return $event;
    }

    /**
     * Persist the remote event identifier against the local match so later synchronisations can update that event.
     * @param int $matchId Local match identifier.
     * @param string $eventId Remote calendar event identifier.
     * @return void
     */
    private function storeEventId(int $matchId, string $eventId): void
    {
        $row = (object) [
            'id' => $matchId,
            'gcal_event_id' => $eventId,
        ];

        $this->db->updateObject('#__sportsmanagement_match', $row, 'id');
    }

    /**
     * Accept positive integer identifiers and canonical positive decimal strings within the PHP integer range. Discard invalid entries and remove duplicates before returning a sequential list.
     * @param array<int|string> $ids Candidate identifiers.
     * @return array<int> Unique valid identifiers.
     */
    private function normaliseIds(array $ids): array
    {
        $normalised = [];

        foreach ($ids as $id) {
            if (!is_int($id) && (!is_string($id) || !preg_match('/^[1-9][0-9]*$/D', $id))) {
                continue;
            }

            $validated = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($validated !== false) {
                $normalised[$validated] = $validated;
            }
        }

        return array_values($normalised);
    }
}
