<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;

class PredictionresultsModel extends SportsManagementPredictionReadModel
{
    private ?object $resultsProject = null;
    private ?array $resultsData = null;
    private int $limit = 0;
    private int $limitStart = 0;

    public function getProjectId(): int
    {
        $projects = $this->getPredictionProjects();
        $projectIds = array_values(array_filter(array_map(
            static fn(object $project): int => (int) ($project->project_id ?? 0),
            $projects
        )));

        if ($this->projectId <= 0) {
            $raw = (string) Factory::getApplication()->getInput()->get('pj', '', 'string');
            $this->projectId = $this->extractId($raw);
        }

        if ($this->projectId <= 0 || !in_array($this->projectId, $projectIds, true)) {
            $this->projectId = $projectIds[0] ?? 0;
        }

        return $this->projectId;
    }

    public function getRoundId(): int
    {
        if ($this->roundId <= 0) {
            $raw = (string) Factory::getApplication()->getInput()->get('r', '', 'string');
            $this->roundId = $this->extractId($raw);
        }

        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        if ($this->roundId <= 0 || !$this->roundBelongsToProject($this->roundId, $projectId)) {
            $this->roundId = $this->getProjectCurrentRoundId($projectId);
        }

        if ($this->roundId <= 0 || !$this->roundBelongsToProject($this->roundId, $projectId)) {
            $this->roundId = $this->getFirstProjectRoundId($projectId);
        }

        return $this->roundId;
    }

    public function getSelectedRoundId(array $config = []): int
    {
        $roundId = $this->getRoundId();
        $allowed = $this->getConfiguredRoundIds($config);

        if (!$allowed) {
            return $roundId;
        }

        $allowed = array_values(array_filter(
            $allowed,
            fn(int $id): bool => $this->roundBelongsToProject($id, $this->getProjectId())
        ));

        if (!$allowed) {
            return $roundId;
        }

        if (!in_array($roundId, $allowed, true)) {
            $roundId = $allowed[0];
        }

        return $roundId;
    }

