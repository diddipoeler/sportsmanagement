<?php
/**
 * SportsManagement prediction routing compatibility helper for Joomla 5/6.
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       predictionroute.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class JSMPredictionHelperRoute extends sportsmanagementHelperRoute
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
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => 'predictionresults',
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ];

        return Route::_('index.php?' . self::buildQuery($params) . $anchor, false);
    }

    public static function buildQuery($parts)
    {
        if ($item = sportsmanagementHelperRoute::_findItem($parts)) {
            $parts['Itemid'] = $item->id;
        } else {
            $params = ComponentHelper::getParams('com_sportsmanagement');
            $defaultItemId = (int) $params->get('default_itemid', 0);

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
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => 'predictionranking',
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID,
            'pggrouprank' => $groupRank,
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ];

        return Route::_('index.php?' . self::buildQuery($params), false);
    }

    public static function getPredictionRulesRoute($predictionID, $cfg_which_database = 0)
    {
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => 'predictionrules',
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
        ];

        return Route::_('index.php?' . self::buildQuery($params), false);
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
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => 'predictionentry',
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ];

        return Route::_('index.php?' . self::buildQuery($params), false);
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
        $params = [
            'option' => 'com_sportsmanagement',
            'view' => $isEdit ? 'predictionuser' : 'predictionusers',
            'cfg_which_database' => $cfg_which_database,
            'prediction_id' => $predictionID,
            'pggroup' => $groupID,
            'pj' => $projectID,
            'r' => $roundID !== '' ? $roundID : 0,
            'uid' => $userID,
        ];

        if ($isEdit) {
            $params['layout'] = 'edit';
        }

        return Route::_('index.php?' . self::buildQuery($params), false);
    }
}
