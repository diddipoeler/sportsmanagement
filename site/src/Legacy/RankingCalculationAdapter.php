<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RankingModel;

/**
 * Temporary Joomla 5/6 boundary around the historical JSMRanking algorithm.
 *
 * Request state, project reads and range/division selection are resolved by
 * native MVC code. Only the sport-specific ranking arithmetic remains in the
 * historical helper until it can be replaced independently.
 */
final class RankingCalculationAdapter
{
    /**
     * @return array{
     *     round:int,from:int,to:int,part:int,type:int,divLevel:int,
     *     divisionIds:array<int,int>,currentRanking:array,homeRank:array,
     *     awayRank:array,previousRanking:array,firstRank:array,secondRank:array
     * }
     */
    public static function calculate(
        RankingModel $model,
        object $project,
        array $config,
        int $databaseSelector = 0,
        int $roundId = 0,
        int $from = 0,
        int $to = 0,
        int $part = 0,
        int $type = 0,
        int $selectedDivision = 0,
        ?int $divisionLevel = null
    ): array {
        $result = self::emptyResult();
        $rounds = $model->getRounds('ASC', false);

        if ($rounds === []) {
            return $result;
        }

        $firstRoundId = (int) ($rounds[0]->id ?? 0);
        $lastRound = $rounds[array_key_last($rounds)];
        $lastRoundId = (int) ($lastRound->id ?? 0);
        $roundId = $roundId > 0 ? $roundId : $model->getCurrentRound();
        if ($roundId <= 0) {
            $roundId = $lastRoundId;
        }

        [$from, $to] = self::resolveRange(
            $rounds,
            $firstRoundId,
            $lastRoundId,
            $roundId,
            $from,
            $to,
            $part
        );

        $divisionLevel ??= (int) ($config['default_division_view'] ?? 0);
        [$divisionLevel, $divisionIds] = self::resolveDivisions(
            $model,
            $project,
            $config,
            $selectedDivision,
            $divisionLevel
        );

        $engine = self::createEngine($model, $project, $databaseSelector);
        if (!$engine) {
            return array_merge($result, [
                'round' => $roundId,
                'from' => $from,
                'to' => $to,
                'part' => $part,
                'type' => $type,
                'divLevel' => $divisionLevel,
                'divisionIds' => $divisionIds,
            ]);
        }

        $sportType = (string) ($project->sport_type_name ?? '');
        $main = self::calculateRange(
            $engine,
            $model,
            $config,
            $divisionIds,
            $from,
            $to,
            $type,
            $databaseSelector,
            $sportType
        );

        $firstRank = [];
        $secondRank = [];
        if (!empty($config['show_half_of_season'])) {
            if (!empty($config['show_table_4'])) {
                [$firstFrom, $firstTo] = self::resolveRange(
                    $rounds,
                    $firstRoundId,
                    $lastRoundId,
                    $roundId,
                    0,
                    0,
                    1
                );
                $firstRank = self::calculateRange(
                    $engine,
                    $model,
                    $config,
                    $divisionIds,
                    $firstFrom,
                    $firstTo,
                    $type,
                    $databaseSelector,
                    $sportType
                )['currentRanking'];
            }

            if (!empty($config['show_table_5'])) {
                [$secondFrom, $secondTo] = self::resolveRange(
                    $rounds,
                    $firstRoundId,
                    $lastRoundId,
                    $roundId,
                    0,
                    0,
                    2
                );
                $secondRank = self::calculateRange(
                    $engine,
                    $model,
                    $config,
                    $divisionIds,
                    $secondFrom,
                    $secondTo,
                    $type,
                    $databaseSelector,
                    $sportType
                )['currentRanking'];
            }
        }

        return [
            'round' => $roundId,
            'from' => $from,
            'to' => $to,
            'part' => $part,
            'type' => $type,
            'divLevel' => $divisionLevel,
            'divisionIds' => $divisionIds,
            'currentRanking' => $main['currentRanking'],
            'homeRank' => $main['homeRank'],
            'awayRank' => $main['awayRank'],
            'previousRanking' => $main['previousRanking'],
            'firstRank' => $firstRank,
            'secondRank' => $secondRank,
        ];
    }

    private static function createEngine(RankingModel $model, object $project, int $databaseSelector): ?object
    {
        RankingProjectFacade::setModel($model);
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(RankingProjectFacade::class, 'sportsmanagementModelProject');
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            class_alias(RankingHelperFacade::class, 'sportsmanagementHelper');
        }

        if (!class_exists('JSMRanking', false)) {
            $file = JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php';
            if (!is_file($file)) {
                return null;
            }
            require_once $file;
        }

        if (!class_exists('JSMRanking', false)) {
            return null;
        }

        RankingHelperFacade::resetMessages();
        $engine = \JSMRanking::getInstance($project, $databaseSelector);
        if (!$engine) {
            return null;
        }

        $engine->setProjectId((int) ($project->id ?? 0), $databaseSelector);

