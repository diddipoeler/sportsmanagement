<?php
namespace Diddipoeler\Component\SportsManagement\Site\Legacy;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel;

/**
 * Temporary boundary around the historical JSMRanking engine used by Curve.
 *
 * All global class registration stays here so the Joomla 5/6 MVC model does
 * not expose or manage legacy aliases itself. This adapter can be removed once
 * the ranking algorithm has a native namespaced implementation.
 */
final class CurveRankingAdapter
{
    /**
     * @return array<int, array<int, object>> Rankings keyed by round id.
     */
    public static function getRankings(
        SportsManagementProjectModel $model,
        object $project,
        array $rounds,
        int $divisionId,
        int $databaseSelector
    ): array {
        if ($rounds === []) {
            return [];
        }

        RankingProjectFacade::setModel($model);
        if (!class_exists('sportsmanagementModelProject', false)) {
            class_alias(RankingProjectFacade::class, 'sportsmanagementModelProject');
        }

        if (!class_exists('JSMRanking', false)) {
            if (is_file(JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php')) {
                require_once JPATH_SITE . '/components/com_sportsmanagement/helpers/ranking.php';
            }
        }
        if (!class_exists('JSMRanking')) {
            return [];
        }

        $ranking = \JSMRanking::getInstance($project, $databaseSelector);
        if (!$ranking) {
            return [];
        }

        $ranking->setProjectId((int) ($project->id ?? 0), $databaseSelector);
        $firstRoundId = (int) ($rounds[0]->id ?? 0);
        if ($firstRoundId <= 0) {
            return [];
        }

        $rankings = [];
        foreach ($rounds as $round) {
            $roundId = (int) ($round->id ?? 0);
            if ($roundId <= 0) {
                continue;
            }

            $rankings[$roundId] = (array) $ranking->getRanking(
                $firstRoundId,
                $roundId,
                $divisionId,
                $databaseSelector
            );
        }

        return $rankings;
    }
}
