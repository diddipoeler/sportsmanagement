<?php
/**
 * Native Joomla 5/6 importer for Inline Hockey schedules, results and playground data.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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

    /**
     * Initialise the schedule importer with its database, external API client and project relation service.
     * @param DatabaseInterface $db Target database connection.
     * @param InlineHockeyApiClient $api External schedule client.
     * @param InlineHockeyProjectService $projects Project relation service.
     */
    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly InlineHockeyApiClient $api,
        private readonly InlineHockeyProjectService $projects
    ) {
    }

    /**
     * Resolve the project season and schedule URL, ensure the sport and round prerequisites, then process the paginated external schedules. Prepare team relations, import match records and synchronise venue information before refreshing the round dates.
     * @param int $projectId Target project identifier.
     * @param string $matchLink Optional schedule URL overriding project configuration.
     * @param string $username API username.
     * @param string $password API password.
     * @return int Number of matches reported as changed.
     * @throws \RuntimeException If required import prerequisites are missing.
     */
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
        $pages = $this->api->pageCount($firstPage->pages ?? 1);
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
     * Resolve an external match side to club, team and project-team identifiers. Create missing club and team records when the supplied identifiers and names permit it, or use the alternate team information for lookup.
     * @param mixed $side External team-side payload.
     * @param int $projectId Target project identifier.
     * @param int $seasonId Season identifier.
     * @param int $sportsTypeId Sport type identifier.
     * @return array{club_id:int,team_id:int,project_team_id:int,club_info_href:string} Resolved side and club information URL.
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
        $clubId = $this->api->positiveId($club->id ?? null);
        $clubName = trim((string) ($club->name ?? ''));
        $website = is_object($club->website ?? null) ? trim((string) ($club->website->url ?? '')) : '';
        $clubInfoHref = is_object($club->_links ?? null) && is_object($club->_links->self ?? null)
            ? trim((string) ($club->_links->self->href ?? ''))
            : '';

        if ($clubId > 0 && $clubName !== '') {
            $this->ensureClub($clubId, $clubName, $website);
        }

        $teamId = $this->api->positiveId($side->team_id ?? null);
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
     * Build a match record from the external identifier, mapped participants, date and numeric scores, including overtime and shootout result types. Update the existing project-scoped import record or insert a new one.
     * @param object $externalMatch External match payload.
     * @param int $projectId Target project identifier.
     * @param int $roundId Target round identifier.
     * @param array{club_id:int,team_id:int,project_team_id:int,club_info_href:string} $home Resolved home side.
     * @param array{club_id:int,team_id:int,project_team_id:int,club_info_href:string} $away Resolved away side.
     * @return bool False for an invalid external identifier, otherwise true after issuing the database write.
     */
    private function upsertMatch(
        object $externalMatch,
        int $projectId,
        int $roundId,
        array $home,
        array $away
    ): bool {
        $externalId = $this->api->positiveId($externalMatch->id ?? null);

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

    /**
     * Look up the season identifier assigned to the target project.
     * @param int $projectId Project identifier.
     * @return int Season identifier or zero.
     */
    private function projectSeasonId(int $projectId): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('season_id'))
            ->from($this->db->quoteName('#__sportsmanagement_project'))
            ->where($this->db->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    /**
     * Find the project's round with code one, creating the default first matchday when no matching round exists.
     * @param int $projectId Project identifier.
     * @return int Existing or newly inserted round identifier.
     */
    private function ensureRound(int $projectId): int
    {
        $roundCode = '1';
        $query = $this->db->createQuery()
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

    /**
     * Find the skater hockey sport type by its translation key and insert the type when absent.
     * @return int Existing or newly inserted sport type identifier.
     */
    private function ensureSportsType(): int
    {
        $name = self::SPORTS_TYPE;
        $query = $this->db->createQuery()
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

    /**
     * Reuse a club with the supplied external primary key or insert a new German club with its name, website and generated URL alias.
     * @param int $clubId External club identifier used as primary key.
     * @param string $name Club name.
     * @param string $website Club website.
     * @return void
     */
    private function ensureClub(int $clubId, string $name, string $website): void
    {
        $query = $this->db->createQuery()
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

    /**
     * Reuse a team with the supplied external primary key or insert a team with its club relation, names, alternate information, sport type and generated alias.
     * @param int $teamId External team identifier used as primary key.
     * @param int $clubId Club identifier.
     * @param string $name Team name.
     * @param string $info Alternate team information.
     * @param int $sportsTypeId Sport type identifier.
     * @return void
     */
    private function ensureTeam(
        int $teamId,
        int $clubId,
        string $name,
        string $info,
        int $sportsTypeId
    ): void {
        $query = $this->db->createQuery()
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

    /**
     * Find an existing team by the exact club identifier and alternate team information using bound query parameters.
     * @param int $clubId Club identifier.
     * @param string $info Alternate team information.
     * @return int Matching team identifier or zero.
     */
    private function findTeamByInfo(int $clubId, string $info): int
    {
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_team'))
            ->where($this->db->quoteName('club_id') . ' = :clubId')
            ->where($this->db->quoteName('info') . ' = :info')
            ->bind(':clubId', $clubId, ParameterType::INTEGER)
            ->bind(':info', $info, ParameterType::STRING);
        $this->db->setQuery($query, 0, 1);

        return (int) ($this->db->loadResult() ?: 0);
    }

    /**
     * Look up the imported match identifier within the target project through its round relationship, preventing matches in other projects from being selected for update.
     * @param int $projectId Project identifier.
     * @param int $externalId External match identifier.
     * @return int Existing match identifier or zero.
     */
    private function findMatchId(int $projectId, int $externalId): int
    {
        $query = $this->db->createQuery()
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

    /**
     * Fetch club venue metadata from the supplied API link, using the ISHD base URL for relative links. Update the club's default venue and insert missing playground information when a valid venue identifier is provided.
     * @param int $clubId Club identifier.
     * @param string $clubInfoHref Club detail URL.
     * @param string $username API username.
     * @param string $password API password.
     * @return void
     */
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

        $playgroundId = $this->api->positiveId($venue->id ?? null);

        if ($playgroundId <= 0) {
            return;
        }

        $club = (object) [
            'id' => $clubId,
            'standard_playground' => $playgroundId,
        ];
        $this->db->updateObject('#__sportsmanagement_club', $club, 'id');

        $query = $this->db->createQuery()
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

    /**
     * Calculate the earliest and latest match dates in the selected round and update its date range when both values are available.
     * @param int $roundId Round identifier.
     * @return void
     */
    private function refreshRoundDates(int $roundId): void
    {
        $query = $this->db->createQuery()
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

    /**
     * Parse the external match date using DateTimeImmutable and return its formatted local date string and Unix timestamp.
     * @param string $dateTime External match date and time.
     * @return array{0:string,1:int} Formatted date and timestamp.
     * @throws \RuntimeException If date parsing fails.
     */
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