        return $engine;
    }

    /** @return array{currentRanking:array,homeRank:array,awayRank:array,previousRanking:array} */
    private static function calculateRange(
        object $engine,
        RankingModel $model,
        array $config,
        array $divisionIds,
        int $from,
        int $to,
        int $type,
        int $databaseSelector,
        string $sportType
    ): array {
        $current = [];
        $home = [];
        $away = [];
        $previous = [];
        $previousTo = $model->getPreviousRoundId($to);

        foreach ($divisionIds as $divisionId) {
            if ($type === 2) {
                $current[$divisionId] = (array) $engine->getRankingAway(
                    $from,
                    $to,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            } elseif ($type === 1) {
                $current[$divisionId] = (array) $engine->getRankingHome(
                    $from,
                    $to,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            } else {
                $current[$divisionId] = (array) $engine->getRanking(
                    $from,
                    $to,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
                $home[$divisionId] = (array) $engine->getRankingHome(
                    $from,
                    $to,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
                $away[$divisionId] = (array) $engine->getRankingAway(
                    $from,
                    $to,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            }

            if (empty($config['last_ranking'])) {
                continue;
            }

            if ($to === $from || $previousTo === $to) {
                $previous[$divisionId] = $current[$divisionId];
                continue;
            }

            if ($type === 2) {
                $previous[$divisionId] = (array) $engine->getRankingAway(
                    $from,
                    $previousTo,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            } elseif ($type === 1) {
                $previous[$divisionId] = (array) $engine->getRankingHome(
                    $from,
                    $previousTo,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            } else {
                $previous[$divisionId] = (array) $engine->getRanking(
                    $from,
                    $previousTo,
                    $divisionId,
                    $databaseSelector,
                    $sportType
                );
            }
        }

        return [
            'currentRanking' => $current,
            'homeRank' => $home,
            'awayRank' => $away,
            'previousRanking' => $previous,
        ];
    }

    /** @return array{0:int,1:int} */
    private static function resolveRange(
        array $rounds,
        int $firstRoundId,
        int $lastRoundId,
        int $roundId,
        int $from,
        int $to,
        int $part
    ): array {
        $count = count($rounds);

        if ($part === 1) {
            $middle = intdiv($count, 2);
            $toIndex = min(max(0, $middle), $count - 1);

            return [$firstRoundId, (int) ($rounds[$toIndex]->id ?? $lastRoundId)];
        }

        if ($part === 2) {
            $middle = max(0, intdiv($count, 2) - 1);

            return [(int) ($rounds[$middle]->id ?? $firstRoundId), $lastRoundId];
        }

        return [
            $from > 0 ? $from : $firstRoundId,
            $to > 0 ? $to : ($roundId > 0 ? $roundId : $lastRoundId),
        ];
    }

    /** @return array{0:int,1:array<int,int>} */
    private static function resolveDivisions(
        RankingModel $model,
        object $project,
        array $config,
        int $selectedDivision,
        int $divisionLevel
    ): array {
        if ((string) ($project->project_type ?? '') !== 'DIVISIONS_LEAGUE') {
            return [0, [0]];
        }

        if ($selectedDivision > 0) {
            return [$divisionLevel, [$selectedDivision]];
        }

        $allowedLevels = [];
        if (!empty($config['show_project_table'])) {
            $allowedLevels[] = 0;
        }
        if (!empty($config['show_level1_table'])) {
            $allowedLevels[] = 1;
        }
        if (!empty($config['show_level2_table'])) {
            $allowedLevels[] = 2;
        }

        if ($allowedLevels === []) {
            return [0, [0]];
        }

        $defaultLevel = (int) ($config['default_division_view'] ?? 0);
        if (!in_array($divisionLevel, $allowedLevels, true)) {
            $divisionLevel = in_array($defaultLevel, $allowedLevels, true)
                ? $defaultLevel
                : (int) $allowedLevels[0];
        }

        if ($divisionLevel <= 0) {
            return [0, [0]];
        }

        $divisions = $model->getDivisions($divisionLevel);
        $ids = array_values(array_filter(array_map(
            static fn ($division): int => (int) ($division->id ?? 0),
            $divisions
        )));

        if ($ids === []) {
            if (in_array(0, $allowedLevels, true)) {
                return [0, [0]];
            }

            foreach ($allowedLevels as $fallbackLevel) {
                if ($fallbackLevel <= 0 || $fallbackLevel === $divisionLevel) {
                    continue;
                }
                $fallbackDivisions = $model->getDivisions($fallbackLevel);
                $fallbackIds = array_values(array_filter(array_map(
                    static fn ($division): int => (int) ($division->id ?? 0),
                    $fallbackDivisions
                )));
                if ($fallbackIds !== []) {
                    return [(int) $fallbackLevel, $fallbackIds];
                }
            }
        }

        return [$divisionLevel, $ids !== [] ? $ids : [0]];
    }

    private static function emptyResult(): array
    {
        return [
            'round' => 0,
            'from' => 0,
            'to' => 0,
            'part' => 0,
            'type' => 0,
            'divLevel' => 0,
            'divisionIds' => [0],
            'currentRanking' => [],
            'homeRank' => [],
            'awayRank' => [],
            'previousRanking' => [],
            'firstRank' => [],
            'secondRank' => [],
        ];
    }
}
