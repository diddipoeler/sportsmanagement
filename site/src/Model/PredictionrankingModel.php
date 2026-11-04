<?php
/**
 * Native Joomla 5/6 frontend prediction ranking model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class PredictionrankingModel extends SportsManagementPredictionReadModel
{
    private ?object $rankingProject = null;
    private ?array $rankingData = null;
    private int $limit = 0;
    private int $limitStart = 0;

    /**
     * Resolve the ranking project from permitted prediction game projects.
     * Parses the legacy project selector, rejects invalid or foreign
     * projects and falls back to the game's first eligible project.
     * @return int Selected project ID or zero.
     */
    public function getProjectId(): int
    {
        $projects = $this->getPredictionProjects();
        $projectIds = array_values(array_filter(array_map(
            static fn(object $project): int => (int) ($project->project_id ?? 0),
            $projects
        )));

        if ($this->projectId <= 0) {
            $raw = (string) $this->siteApplication()->getInput()->get('pj', '', 'string');
            $this->projectId = $this->extractId($raw);
        }

        if ($this->projectId <= 0 || !in_array($this->projectId, $projectIds, true)) {
            $this->projectId = $projectIds[0] ?? 0;
        }

        return $this->projectId;
    }

    /**
     * Resolve the displayed ranking round for the chosen project.
     * Validates its relation to the selected project and falls back
     * to the project's current round when the request is invalid.
     * @return int Valid ranking round ID or zero.
     */
    public function getRoundId(): int
    {
        if ($this->roundId <= 0) {
            $raw = (string) $this->siteApplication()->getInput()->get('r', '', 'string');
            $this->roundId = $this->extractId($raw);
        }

        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        if ($this->roundId <= 0 || !$this->roundBelongsToProject($this->roundId, $projectId)) {
            $this->roundId = $this->getProjectCurrentRoundId($projectId);
        }

        return $this->roundId;
    }

    /**
     * Select the first round included in accumulated ranking results.
     * Reads the requested lower bound and verifies the round belongs
     * to the same project as the ranking before using it.
     * @return int First ranking round ID.
     */
    public function getFromRoundId(): int
    {
        if ($this->fromRoundId <= 0) {
            $raw = (string) $this->siteApplication()->getInput()->get('from', '', 'string');
            $this->fromRoundId = $this->extractId($raw);
        }

        $projectId = $this->getProjectId();
        if ($this->fromRoundId <= 0 || !$this->roundBelongsToProject($this->fromRoundId, $projectId)) {
            $this->fromRoundId = max(1, $this->getRoundId());
        }

        return $this->fromRoundId;
    }

    /**
     * Select the final round for aggregated ranking results.
     * Validates the requested round and ensures the range is not
     * reversed relative to the selected first round.
     * @return int Last ranking round ID.
     */
    public function getToRoundId(): int
    {
        if ($this->toRoundId <= 0) {
            $raw = (string) $this->siteApplication()->getInput()->get('to', '', 'string');
            $this->toRoundId = $this->extractId($raw);
        }

        $projectId = $this->getProjectId();
        if ($this->toRoundId <= 0 || !$this->roundBelongsToProject($this->toRoundId, $projectId)) {
            $this->toRoundId = $this->getRoundId();
        }
        if ($this->toRoundId < $this->getFromRoundId()) {
            $this->toRoundId = $this->getFromRoundId();
        }

        return $this->toRoundId;
    }

    /**
     * Load and cache prediction project details needed by the ranking.
     * Joins the current competition project to its prediction configuration
     * and rejects unknown game/project combinations.
     * @return object|null Selected prediction project or null.
     */
    public function getRankingProject(): ?object
    {
        if ($this->rankingProject !== null) {
            return $this->rankingProject;
        }

        $projectId = $this->getProjectId();
        if ($projectId <= 0 || $this->predictionGameId <= 0) {
            return null;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'pp.*',
                $db->quoteName('p.name', 'projectName'),
                $db->quoteName('p.alias', 'projectAlias'),
                $db->quoteName('p.start_date'),
                $db->quoteName('p.start_time'),
                $db->quoteName('p.timezone'),
                $db->quoteName('p.current_round'),
                $db->quoteName('p.master_template'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_project', 'pp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pp.project_id'))
            ->where($db->quoteName('pp.prediction_id') . ' = :rankingPredictionId')
            ->where($db->quoteName('pp.project_id') . ' = :rankingProjectId')
            ->where($db->quoteName('pp.published') . ' = 1')
            ->bind(':rankingPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':rankingProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject() ?: null;

        if ($project && ($project->start_date ?? '') === '0000-00-00') {
            $roundProjectId = (int) $project->project_id;
            $roundQuery = $db->createQuery()
                ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = :rankingRoundProjectId')
                ->bind(':rankingRoundProjectId', $roundProjectId, ParameterType::INTEGER);
            $db->setQuery($roundQuery);
            $project->start_date = $db->loadResult();
        }

        $this->rankingProject = $project;
        return $this->rankingProject;
    }

    /**
     * Retrieve the current round for a project via Joomla's DB API.
     * Uses a bound positive project ID and returns zero when the project
     * cannot be selected from the database.
     * @param int|null $projectId Optional explicit project identifier.
     * @return int Current competition round ID.
     */
    public function getProjectCurrentRoundId(?int $projectId = null): int
    {
        $projectId ??= $this->getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('current_round'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :currentRoundProjectId')
            ->bind(':currentRoundProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return (int) $db->loadResult();
    }

    /**
     * Build the list of available projects for the ranking selector.
     * Maps configured prediction projects to Joomla value/text
     * options preserving display names and numeric IDs.
     * @return array<int,object> Project selector options.
     */
    public function getProjectOptions(): array
    {
        $options = [];
        foreach ($this->getPredictionProjects() as $project) {
            $options[] = (object) [
                'value' => (int) ($project->project_id ?? 0),
                'text' => (string) ($project->projectName ?? $project->project_id ?? ''),
            ];
        }
        return $options;
    }

    /**
     * Create round dropdown options for the selected prediction project.
     * Delegates to the shared round-name lookup and normalises
     * each value through the strict Joomla ID parser.
     * @param array<int>|null $allowedRoundIds Optional allowed round filter.
     * @return array<int,object> Round value/text options.
     */
    public function getRoundOptions(?array $allowedRoundIds = null): array
    {
        $options = [];
        foreach ($this->getRoundNames($this->getProjectId(), 'ASC', $allowedRoundIds) as $round) {
            $options[] = (object) [
                'value' => $this->extractId((string) ($round->value ?? '0')),
                'text' => (string) ($round->text ?? ''),
            ];
        }
        return $options;
    }

    /**
     * Calculate and cache ranking rows for the selected prediction game.
     * Loads permitted prediction members, applies round range, project
     * scoring rules and avatar settings, and prepares paginated
     * member rows with totals and unpaginated ranking data.
     * @param array $config Prediction ranking options.
     * @param array $avatarConfig Avatar rendering settings.
     * @return array Ranking rows, count and full rankings.
     */
    public function getRankingData(array $config, array $avatarConfig): array
    {
        if ($this->rankingData !== null) {
            return $this->rankingData;
        }

        $project = $this->getRankingProject();
        if (!$project) {
            return $this->rankingData = ['rows' => [], 'total' => 0, 'allRows' => []];
        }

        $members = $this->getPredictionMembersList($config, $avatarConfig);
        $memberRows = [];
        $groupRows = [];
        $from = $this->getFromRoundId();
        $to = $this->getToRoundId();

        foreach ($members as $member) {
            $memberId = (int) ($member->pmID ?? 0);
            if ($memberId <= 0) {
                continue;
            }

            $results = $this->getPredictionMembersResultsList(
                (int) $project->project_id,
                $from,
                $to,
                (int) ($member->user_id ?? 0)
            );

            $stats = [
                'predictionsCount' => 0,
                'totalPoints' => 0,
                'totalTop' => 0,
                'totalDiff' => 0,
                'totalTend' => 0,
                'totalJoker' => 0,
            ];

            foreach ($results as $resultRow) {
                if (!$this->hasPlayedResult($resultRow)) {
                    continue;
                }

                $stats['predictionsCount']++;
                $normalised = $this->normaliseResultForScoring($resultRow, (int) ($config['use_match_result'] ?? 0));
                $resultObject = $this->createResultObject(
                    (int) $normalised->homeResult,
                    (int) $normalised->awayResult,
                    (int) ($normalised->prTipp ?? 0),
                    (int) ($normalised->prHomeTipp ?? 0),
                    (int) ($normalised->prAwayTipp ?? 0),
                    !empty($normalised->prJoker),
                    (int) ($normalised->homeDecision ?? 0),
                    (int) ($normalised->awayDecision ?? 0),
                    (int) ($normalised->matchRoundId ?? 0)
                );

                // Ranking display is deliberately read-only: show the current
                // calculated value but never persist corrections during render.
                $stats['totalPoints'] += $this->scorePredictionResult($project, $resultObject);
                $stats['totalJoker'] += (int) ($normalised->prJoker ?? 0);
                $stats['totalTop'] += (int) ($normalised->prTop ?? 0);
                $stats['totalDiff'] += (int) ($normalised->prDiff ?? 0);
                $stats['totalTend'] += (int) ($normalised->prTend ?? 0);
            }

            $champion = $this->getChampionTipData((int) $project->project_id, (string) ($member->champ_tipp ?? ''));
            $final4 = $this->getFinal4TipData((int) $project->project_id, (string) ($member->final4_tipp ?? ''));
            $final4Points = array_sum(array_map(static fn(array $tip): int => (int) ($tip['points'] ?? 0), $final4));
            $showTips = $this->isProjectStarted($project) || $this->isAllowedAdmin((int) ($member->user_id ?? 0));

            $memberRows[$memberId] = array_merge($stats, [
                'pmID' => $memberId,
                'member' => $member,
                'pg_group_id' => (int) ($member->pg_group_id ?? 0),
                'pg_group_name' => (string) ($member->pg_group_name ?? ''),
                'membernameAtoZ' => (string) (($member->aliasName ?? '') ?: ($member->name ?? '')),
                'showTips' => $showTips,
                'champion' => $champion,
                'final4' => $final4,
                'totalPoints' => $stats['totalPoints'] + (int) ($champion['points'] ?? 0) + $final4Points,
            ]);

            $groupId = (int) ($member->pg_group_id ?? 0);
            if ($groupId > 0) {
                if (!isset($groupRows[$groupId])) {
                    $groupRows[$groupId] = [
                        'pg_group_id' => $groupId,
                        'pg_group_name' => (string) ($member->pg_group_name ?? ''),
                        'membernameAtoZ' => (string) ($member->pg_group_name ?? ''),
                        'predictionsCount' => 0,
                        'totalPoints' => 0,
                        'totalTop' => 0,
                        'totalDiff' => 0,
                        'totalTend' => 0,
                        'totalJoker' => 0,
                        'memberCount' => 0,
                    ];
                }
                $groupRows[$groupId]['memberCount']++;
                $groupRows[$groupId]['predictionsCount'] += $stats['predictionsCount'];
                $groupRows[$groupId]['totalPoints'] += $stats['totalPoints'] + (int) ($champion['points'] ?? 0) + $final4Points;
                $groupRows[$groupId]['totalTop'] += $stats['totalTop'];
                $groupRows[$groupId]['totalDiff'] += $stats['totalDiff'];
                $groupRows[$groupId]['totalTend'] += $stats['totalTend'];
                $groupRows[$groupId]['totalJoker'] += $stats['totalJoker'];
            }
        }

        $allRows = $this->groupRanking ? $groupRows : $memberRows;
        $allRows = $this->computeMembersRanking($allRows, $config);
        $total = count($allRows);
        $this->configurePagination($total, !empty($config['show_all_user']));
        $rows = $this->limit > 0 ? array_slice($allRows, $this->limitStart, $this->limit, true) : $allRows;

        return $this->rankingData = ['rows' => $rows, 'total' => $total, 'allRows' => $allRows];
    }

    /**
     * Construct Joomla Pagination for the member ranking.
     * Initialises the configured page limit when needed and ensures
     * a positive effective page size even for empty rankings.
     * @param int $total Total members matching ranking filters.
     * @return Pagination Joomla pagination object.
     */
    public function getPagination(int $total): Pagination
    {
        if ($this->limit === 0 && $total > 0) {
            $this->configurePagination($total, false);
        }
        return new Pagination($total, $this->limitStart, max(1, $this->limit ?: $total ?: 1));
    }

    /**
     * Return the configured page size for the ranking list.
     * Initialises the limit from Joomla user state if the ranking
     * has not yet calculated its pagination settings.
     * @return int Positive page size.
     */
    public function getLimit(): int
    {
        if ($this->limit <= 0) {
            $this->configurePagination(0, false);
        }
        return $this->limit;
    }

    /**
     * Return the current pagination offset for ranking records.
     * Uses the starting row calculated by configurePagination().
     * @return int Nonnegative first row index.
     */
    public function getLimitStart(): int
    {
        return $this->limitStart;
    }

    /**
     * Read the map template configuration for this ranking project.
     * Combines default map XML options and project-specific settings
     * through the shared prediction template configuration loader.
     * @return array<string,mixed> Effective map template settings.
     */
    public function getMapConfig(): array
    {
        return $this->getProjectTemplateConfig('map', $this->getProjectId());
    }

    /**
     * Build geographical KML data for visible prediction members.
     * Only generates geodata when a compatible profile source is
     * configured and its user table exists, using the game's
     * member records and stored coordinates.
     * @param array $avatarConfig Profile image/source configuration.
     * @return string|null Generated KML path or null when unavailable.
     */
    public function buildPredictionKml(array $avatarConfig): ?string
    {
        if ($this->predictionGameId <= 0 || ($avatarConfig['show_image_from'] ?? '') !== 'com_cbe') {
            return null;
        }

        $db = $this->getDatabase();
        $table = $db->replacePrefix('#__cbe_users');
        if (!in_array($table, $db->getTableList(), true)) {
            return null;
        }

        $predictionGameId = $this->predictionGameId;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pm.id'),
                $db->quoteName('u.name'),
                $db->quoteName('cbe.latitude'),
                $db->quoteName('cbe.longitude'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->join('LEFT', $db->quoteName('#__cbe_users', 'cbe') . ' ON ' . $db->quoteName('cbe.userid') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('pm.prediction_id') . ' = :kmlPredictionId')
            ->bind(':kmlPredictionId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($query);
        $members = $db->loadObjectList() ?: [];

        $placemarks = [];
        foreach ($members as $member) {
            $lat = (float) ($member->latitude ?? 255);
            $lng = (float) ($member->longitude ?? 255);
            if ($lat === 255.0 || $lng === 255.0 || ($lat === 0.0 && $lng === 0.0)) {
                continue;
            }
            $name = htmlspecialchars((string) ($member->name ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $placemarks[] = "<Placemark><name>{$name}</name><Point><coordinates>{$lng},{$lat}</coordinates></Point></Placemark>";
        }

        if (!$placemarks) {
            return null;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<kml xmlns="http://www.opengis.net/kml/2.2"><Document>' . "\n"
            . implode("\n", $placemarks) . "\n</Document></kml>";
        $file = JPATH_SITE . '/tmp/' . $this->predictionGameId . '-prediction.kml';

        try {
            File::write($file, $xml);
        } catch (\Throwable) {
            return null;
        }

        return Uri::root() . 'tmp/' . rawurlencode((string) $this->predictionGameId) . '-prediction.kml';
    }

    /**
     * Read the champion prediction and available reward for a project.
     * Matches the selected project team in a member's saved picks
     * and loads the project's champion-tip scoring configuration.
     * @param int $projectId Competition project ID.
     * @param string $tip Serialized champion selection.
     * @return array Champion tip and scoring data.
     */
    private function getChampionTipData(int $projectId, string $tip): array
    {
        $teamId = $this->extractTipTeamId($tip, $projectId);
        $points = false;
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('league_champ'), $db->quoteName('points_tipp_champ')])
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = :championPredictionId')
            ->where($db->quoteName('project_id') . ' = :championProjectId')
            ->where($db->quoteName('champ') . ' = 1')
            ->bind(':championPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':championProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $evaluation = $db->loadObject();
        if ($evaluation && (int) $evaluation->league_champ > 0) {
            $points = $teamId > 0 && $teamId === (int) $evaluation->league_champ
                ? (int) $evaluation->points_tipp_champ
                : 0;
        }

        return [
            'teamId' => $teamId,
            'team' => $teamId > 0 ? $this->getProjectTeamInfo($teamId) : null,
            'points' => $points,
        ];
    }

    /**
     * Load final-four tip scores and related project configuration.
     * Compares the member's saved final-four team picks against
     * the configured project values and point rules.
     * @param int $projectId Competition project ID.
     * @param string $tips Serialized final-four selections.
     * @return array Final-four tip points and values.
     */
    private function getFinal4TipData(int $projectId, string $tips): array
    {
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('league_final4'), $db->quoteName('points_tipp_final4')])
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = :final4PredictionId')
            ->where($db->quoteName('project_id') . ' = :final4ProjectId')
            ->where($db->quoteName('final4') . ' = 1')
            ->bind(':final4PredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':final4ProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $evaluation = $db->loadObject();
        $evaluatedTeams = $evaluation && !empty($evaluation->league_final4)
            ? array_values(array_filter(array_map('intval', explode(',', (string) $evaluation->league_final4))))
            : [];
        $evaluated = count($evaluatedTeams) === 4;
        $pointsPerTeam = (int) ($evaluation->points_tipp_final4 ?? 0);

        $result = [];
        foreach (array_filter(explode(';', $tips), static fn(string $value): bool => trim($value) !== '') as $entry) {
            [$entryProject, $teamId] = array_pad(array_map('intval', explode(',', $entry, 2)), 2, 0);
            if ($entryProject !== $projectId || $teamId <= 0) {
                continue;
            }
            $result[] = [
                'teamId' => $teamId,
                'team' => $this->getProjectTeamInfo($teamId),
                'points' => $evaluated ? (in_array($teamId, $evaluatedTeams, true) ? $pointsPerTeam : 0) : false,
            ];
        }
        return $result;
    }

    /**
     * Determine whether a competition project has started for scoring.
     * Uses the project's date, time and timezone; treats invalid
     * dates or timezones as started to avoid disclosing predictions.
     * @param object $project Prediction project start settings.
     * @return bool Whether the project is considered started.
     */
    private function isProjectStarted(object $project): bool
    {
        $date = (string) ($project->start_date ?? '');
        if ($date === '' || $date === '0000-00-00') {
            return true;
        }

        $time = (string) ($project->start_time ?? '00:00:00');
        $timezoneName = (string) ($project->timezone ?? 'UTC');
        try {
            $timezone = new DateTimeZone($timezoneName ?: 'UTC');
            $start = new DateTimeImmutable(trim($date . ' ' . $time), $timezone);
            return new DateTimeImmutable('now', $timezone) > $start;
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Choose regular, overtime or shootout result for tip scoring.
     * Clones the fixture row and selects the requested score mode
     * without mutating source match data used elsewhere in ranking.
     * @param object $row Result record containing all score variants.
     * @param int $mode Result calculation mode.
     * @return object Match result data normalised for ranking.
     */
    private function normaliseResultForScoring(object $row, int $mode): object
    {
        $copy = clone $row;
        if ($mode === 1 && ($copy->homeResultOT !== null || $copy->awayResultOT !== null)) {
            $copy->homeResult = $copy->homeResultOT;
            $copy->awayResult = $copy->awayResultOT;
        } elseif ($mode === 2 && ($copy->homeResultSO !== null || $copy->awayResultSO !== null)) {
            $copy->homeResult = $copy->homeResultSO;
            $copy->awayResult = $copy->awayResultSO;
        } elseif ($mode === 0 && ($copy->homeResultOT !== null || $copy->awayResultOT !== null || $copy->homeResultSO !== null || $copy->awayResultSO !== null)) {
            $homeParts = explode(';', (string) ($copy->homeResultSplit ?? ''));
            $awayParts = explode(';', (string) ($copy->awayResultSplit ?? ''));
            if ($homeParts && end($homeParts) !== '') {
                $copy->homeResult = end($homeParts);
            }
            if ($awayParts && end($awayParts) !== '') {
                $copy->awayResult = end($awayParts);
            }
        }
        return $copy;
    }

    /**
     * Detect whether any regular or decision result is recorded.
     * Considers scores and winner decisions for both teams before
     * awarding fixture points in ranking calculations.
     * @param object $row Fixture result record.
     * @return bool Whether the match has a result.
     */
    private function hasPlayedResult(object $row): bool
    {
        return $row->homeResult !== null
            || $row->awayResult !== null
            || $row->homeDecision !== null
            || $row->awayDecision !== null;
    }

    /**
     * Parse the chosen team ID for one specific prediction project.
     * Reads semicolon-separated project,team entries and rejects
     * malformed or overflowing IDs instead of partially casting
     * invalid stored predictions to valid team identifiers.
     * @param string $tips Serialized project/team selections.
     * @param int $projectId Project whose tip should be extracted.
     * @return int Valid team ID or zero.
     */
    private function extractTipTeamId(string $tips, int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        foreach (explode(';', $tips) as $entry) {
            $pair = array_map('trim', explode(',', $entry, 2));

            if (count($pair) !== 2) {
                continue;
            }

            $entryProject = $this->extractId($pair[0]);
            $teamId = $this->extractId($pair[1]);

            if ($entryProject === $projectId && $teamId > 0) {
                return $teamId;
            }
        }

        return 0;
    }

    /**
     * Merge ranking map XML defaults and project template overrides.
     * Loads project settings, optionally inherits from its master
     * template and returns the effective configuration fields.
     * @param string $template Requested prediction template identifier.
     * @param int $projectId Project whose overrides should apply.
     * @return array<string,mixed> Final presentation configuration.
     */
    private function getProjectTemplateConfig(string $template, int $projectId): array
    {
        $defaults = $this->loadXmlDefaults($template);
        if ($projectId <= 0) {
            return $defaults;
        }

        $params = $this->loadProjectTemplateParams($template, $projectId);
        if ($params === null) {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select($db->quoteName('master_template'))
                ->from($db->quoteName('#__sportsmanagement_project'))
                ->where($db->quoteName('id') . ' = :templateProjectId')
                ->bind(':templateProjectId', $projectId, ParameterType::INTEGER);
            $db->setQuery($query, 0, 1);
            $masterId = (int) $db->loadResult();
            if ($masterId > 0 && $masterId !== $projectId) {
                $params = $this->loadProjectTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString($params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Retrieve stored template parameters for a specific project.
     * Queries Joomla's template configuration table by bound
     * template name and project ID, returning null if absent.
     * @param string $template Template configuration name.
     * @param int $projectId Project to look up.
     * @return string|null Serialized project template parameters.
     */
    private function loadProjectTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = :templateName')
            ->where($db->quoteName('project_id') . ' = :templateConfigProjectId')
            ->bind(':templateName', $template, ParameterType::STRING)
            ->bind(':templateConfigProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

    /**
     * Read default values from the prediction template XML.
     * Restricts the filename to its basename and extracts the
     * default attributes of declared fields when a template exists.
     * @param string $template Template name to load.
     * @return array<string,mixed> Default template field values.
     */
    private function loadXmlDefaults(string $template): array
    {
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file)) {
            return [];
        }
        try {
            $xml = simplexml_load_file($file);
        } catch (\Throwable) {
            return [];
        }
        if ($xml === false) {
            return [];
        }
        $defaults = [];
        foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
            $attributes = $field->attributes();
            if (isset($attributes['default'])) {
                $defaults[(string) $attributes['name']] = (string) $attributes['default'];
            }
        }
        return $defaults;
    }

    /**
     * Verify a ranking round belongs to the active competition project.
     * Counts the matching Joomla round row with both identifiers bound
     * so a request cannot select a round from another project.
     * @param int $roundId Round identifier to validate.
     * @param int $projectId Owning project ID.
     * @return bool True when the relation exists.
     */
    private function roundBelongsToProject(int $roundId, int $projectId): bool
    {
        if ($roundId <= 0 || $projectId <= 0) {
            return false;
        }
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :belongsRoundId')
            ->where($db->quoteName('project_id') . ' = :belongsProjectId')
            ->bind(':belongsRoundId', $roundId, ParameterType::INTEGER)
            ->bind(':belongsProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        return (int) $db->loadResult() > 0;
    }

    /**
     * Apply Joomla user-state pagination and normalise list offsets.
     * Uses a bounded positive page size, chooses an appropriate
     * start row and supports an explicit show-all ranking mode.
     * @param int $total Total matching ranking rows.
     * @param bool $showAll Whether all rankings should be included.
     * @return void
     */
    private function configurePagination(int $total, bool $showAll): void
    {
        if ($showAll) {
            $this->limit = max(1, $total);
            $this->limitStart = 0;
            return;
        }

        $app = $this->siteApplication();
        $defaultLimit = (int) $app->get('list_limit', 20);
        $this->limit = max(1, $app->getUserStateFromRequest('com_sportsmanagement.predictionranking.limit', 'limit', $defaultLimit, 'uint'));
        $rawStart = max(0, $app->getUserStateFromRequest('com_sportsmanagement.predictionranking.limitstart', 'limitstart', 0, 'uint'));
        $this->limitStart = (int) floor($rawStart / $this->limit) * $this->limit;
        if ($total > 0 && $this->limitStart >= $total) {
            $this->limitStart = max(0, ((int) ceil($total / $this->limit) - 1) * $this->limit);
        }
    }

    /**
     * Strictly parse a numeric Joomla project, round or team ID slug.
     * Accepts decimal IDs and ID:alias strings but rejects invalid
     * characters, partial numeric conversions and values above PHP_INT_MAX.
     * @param string $value Raw ID or legacy Joomla ID:alias segment.
     * @return int Positive ID or zero when invalid.
     */
    private function extractId(string $value): int
    {
        $value = trim($value);

        if (!preg_match('/^([0-9]+)(?::[^:\\s,;]+)?$/D', $value, $matches)) {
            return 0;
        }

        $digits = ltrim($matches[1], '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }
}
