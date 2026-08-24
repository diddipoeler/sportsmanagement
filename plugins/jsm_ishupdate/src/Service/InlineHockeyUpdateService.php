<?php
namespace Diddipoeler\Plugin\System\SportsmanagementIshupdate\Service;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyApiClient;
use Diddipoeler\Component\SportsManagement\Site\Service\InlineHockeyProjectService;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Refresh existing SportsManagement matches from the configured ISHD schedule.
 *
 * This service intentionally updates only matches that already belong to the
 * selected project. Creating clubs, teams, rounds and playgrounds remains in
 * the manual Inline Hockey importer until those workflows are migrated too.
 */
final class InlineHockeyUpdateService
{
    private readonly InlineHockeyProjectService $projects;
    private readonly InlineHockeyApiClient $api;

    public function __construct(private readonly DatabaseInterface $db)
    {
        $this->projects = new InlineHockeyProjectService($db);
        $this->api = new InlineHockeyApiClient();
    }

    public function updateProject(int $projectId, string $username = '', string $password = ''): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $matchLink = $this->projects->getMatchLink($projectId);

        if ($matchLink === '') {
            return 0;
        }

        $firstPage = $this->api->fetchJson($matchLink, $username, $password);
        $pages = max(1, (int) ($firstPage->pages ?? 1));
        $updated = 0;
        $roundIds = [];

        for ($page = 1; $page <= $pages; $page++) {
            $payload = $page === 1
                ? $firstPage
                : $this->api->fetchJson($this->api->pageUrl($matchLink, $page), $username, $password);
            $schedule = $payload->_embedded->schedule ?? [];

            if (!is_iterable($schedule)) {
                continue;
            }

            foreach ($schedule as $externalMatch) {
                if (!is_object($externalMatch)) {
                    continue;
                }

                $externalId = (int) ($externalMatch->id ?? 0);
                $existing = $externalId > 0 ? $this->findMatch($projectId, $externalId) : null;

                if ($existing === null) {
                    continue;
                }

                $record = (object) [
                    'id' => (int) $existing->id,
                    'import_match_id' => $externalId,
                    'match_number' => $externalId,
                    'published' => 1,
                ];

                $dateTime = trim((string) ($externalMatch->date_time ?? ''));

                if ($dateTime !== '') {
                    [$matchDate, $timestamp] = $this->normaliseDate($dateTime);
                    $record->match_date = $matchDate;
                    $record->match_timestamp = $timestamp;
                }

                $homeGoals = $externalMatch->home_goals ?? null;
                $awayGoals = $externalMatch->away_goals ?? null;

                if (is_numeric($homeGoals) && is_numeric($awayGoals)) {
                    $record->team1_result = (int) $homeGoals;
                    $record->team2_result = (int) $awayGoals;
                    $record->match_result_type = 0;

                    if (!empty($externalMatch->is_after_overtime)) {
                        $record->team1_result_ot = (int) $homeGoals;
                        $record->team2_result_ot = (int) $awayGoals;
                        $record->match_result_type = 1;
                    }

                    if (!empty($externalMatch->is_after_penalty_shoot_out)) {
                        $record->team1_result_so = (int) $homeGoals;
                        $record->team2_result_so = (int) $awayGoals;
                        $record->match_result_type = 2;
                    }
                }

                $this->db->updateObject('#__sportsmanagement_match', $record, 'id');
                $roundIds[(int) $existing->round_id] = true;
                $updated++;
            }
        }

        $this->refreshRoundDates(array_keys($roundIds));

        return $updated;
    }

    private function findMatch(int $projectId, int $externalId): ?object
    {
        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('m.id'),
                $this->db->quoteName('m.round_id'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $this->db->quoteName('r.id') . ' = ' . $this->db->quoteName('m.round_id')
            )
            ->where($this->db->quoteName('r.project_id') . ' = :projectId')
            ->where($this->db->quoteName('m.import_match_id') . ' = :externalId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':externalId', $externalId, ParameterType::INTEGER);

        $this->db->setQuery($query, 0, 1);

        return $this->db->loadObject() ?: null;
    }

    /** @param list<int> $roundIds */
    private function refreshRoundDates(array $roundIds): void
    {
        foreach ($roundIds as $roundId) {
            if ($roundId <= 0) {
                continue;
            }

            $query = $this->db->getQuery(true)
                ->select([
                    'MIN(' . $this->db->quoteName('match_date') . ') AS first_date',
                    'MAX(' . $this->db->quoteName('match_date') . ') AS last_date',
                ])
                ->from($this->db->quoteName('#__sportsmanagement_match'))
                ->where($this->db->quoteName('round_id') . ' = :roundId')
                ->bind(':roundId', $roundId, ParameterType::INTEGER);
            $this->db->setQuery($query);
            $dates = $this->db->loadObject();

            if (!$dates || empty($dates->first_date) || empty($dates->last_date)) {
                continue;
            }

            $round = (object) [
                'id' => $roundId,
                'round_date_first' => substr((string) $dates->first_date, 0, 10),
                'round_date_last' => substr((string) $dates->last_date, 0, 10),
            ];
            $this->db->updateObject('#__sportsmanagement_round', $round, 'id');
        }
    }

    /** @return array{0:string,1:int} */
    private function normaliseDate(string $dateTime): array
    {
        try {
            $date = new \DateTimeImmutable($dateTime);

            return [$date->format('Y-m-d H:i:s'), $date->getTimestamp()];
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Inline-Hockey match date is invalid: ' . $dateTime, 0, $exception);
        }
    }
}