    public function getResultsProject(): ?object
    {
        if ($this->resultsProject !== null) {
            return $this->resultsProject;
        }

        $projectId = $this->getProjectId();
        if ($projectId <= 0 || $this->predictionGameId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('pp.prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('pp.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pp.published') . ' = 1');
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject() ?: null;

        if ($project && ($project->start_date ?? '') === '0000-00-00') {
            $roundQuery = $db->getQuery(true)
                ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = ' . (int) $project->project_id);
            $db->setQuery($roundQuery);
            $project->start_date = $db->loadResult();
        }

        $this->resultsProject = $project;
        return $this->resultsProject;
    }

    public function getResultsConfig(): array
    {
        return array_merge(
            $this->getPredictionTemplateConfig('predictionentry'),
            $this->getPredictionTemplateConfig('predictionoverall'),
            $this->getPredictionTemplateConfig('predictionresults')
        );
    }

    public function getProjectCurrentRoundId(?int $projectId = null): int
    {
        $projectId ??= $this->getProjectId();
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('current_round'))
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        return (int) $db->loadResult();
    }

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

    public function getRoundOptions(array $config = []): array
    {
        $allowed = $this->getConfiguredRoundIds($config);
        $options = [];
        foreach ($this->getRoundNames($this->getProjectId(), 'ASC', $allowed ?: null) as $round) {
            $options[] = (object) [
                'value' => $this->extractId((string) ($round->value ?? '0')),
                'text' => (string) ($round->text ?? ''),
            ];
        }
        return $options;
    }

    public function getMatches(array $config = []): array
    {
        $projectId = $this->getProjectId();
        $roundId = $this->getSelectedRoundId($config);
        if ($projectId <= 0 || $roundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $select = [
            $db->quoteName('m.id', 'mID'),
            $db->quoteName('m.match_date'),
            $db->quoteName('m.team1_result', 'homeResult'),
            $db->quoteName('m.team2_result', 'awayResult'),
            $db->quoteName('m.team1_result_decision', 'homeDecision'),
            $db->quoteName('m.team2_result_decision', 'awayDecision'),
            $db->quoteName('m.projectteam1_id'),
            $db->quoteName('m.projectteam2_id'),
            $db->quoteName('t1.name', 'homeName'),
            $db->quoteName('t1.short_name', 'homeShortName'),
            $db->quoteName('t1.id', 'homeid'),
            $db->quoteName('t2.name', 'awayName'),
            $db->quoteName('t2.short_name', 'awayShortName'),
            $db->quoteName('t2.id', 'awayid'),
            $db->quoteName('c1.country', 'homeCountry'),
            $db->quoteName('c2.country', 'awayCountry'),
        ];

        $logoField = (string) ($config['show_logo_small_overview'] ?? '');
        if (in_array($logoField, ['logo_small', 'logo_middle', 'logo_big'], true)) {
            $select[] = $db->quoteName('c1.' . $logoField, 'homeLogo');
            $select[] = $db->quoteName('c2.' . $logoField, 'awayLogo');
        }

        $query = $db->getQuery(true)
            ->select($select)
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('r.id') . ' = ' . $roundId)
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->where($db->quoteName('m.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' ASC, ' . $db->quoteName('m.id') . ' ASC');

        if (!empty($config['use_pred_select_matches'])) {
            $matchIds = $this->parseIds($config['predictionmatchid'] ?? []);
            if ($matchIds) {
                $query->where($db->quoteName('m.id') . ' IN (' . implode(',', $matchIds) . ')');
            }
        }

        if (!empty($config['use_pred_select_proteams'])) {
            $teamIds = $this->parseIds($config['predictionproteamid'] ?? []);
            if ($teamIds) {
                $ids = implode(',', $teamIds);
                $query->where('(' . $db->quoteName('m.projectteam1_id') . ' IN (' . $ids . ') OR ' . $db->quoteName('m.projectteam2_id') . ' IN (' . $ids . '))');
            }
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getResultsData(array $config, array $avatarConfig): array
    {
        if ($this->resultsData !== null) {
            return $this->resultsData;
        }

        $project = $this->getResultsProject();
        if (!$project) {
            return $this->resultsData = ['rows' => [], 'allRows' => [], 'matches' => [], 'total' => 0];
        }

        $roundId = $this->getSelectedRoundId($config);
        $matches = $this->getMatches($config);
        $matchIds = array_fill_keys(array_map(static fn(object $match): int => (int) $match->mID, $matches), true);
        $members = $this->getPredictionMembersList($config, $avatarConfig);
        $closingTimes = $this->getRoundClosingTimes($project);
        $currentMemberId = $this->getCurrentPredictionMemberNumericId();
        $rows = [];

        foreach ($members as $member) {
            $memberId = (int) ($member->pmID ?? 0);
            if ($memberId <= 0) {
                continue;
            }

            $resultRows = $this->getPredictionMembersResultsList(
                (int) $project->project_id,
                $roundId,
                $roundId,
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
            $tips = [];

            foreach ($resultRows as $resultRow) {
                $matchId = (int) ($resultRow->matchID ?? 0);
                if ($matchIds && !isset($matchIds[$matchId])) {
                    continue;
                }

                $played = $this->hasPlayedResult($resultRow);
                $normalised = $this->normaliseResultForScoring($resultRow, (int) ($config['use_match_result'] ?? 0));
                $score = $played ? $this->calculateScore($project, $normalised) : 0;
                $classes = $played ? $this->classifyPrediction($project, $normalised) : ['top' => 0, 'diff' => 0, 'tend' => 0, 'tipp' => $normalised->prTipp ?? null];

                if ($played) {
                    $stats['predictionsCount']++;
                    $stats['totalPoints'] += $score;
                    $stats['totalTop'] += $classes['top'];
                    $stats['totalDiff'] += $classes['diff'];
                    $stats['totalTend'] += $classes['tend'];
                    $stats['totalJoker'] += (int) ($normalised->prJoker ?? 0);
                }

                $showAllowed = $this->mayShowTip($normalised, $memberId, $currentMemberId, $project, $closingTimes);
                $tips[$matchId] = [
                    'shown' => $showAllowed,
                    'tip' => $this->formatTip($project, $normalised, (string) ($config['seperator'] ?? ':')),
                    'points' => $played ? $score : null,
                    'joker' => !empty($normalised->prJoker),
                ];
            }

            $rows[$memberId] = array_merge($stats, [
                'pmID' => $memberId,
                'member' => $member,
                'pg_group_id' => (int) ($member->pg_group_id ?? 0),
                'pg_group_name' => (string) ($member->pg_group_name ?? ''),
                'membernameAtoZ' => (string) (($member->aliasName ?? '') ?: ($member->name ?? '')),
                'matches' => $tips,
            ]);
        }

        $rows = $this->computeMembersRanking($rows, $config);
        $total = count($rows);
        $this->configurePagination($total, !empty($config['show_all_user']));
        $paged = $this->limit > 0 ? array_slice($rows, $this->limitStart, $this->limit, true) : $rows;

        return $this->resultsData = [
            'rows' => $paged,
            'allRows' => $rows,
            'matches' => $matches,
            'total' => $total,
        ];
    }

    public function getPagination(int $total): Pagination
    {
        if ($this->limit <= 0) {
            $this->configurePagination($total, false);
        }
        return new Pagination($total, $this->limitStart, max(1, $this->limit ?: $total ?: 1));
    }

    public function getLimit(): int
    {
        if ($this->limit <= 0) {
            $this->configurePagination(0, false);
        }
        return $this->limit;
    }

    public function getLimitStart(): int
    {
        return $this->limitStart;
    }

    public function getCurrentPredictionMemberNumericId(): int
    {
        $member = $this->getPredictionMember();
        return $this->extractId((string) ($member->pmID ?? '0'));
    }

    public function scoreExample(int $home, int $away, int $tipp, int $tippHome, int $tippAway, bool $joker = false): int
    {
        $project = $this->getResultsProject();
        if (!$project) {
            return 0;
        }
        return $this->scorePredictionResult(
            $project,
            $this->createResultObject($home, $away, $tipp, $tippHome, $tippAway, $joker)
        );
    }

    private function getRoundClosingTimes(object $project): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('round_id'), $db->quoteName('rien_ne_va_plus')])
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $settings = $db->loadObjectList('round_id') ?: [];
        $timezone = (string) ($project->timezone ?? 'UTC');

        foreach ($settings as $roundId => $setting) {
            $latest = null;
            switch ((string) ($setting->rien_ne_va_plus ?? '')) {
                case 'FIRSTMATCH_OF_TIPPGAME':
                    $latest = $this->toTimestamp(
                        trim((string) ($project->start_date ?? '') . ' ' . (string) ($project->start_time ?? '00:00:00')),
                        $timezone
                    );
                    break;

                case 'FIRSTMATCH_OF_TIPPROUND':
                    $roundQuery = $db->getQuery(true)
                        ->select('MIN(' . $db->quoteName('match_date') . ')')
                        ->from($db->quoteName('#__sportsmanagement_match'))
                        ->where($db->quoteName('round_id') . ' = ' . (int) $roundId)
                        ->where($db->quoteName('published') . ' = 1');
                    $db->setQuery($roundQuery);
                    $firstMatch = (string) $db->loadResult();
                    if ($firstMatch !== '') {
                        $latest = $this->toTimestamp($firstMatch, $timezone);
                    }
                    break;
            }

            if ($latest !== null) {
                $settings[$roundId]->latestTimeToBet = $latest;
            }
        }

        return $settings;
    }

    private function mayShowTip(object $row, int $memberId, int $currentMemberId, object $project, array $closingTimes): bool
    {
        if ($memberId === $currentMemberId && $currentMemberId > 0) {
            return true;
        }
        if ($this->hasPlayedResult($row)) {
            return true;
        }

        $timezone = (string) ($project->timezone ?? 'UTC');
        $latest = $this->toTimestamp((string) ($row->match_date ?? ''), $timezone);
        $roundId = (int) ($row->matchRoundId ?? 0);
        if (isset($closingTimes[$roundId]->latestTimeToBet)) {
            $latest = (int) $closingTimes[$roundId]->latestTimeToBet;
        }
        if ($latest === null) {
            return false;
        }

        return $this->nowTimestamp($timezone) >= $latest;
    }

    protected function calculateScore(object $project, object $row): int
    {
        if ($row->homeResult === null || $row->awayResult === null) {
            return 0;
        }

        if ((int) ($project->mode ?? 0) === 0) {
            if ($row->prHomeTipp === null || $row->prAwayTipp === null) {
                return 0;
            }
        } elseif ($row->prTipp === null) {
            return 0;
        }

        return $this->scorePredictionResult(
            $project,
            $this->createResultObject(
                (int) $row->homeResult,
                (int) $row->awayResult,
                (int) ($row->prTipp ?? 0),
                (int) ($row->prHomeTipp ?? 0),
                (int) ($row->prAwayTipp ?? 0),
                !empty($row->prJoker),
                (int) ($row->homeDecision ?? 0),
                (int) ($row->awayDecision ?? 0),
                (int) ($row->matchRoundId ?? 0)
            )
        );
    }

    protected function classifyPrediction(object $project, object $row): array
    {
        $top = 0;
        $diff = 0;
        $tend = 0;
        $tipp = $row->prTipp ?? null;

        if ((int) ($project->mode ?? 0) !== 0) {
            return ['top' => $top, 'diff' => $diff, 'tend' => $tend, 'tipp' => $tipp];
        }

        $tipHome = $row->prHomeTipp;
        $tipAway = $row->prAwayTipp;
        if ($tipHome === null || $tipAway === null || $row->homeResult === null || $row->awayResult === null) {
            return ['top' => $top, 'diff' => $diff, 'tend' => $tend, 'tipp' => null];
        }

        $tipHome = (int) $tipHome;
        $tipAway = (int) $tipAway;
        $home = (int) $row->homeResult;
        $away = (int) $row->awayResult;
        $tipp = $tipHome > $tipAway ? '1' : ($tipHome < $tipAway ? '2' : '0');

        if ($home === $tipHome && $away === $tipAway) {
            $top = 1;
        } elseif ($home === $away && ($home - $away) === ($tipHome - $tipAway)) {
            $tend = 1;
        } elseif (($home - $away) === ($tipHome - $tipAway)) {
            $diff = 1;
        } elseif ((($home - $away) > 0 && ($tipHome - $tipAway) > 0)
            || (($home - $away) < 0 && ($tipHome - $tipAway) < 0)) {
            $tend = 1;
        }

        return ['top' => $top, 'diff' => $diff, 'tend' => $tend, 'tipp' => $tipp];
    }

    private function formatTip(object $project, object $row, string $separator): string
    {
        if ((int) ($project->mode ?? 0) === 0) {
            if ($row->prHomeTipp === null || $row->prAwayTipp === null) {
                return '- ' . $separator . ' -';
            }
            return (string) $row->prHomeTipp . $separator . (string) $row->prAwayTipp;
        }

        return $row->prTipp === null ? '-' : (string) $row->prTipp;
    }

    protected function normaliseResultForScoring(object $row, int $mode): object
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
            $homeRegular = end($homeParts);
            $awayRegular = end($awayParts);
            if ($homeRegular !== false && $homeRegular !== '') {
                $copy->homeResult = $homeRegular;
            }
            if ($awayRegular !== false && $awayRegular !== '') {
                $copy->awayResult = $awayRegular;
            }
        }
        return $copy;
    }

    protected function hasPlayedResult(object $row): bool
    {
        return $row->homeResult !== null
            || $row->awayResult !== null
            || $row->homeDecision !== null
            || $row->awayDecision !== null;
    }

    private function getConfiguredRoundIds(array $config): array
    {
        if (empty($config['use_pred_select_rounds'])) {
            return [];
        }
        return $this->parseIds($config['predictionroundid'] ?? []);
    }

    private function parseIds(mixed $value): array
    {
        $parts = is_array($value) ? $value : preg_split('/[\s,;]+/', trim((string) $value));
        $ids = [];
        foreach ((array) $parts as $part) {
            if (is_array($part)) {
                $ids = array_merge($ids, $this->parseIds($part));
                continue;
            }
            $id = $this->extractId((string) $part);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function roundBelongsToProject(int $roundId, int $projectId): bool
    {
        if ($roundId <= 0 || $projectId <= 0) {
            return false;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);
        return (int) $db->loadResult() > 0;
    }

    private function getFirstProjectRoundId(int $projectId): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('MIN(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    private function configurePagination(int $total, bool $showAll): void
    {
        if ($showAll) {
            $this->limit = max(1, $total);
            $this->limitStart = 0;
            return;
        }

        $app = Factory::getApplication();
        $defaultLimit = (int) ($this->getResultsConfig()['limit'] ?? $app->get('list_limit', 20));
        if ($defaultLimit <= 0) {
            $defaultLimit = (int) $app->get('list_limit', 20);
        }
        $this->limit = max(1, $app->getUserStateFromRequest('com_sportsmanagement.predictionresults.limit', 'limit', $defaultLimit, 'uint'));
        $rawStart = max(0, $app->getUserStateFromRequest('com_sportsmanagement.predictionresults.limitstart', 'limitstart', 0, 'uint'));
        $this->limitStart = (int) floor($rawStart / $this->limit) * $this->limit;
        if ($total > 0 && $this->limitStart >= $total) {
            $this->limitStart = max(0, ((int) ceil($total / $this->limit) - 1) * $this->limit);
        }
    }

    private function extractId(string $value): int
    {
        if ($value === '') {
            return 0;
        }
        return max(0, (int) strtok($value, ':'));
    }

    private function toTimestamp(string $dateTime, string $timezoneName): ?int
    {
        if (trim($dateTime) === '') {
            return null;
        }
        try {
            $timezone = new DateTimeZone($timezoneName ?: 'UTC');
            return (new DateTimeImmutable($dateTime, $timezone))->getTimestamp();
        } catch (\Throwable) {
            try {
                return (new DateTimeImmutable($dateTime, new DateTimeZone('UTC')))->getTimestamp();
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function nowTimestamp(string $timezoneName): int
    {
        try {
            return (new DateTimeImmutable('now', new DateTimeZone($timezoneName ?: 'UTC')))->getTimestamp();
        } catch (\Throwable) {
            return time();
        }
    }
}
