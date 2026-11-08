<?php
/**
 * SportsManagement prediction routing compatibility helper for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       predictionroute.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

if (!class_exists(SiteRouteHelper::class)) {
    $nativeHelper = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(SiteRouteHelper::class)) {
    throw new \RuntimeException('SportsManagement native SiteRouteHelper could not be loaded.', 500);
}

/**
 * Legacy class-name facade for prediction routes.
 *
 * Route generation is delegated to the native SiteRouteHelper so Joomla's
 * component router can resolve the matching menu item during preprocess().
 */
class JSMPredictionHelperRoute
{
    /**
     * Build the Joomla route to a prediction game's results page.
     * Carries game, group, project, round and user identifiers to the native router and preserves the caller's optional URL fragment.
     *
     * @param int|string $predictionID Prediction game identifier.
     * @param int|string $roundID Optional round identifier.
     * @param int|string $projectID Optional project identifier.
     * @param int|string $userID Optional prediction user identifier.
     * @param string $anchor Optional fragment appended to the resolved URL.
     * @param int|string $groupID Optional prediction group identifier.
     * @param int $cfg_which_database Selected SportsManagement database.
     * @return string Routed prediction results URL.
     */
    public static function getPredictionResultsRoute(
        $predictionID,
        $roundID = 0,
        $projectID = 0,
        $userID = 0,
        $anchor = '',
        $groupID = 0,
        $cfg_which_database = 0
    ) {
        $url = SiteRouteHelper::view('predictionresults', [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ]);

        return $url . (string) $anchor;
    }

    /**
     * Retain the historical query-string helper for third-party callers.
     *
     * Native route methods above intentionally do not force a menu item: Joomla's
     * component router is allowed to choose one that matches the target view.
     */
    public static function buildQuery($parts): string
    {
        $parts = (array) $parts;
        $itemId = (int) ($parts['Itemid'] ?? 0);

        if ($itemId > 0) {
            $parts['Itemid'] = $itemId;
        } else {
            unset($parts['Itemid']);
            $defaultItemId = (int) ComponentHelper::getParams('com_sportsmanagement')->get('default_itemid', 0);

            if ($defaultItemId > 0) {
                $parts['Itemid'] = $defaultItemId;
            }
        }

        return Uri::buildQuery($parts);
    }

    /**
     * Generate a prediction ranking URL using Joomla's native component router.
     * Preserves legacy league, group ranking, round and range filters without forcing a potentially incorrect menu item.
     *
     * @param int|string $predictionID Prediction game identifier.
     * @param int|string $projectID Optional related project.
     * @param int|string $roundID Optional round filter.
     * @param string $anchor Historical fragment argument retained for compatibility.
     * @param int|string $groupID Optional prediction group.
     * @param int|string $groupRank Optional group ranking filter.
     * @param int|string $type Ranking display mode.
     * @param int|string $from Optional beginning of the ranking period.
     * @param int|string $to Optional end of the ranking period.
     * @param int $cfg_which_database Database selector.
     * @return string Routed prediction ranking URL.
     */
    public static function getPredictionRankingRoute(
        $predictionID,
        $projectID = 0,
        $roundID = 0,
        $anchor = '',
        $groupID = 0,
        $groupRank = 0,
        $type = 0,
        $from = 0,
        $to = 0,
        $cfg_which_database = 0
    ) {
        return SiteRouteHelper::view('predictionranking', [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID,
            'pggrouprank' => $groupRank,
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Build the page URL for a prediction game's rules.
     * Passes only the prediction game and selected database to the Joomla component router.
     *
     * @param int|string $predictionID Prediction game identifier.
     * @param int $cfg_which_database Selected database.
     * @return string Routed prediction rules URL.
     */
    public static function getPredictionRulesRoute($predictionID, $cfg_which_database = 0)
    {
        return SiteRouteHelper::view('predictionrules', [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
        ]);
    }

    /**
     * Create the route to a member's prediction entry form.
     * Includes the game, round, group, project and user context in the canonical native predictionentry view.
     *
     * @param int|string $predictionID Prediction game identifier.
     * @param int|string $userID Optional prediction user.
     * @param int|string $roundID Optional round.
     * @param int|string $projectID Optional project.
     * @param string $anchor Historical fragment argument retained for compatibility.
     * @param int|string $groupID Optional prediction group.
     * @param int $cfg_which_database Database selector.
     * @return string Routed entry form URL.
     */
    public static function getPredictionTippEntryRoute(
        $predictionID,
        $userID = 0,
        $roundID = 0,
        $projectID = 0,
        $anchor = '',
        $groupID = 0,
        $cfg_which_database = 0
    ) {
        return SiteRouteHelper::view('predictionentry', [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ]);
    }

    /**
     * Resolve a prediction member list or a member edit view.
     * The edit task selects predictionuser with its edit layout, while ordinary member requests select predictionusers.
     *
     * @param int|string $predictionID Prediction game identifier.
     * @param int|string $userID Optional member identifier.
     * @param string|int $task Legacy task; edit selects the edit layout.
     * @param int|string $projectID Optional project.
     * @param int|string $groupID Optional prediction group.
     * @param int|string $roundID Optional round.
     * @param int $cfg_which_database Database selector.
     * @return string Routed member page URL.
     */
    public static function getPredictionMemberRoute(
        $predictionID,
        $userID = 0,
        $task = 0,
        $projectID = 0,
        $groupID = 0,
        $roundID = 0,
        $cfg_which_database = 0
    ) {
        $isEdit = $task === 'edit';
        $parameters = [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ];

        if ($isEdit) {
            $parameters['layout'] = 'edit';
        }

        return SiteRouteHelper::view($isEdit ? 'predictionuser' : 'predictionusers', $parameters);
    }
}
