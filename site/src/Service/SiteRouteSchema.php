<?php
/**
 * Native positional route schema shared by the Joomla 5/6 site router.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

/**
 * Native positional route definition shared by the Joomla 5/6 site router.
 *
 * The order of every key is part of the public SEF URL contract: Router::build()
 * emits these values positionally and Router::parse() reads them back in the
 * same order.
 */
final class SiteRouteSchema
{
    private const VIEWS = [
        'about' => ['cfg_which_database' => '', 's' => '', 'p' => ''],
        'uefawertung' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'coefficientyear' => ''],
        'calendar' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''],
        'clubinfo' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => ''],
        'clubplan' => ['cfg_which_database' => '', 's' => '', 'cid' => '', 'p' => '', 'startdate' => '', 'enddate' => ''],
        'curve' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid1' => '', 'tid2' => '', 'division' => ''],
        'editprojectteam' => ['tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''],
        'editteam' => ['tmpl' => '', 'ptid' => '', 'tid' => '', 'p' => ''],
        'editperson' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''],
        'editclub' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'cid' => '', 'id' => '', 'tmpl' => ''],
        'editmatch' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => '', 'matchid' => '', 'tmpl' => '', 'oldlayout' => '', 'team' => '', 'pteam' => ''],
        'eventsranking' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'evid' => '', 'mid' => '', 'division' => ''],
        'ical' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''],
        'scoresheet' => ['cfg_which_database' => '', 'p' => '', 'mid' => ''],
        'jltournamenttree' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => ''],
        'tournamentbracket' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => ''],
        'matchreport' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''],
        'matrix' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''],
        'rankingmatrix' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''],
        'nextmatch' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'mid' => ''],
        'player' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''],
        'playground' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'pgid' => ''],
        'leaguechampionoverview' => ['cfg_which_database' => '', 'l' => '', 's' => '', 'p' => ''],
        'ranking' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'type' => '', 'r' => '', 'from' => '', 'to' => '', 'division' => ''],
        'rankingalltime' => ['cfg_which_database' => '', 'l' => '', 'points' => '', 'type' => '', 'order' => '', 'dir' => '', 's' => '', 'p' => ''],
        'referee' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'pid' => ''],
        'referees' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'r' => ''],
        'results' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => ''],
        'allprojectrounds' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'division' => '', 'mode' => '', 'order' => '', 'layout' => ''],
        'resultsranking' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => ''],
        'resultsmatrix' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'r' => '', 'mode' => '', 'order' => '', 'layout' => '', 'division' => ''],
        'rivals' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => ''],
        'roster' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => ''],
        'rosteralltime' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'start' => ''],
        'staff' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'pid' => ''],
        'stats' => ['cfg_which_database' => '', 's' => '', 'p' => ''],
        'statsranking' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => '', 'tid' => '', 'sid' => '', 'order' => ''],
        'teaminfo' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'ptid' => ''],
        'teamplan' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => '', 'division' => '', 'mode' => '', 'ptid' => ''],
        'teams' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => ''],
        'teamstats' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tid' => ''],
        'teamstree' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'division' => ''],
        'treetonode' => ['cfg_which_database' => '', 's' => '', 'p' => '', 'tnid' => ''],
        'predictionentry' => ['cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''],
        'predictionresults' => ['cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''],
        'predictionranking' => ['cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'pggrouprank' => '', 'type' => '', 'from' => '', 'to' => ''],
        'predictionuser' => ['cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => '', 'layout' => 'edit'],
        'predictionusers' => ['cfg_which_database' => '', 'prediction_id' => '', 'pggroup' => '', 'pj' => '', 'r' => '', 'uid' => ''],
        'predictionrules' => ['cfg_which_database' => '', 'prediction_id' => ''],
    ];

    public static function has(string $view): bool
    {
        return isset(self::VIEWS[$view]);
    }

    public static function defaults(string $view): array
    {
        return self::VIEWS[$view] ?? [];
    }
}
