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
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Legacy class-name facade for prediction routes.
 *
 * Route generation is delegated to the native SiteRouteHelper so Joomla's
 * component router can resolve the matching menu item during preprocess().
 */
class JSMPredictionHelperRoute
{
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

    public static function getPredictionRulesRoute($predictionID, $cfg_which_database = 0)
    {
        return SiteRouteHelper::view('predictionrules', [
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
        ]);
    }

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
