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

    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * @param array<int|string> $matchIds
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

                if ($eventId !== '') {
                    $this->storeEventId((int) $match->id, $eventId);
                }
            }

            ++$updated;
        }

        return $updated;
    }

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
     * @param array<int> $matchIds
     * @return array<object>
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
            ->where('m.id IN (' . implode(',', $matchIds) . ')')
            ->where('r.project_id = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order('m.match_date ASC, m.match_number ASC');

        if (!empty($project->gcalendar_use_fav_teams)) {
            $favoriteTeamIds = $this->normaliseIds(
                preg_split('/\s*,\s*/', (string) ($project->fav_team ?? ''), -1, PREG_SPLIT_NO_EMPTY) ?: []
            );

            if ($favoriteTeamIds !== []) {
                $favoriteTeams = implode(',', $favoriteTeamIds);
                $query->where('(t1.id IN (' . $favoriteTeams . ') OR t2.id IN (' . $favoriteTeams . '))');
            }
        }

        $this->db->setQuery($query);

        return (array) $this->db->loadObjectList();
    }

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
        $duration = max(0, (int) ($project->game_regular_time ?? 0) + (int) ($project->halftime ?? 0));
        $endDate = $duration > 0 ? $startDate->add(new DateInterval('PT' . $duration . 'M')) : $startDate;

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

    private function storeEventId(int $matchId, string $eventId): void
    {
        $row = (object) [
            'id' => $matchId,
            'gcal_event_id' => $eventId,
        ];

        $this->db->updateObject('#__sportsmanagement_match', $row, 'id');
    }

    /**
     * @param array<int|string> $ids
     * @return array<int>
     */
    private function normaliseIds(array $ids): array
    {
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, static fn (int $id): bool => $id > 0);

        return array_values(array_unique($ids));
    }
}
