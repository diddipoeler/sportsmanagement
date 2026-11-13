<?php
/**
 * Native Joomla 5/6 route helper for SportsManagement prediction views.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

final class PredictionRouteHelper
{
    /**
     * Build the prediction member display or profile edit URL.
     *
     * Uses nonnegative identifiers for the selected member, game, project,
     * group, round and database. A missing or invalid negative selector is
     * treated as zero before constructing the canonical Joomla route.
     * @param int $predictionId Prediction game identifier.
     * @param int $userId Selected prediction member identifier.
     * @param string|null $task Optional edit task.
     * @param int $projectId Selected prediction project.
     * @param int $groupId Prediction member group.
     * @param int $roundId Selected prediction round.
     * @param int $database Component database selector.
     * @return string Frontend member profile or edit URL.
     */
    public static function member(
        int $predictionId,
        int $userId = 0,
        ?string $task = null,
        int $projectId = 0,
        int $groupId = 0,
        int $roundId = 0,
        int $database = 0
    ): string {
        $view = $task === 'edit' ? 'predictionuser' : 'predictionusers';
        $parameters = [
            'cfg_which_database' => max(0, $database),
            'prediction_id' => max(0, $predictionId),
            'pggroup' => max(0, $groupId),
            'pj' => max(0, $projectId),
            'r' => max(0, $roundId),
            'uid' => max(0, $userId),
        ];

        if ($task === 'edit') {
            $parameters['layout'] = 'edit';
        }

        return SiteRouteHelper::view($view, $parameters);
    }

    /**
     * Build the prediction entry page URL with optional extra filters.
     *
     * Normalises mandatory routing identifiers to nonnegative integers and
     * appends optional filters without replacing the validated base context.
     * @param int $predictionId Prediction game identifier.
     * @param int $userId Selected prediction member ID.
     * @param int $projectId Selected project ID.
     * @param int $groupId Prediction group ID.
     * @param int $roundId Current prediction round ID.
     * @param int $database Component database selector.
     * @param array<string,mixed> $extra Optional extra query values.
     * @return string Routed prediction entry URL.
     */
    public static function entry(
        int $predictionId,
        int $userId = 0,
        int $projectId = 0,
        int $groupId = 0,
        int $roundId = 0,
        int $database = 0,
        array $extra = []
    ): string {
        $parameters = [
            'cfg_which_database' => max(0, $database),
            'prediction_id' => max(0, $predictionId),
            'pggroup' => max(0, $groupId),
            'pj' => max(0, $projectId),
            'r' => max(0, $roundId),
            'uid' => max(0, $userId),
        ] + $extra;

        return SiteRouteHelper::view('predictionentry', $parameters);
    }

    /**
     * Build the prediction ranking URL for selected game and filters.
     *
     * Clamps negative identifiers and ranking filters to zero so malformed
     * request values do not propagate through generated frontend URLs.
     * @param int $predictionId Prediction game ID.
     * @param int $projectId Project ID.
     * @param int $roundId Round ID.
     * @param int $groupId Prediction group ID.
     * @param int $groupRank Group-ranking selector.
     * @param int $type Ranking type selector.
     * @param int $from First round filter.
     * @param int $to Last round filter.
     * @param int $database Database selection.
     * @return string Routed prediction ranking URL.
     */
    public static function ranking(
        int $predictionId,
        int $projectId = 0,
        int $roundId = 0,
        int $groupId = 0,
        int $groupRank = 0,
        int $type = 0,
        int $from = 0,
        int $to = 0,
        int $database = 0
    ): string {
        return SiteRouteHelper::view('predictionranking', [
            'cfg_which_database' => $database,
            'prediction_id' => $predictionId,
            'pggroup' => $groupId,
            'pj' => $projectId,
            'r' => $roundId,
            'pggrouprank' => max(0, $groupRank),
            'type' => max(0, $type),
            'from' => max(0, $from),
            'to' => max(0, $to),
        ]);
    }

    /**
     * Build the prediction results page URL for selected filters.
     *
     * Preserves the selected game and result filters while preventing
     * negative identifiers and database selectors in the generated route.
     * @param int $predictionId Prediction game ID.
     * @param int $roundId Selected round ID.
     * @param int $projectId Project ID.
     * @param int $userId Prediction member ID.
     * @param int $groupId Prediction group ID.
     * @param int $database Component database selector.
     * @return string Routed prediction results URL.
     */
    public static function results(
        int $predictionId,
        int $roundId = 0,
        int $projectId = 0,
        int $userId = 0,
        int $groupId = 0,
        int $database = 0
    ): string {
        return SiteRouteHelper::view('predictionresults', [
            'cfg_which_database' => $database,
            'prediction_id' => $predictionId,
            'pggroup' => $groupId,
            'pj' => $projectId,
            'r' => $roundId,
            'uid' => max(0, $userId),
        ]);
    }
}
