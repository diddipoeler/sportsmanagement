<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Import Inline Hockey schedules, results and playground metadata natively. */
final class InlineHockeyMatchImportService
{
    private const SPORTS_TYPE = 'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY';

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly InlineHockeyApiClient $api,
        private readonly InlineHockeyProjectService $projects
    ) {
    }

    public function importMatches(
        int $projectId,
        string $matchLink = '',
        string $username = '',
        string $password = ''
    ): int {
        if ($projectId <= 0) {
            return 0;
        }

        $matchLink = trim($matchLink);

        if ($matchLink === '') {
            $matchLink = $this->projects->getMatchLink($projectId);
        }

        if ($matchLink === '') {
            throw new \RuntimeException('Inline-Hockey match URL is not configured for this project.');
        }

        $seasonId = $this->projectSeasonId($projectId);

        if ($seasonId <= 0) {
            throw new \RuntimeException('Inline-Hockey project has no season assigned.');
        }

        $sportsTypeId = $this->ensureSportsType();
        $roundId = $this->ensureRound($projectId);

        if ($sportsTypeId <= 0 || $roundId <= 0) {
            throw new \RuntimeException('Inline-Hockey import prerequisites could not be created.');
        }

        $firstPage = $this->api->fetchJson($matchLink, $username, $password);
        $pages = max(1, (int) ($firstPage->pages ?? 1));
        $changed = 0;
        $syncedPlaygrounds = [];

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

                try {
                    $home = $this->prepareSide(
                        $externalMatch->home_team ?? null,
                        $projectId,
                        $seasonId,
                        $sportsTypeId
                    );
                    $away = $this->prepareSide(
                        $externalMatch->away_team ?? null,
                        $projectId,
                        $seasonId,
                        $sportsTypeId
                    );

                    if ($home['project_team_id'] <= 0 || $away['project_team_id'] <= 0) {
                        continue;
                    }

                    if ($this->upsertMatch($externalMatch, $projectId, $roundId, $home, $away)) {
                        $changed++;
                    }

                    foreach ([$home, $away] as $side) {
                        $clubId = $side['club_id'];

                        if ($clubId <= 0 || isset($syncedPlaygrounds[$clubId])) {
                            continue;
                        }

                        $syncedPlaygrounds[$clubId] = true;
                        $this->syncPlayground(
                            $clubId,
                            $side['club_info_href'],
                            $username,
                            $password
                        );
                    }
                } catch (\Throwable $exception) {
                    Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->refreshRoundDates($roundId);

        return $changed;
    }

    /**
     * @return array{club_id:int,team_id:int,project_team_id:int,club_info_href:string}
     */
    private function prepareSide(
        mixed $side,
        int $projectId,
        int $seasonId,
        int $sportsTypeId
    ): array {
        if (!is_object($side)) {
            return ['club_id' => 0, 'team_id' => 0, 'project_team_id' => 0, 'club_info_href' => ''];
        }

        $club = is_object($side->club ?? null) ? $side->club : null;
        $clubId = (int) ($club->id ?? 0);
        $clubName = trim((string) ($club->name ?? ''));
        $website = is_object($club->website ?? null) ? trim((string) ($club->website->url ?? '')) : '';
        $clubInfoHref = is_object($club->_links ?? null) && is_object($club->_links->self ?? null)
            ? trim((string) ($club->_links->self->href ?? ''))
            : '';

        if ($clubId > 0 && $clubName !== '') {
            $this->ensureClub($clubId, $clubName, $website);
        }

        $teamId = (int) ($side->team_id ?? 0);
        $teamName = trim((string) ($side->full_name ?? ''));
        $teamInfo = trim((string) ($side->alternate_team_name ?? ''));

        if ($teamId > 0 && $clubId > 0 && $teamName !== '') {
            $this->ensureTeam($teamId, $clubId, $teamName, $teamInfo, $sportsTypeId);
        } elseif ($clubId > 0 && $teamInfo !== '') {
            $teamId = $this->findTeamByInfo($clubId, $teamInfo);
        }

        $projectTeamId = $teamId > 0
            ? $this->projects->ensureProjectTeam($teamId, $projectId, $seasonId)
            : 0;

        return [
            'club_id' => $clubId,
            'team_id' => $teamId,
            'project_team_id' => $projectTeamId,
            'club_info_href' => $clubInfoHref,
        ];
    }

    /**
     * @param array{club_id:int,team_id:int,project_team_id:int,club_info_href:string} $home
     * @param array{club_id:int,team_id:int,project_team_id:int,club_info_href:string} $away
     */
    private function upsertMatch(
        object $externalMatch,
        int $projectId,
        int $roundId,
        array $home,
        array $away
    ): bool {
        $externalId = (int) ($externalMatch->id ?? 0);

        if ($externalId <= 0) {
            return false;
        }

        $record = (object) [
            'import_match_id' => $externalId,
            'match_number' => $externalId,
            'round_id' => $roundId,
            'projectteam1_id' => $home['project_team_id'],
            'projectteam2_id' => $away['project_team_id'],
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

        $existingId = $this->findMatchId($projectId, $externalId);

        if ($existingId > 0) {
            $record->id = $existingId;
            $this->db->updateObject('#__sportsmanagement_match', $record, 'id');
        } else {
            $this->db->insertObject('#__sportsmanagement_match', $record);
        }

        return true;
    }

    private function projectSeasonId(int $projectId): int
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('season_id'))
            ->from($this->db->quoteName('#__sportsmanagement_project'))
            ->where($this->db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    private function ensureRound(int $projectId): int
    {
        $roundCode = '1';
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_round'))
            ->where($this->db->quoteName('project_id') . ' = :projectId')
            ->where($this->db->quoteName('roundcode') . ' = :roundCode')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':roundCode', $roundCode, ParameterType::STRING);
        $this->db->setQuery($query, 0, 1);
        $roundId = (int) ($this->db->loadResult() ?: 0);

        if ($roundId > 0) {
            return $roundId;
        }

        $record = (object) [
            'roundcode' => 1,
            'name' => '1.Spieltag',
            'project_id' => $projectId,
        ];
        $this->db->insertObject('#__sportsmanagement_round', $record);

        return (int) $this->db->insertid();
    }

    private function ensureSportsType(): int
    {
        $name = self::SPORTS_TYPE;
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->db->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->db->setQuery($query, 0, 1);
        $sportsTypeId = (int) ($this->db->loadResult() ?: 0);

        if ($sportsTypeId > 0) {
            return $sportsTypeId;
        }

        $record = (object) ['name' => $name];
        $this->db->insertObject('#__sportsmanagement_sports_type', $record);

        return (int) $this->db->insertid();
    }

    private function ensureClub(int $clubId, string $name, string $website): void
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_club'))
            ->where($this->db->quoteName('id') . ' = :clubId')
            ->bind(':clubId', $clubId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $record = (object) [
            'id' => $clubId,
            'name' => $name,
            'country' => 'DEU',
            'website' => $website,
            'alias' => OutputFilter::stringURLSafe($name),
        ];
        $this->db->insertObject('#__sportsmanagement_club', $record);
    }

    private function ensureTeam(
        int $teamId,
        int $clubId,
        string $name,
        string $info,
        int $sportsTypeId
    ): void {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_team'))
            ->where($this->db->quoteName('id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $record = (object) [
            'id' => $teamId,
            'club_id' => $clubId,
            'name' => $name,
            'short_name' => $name,
            'middle_name' => $name,
            'info' => $info,
            'sports_type_id' => $sportsTypeId,
            'alias' => OutputFilter::stringURLSafe($name),
        ];
        $this->db->insertObject('#__sportsmanagement_team', $record);
    }

    private function findTeamByInfo(int $clubId, string $info): int
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_team'))
            ->where($this->db->quoteName('club_id') . ' = :clubId')
            ->where($this->db->quoteName('info') . ' = :info')
            ->bind(':clubId', $clubId, ParameterType::INTEGER)
            ->bind(':info', $info, ParameterType::STRING);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    private function findMatchId(int $projectId, int $externalId): int
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('m.id'))
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

        return (int) ($this->db->loadResult() ?: 0);
    }

    private function syncPlayground(
        int $clubId,
        string $clubInfoHref,
        string $username,
        string $password
    ): void {
        if ($clubId <= 0 || $clubInfoHref === '') {
            return;
        }

        $url = preg_match('#^https?://#i', $clubInfoHref)
            ? $clubInfoHref
            : 'https://www.ishd.de/' . ltrim($clubInfoHref, '/');

        if (!str_ends_with(strtolower($url), '.json')) {
            $url .= '.json';
        }

        try {
            $payload = $this->api->fetchJson($url, $username, $password);
        } catch (\Throwable $exception) {
            Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
            return;
        }

        $venueWrapper = is_object($payload->venue ?? null) ? $payload->venue : null;
        $venue = $venueWrapper && is_object($venueWrapper->venue ?? null) ? $venueWrapper->venue : null;

        if (!$venue) {
            return;
        }

        $playgroundId = (int) ($venue->id ?? 0);

        if ($playgroundId <= 0) {
            return;
        }

        $club = (object) [
            'id' => $clubId,
            'standard_playground' => $playgroundId,
        ];
        $this->db->updateObject('#__sportsmanagement_club', $club, 'id');

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_playground'))
            ->where($this->db->quoteName('id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        if ((int) $this->db->loadResult() > 0) {
            return;
        }

        $address = is_object($venue->address ?? null) ? $venue->address : null;
        $shortName = trim((string) ($venue->name ?? ''));
        $name = trim((string) ($venue->full_name ?? '')) ?: $shortName;
        $record = (object) [
            'id' => $playgroundId,
            'club_id' => $clubId,
            'name' => $name,
            'short_name' => $shortName !== '' ? $shortName : $name,
            'address' => trim((string) ($address->street ?? '')),
            'zipcode' => trim((string) ($address->postal_code ?? '')),
            'city' => trim((string) ($address->city ?? '')),
            'country' => 'DEU',
            'alias' => OutputFilter::stringURLSafe($name !== '' ? $name : (string) $playgroundId),
        ];
        $this->db->insertObject('#__sportsmanagement_playground', $record);
    }

    private function refreshRoundDates(int $roundId): void
    {
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
            return;
        }

        $round = (object) [
            'id' => $roundId,
            'round_date_first' => substr((string) $dates->first_date, 0, 10),
            'round_date_last' => substr((string) $dates->last_date, 0, 10),
        ];
        $this->db->updateObject('#__sportsmanagement_round', $round, 'id');
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
